<?php

namespace App\Services\HR\Leave;

use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\LeaveApplication;
use App\Models\LeaveApplicationDay;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Enums\ApprovalStatus;

class LeaveDashboardService
{
    public function __construct(
        protected LeaveCalendarService $calendarService,
    ) {}

    /**
     * Get manager dashboard KPIs.
     *
     * @param User $manager
     * @param Carbon|null $month
     * @return array
     */
    public function getManagerDashboard(User $manager, ?Carbon $month = null): array
    {
        $month = $month ?: Carbon::now();
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $today = Carbon::now()->startOfDay();

        // Pending approvals where manager is current approver
        $pendingApprovals = ApprovalRequest::where('current_status', 'pending')
            ->whereHas('steps', function ($q) use ($manager) {
                $q->where('approver_id', $manager->id)
                    ->where('status', 'pending');
            })
            ->where('module_name', 'leave_request')
            ->count();

        // Team members on leave today
        $onLeaveToday = LeaveApplicationDay::where('leave_date', $today->format('Y-m-d'))
            ->where('counts_as_leave', true)
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->where('leave_applications.status', 'approved')
            ->whereIn('leave_applications.user_id', function ($q) use ($manager) {
                $q->select('id')->from('users')->where('reporting_manager_id', $manager->id);
            })
            ->distinct('leave_applications.user_id')
            ->count('leave_applications.user_id');

        // Upcoming leaves (next 30 days)
        $upcoming = LeaveApplication::where('status', 'approved')
            ->where('start_date', '>=', $today->format('Y-m-d'))
            ->where('start_date', '<=', $today->copy()->addDays(30)->format('Y-m-d'))
            ->whereHas('employee', function ($q) use ($manager) {
                $q->where('reporting_manager_id', $manager->id);
            })
            ->count();

        // Leaves this month (approved days from leave_application_days)
        $leaveDaysThisMonth = LeaveApplicationDay::whereBetween('leave_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->where('counts_as_leave', true)
            ->whereHas('application.employee', function ($q) use ($manager) {
                $q->where('reporting_manager_id', $manager->id);
            })
            ->whereHas('application', function ($q) {
                $q->where('status', 'approved');
            })
            ->sum('leave_days');

        // Team size
        $teamSize = User::where('reporting_manager_id', $manager->id)
            ->where('status', User::STATUS_ACTIVE)
            ->count();

        return [
            'pending_approvals' => (int) $pendingApprovals,
            'on_leave_today' => (int) $onLeaveToday,
            'upcoming_leaves' => (int) $upcoming,
            'leave_days_this_month' => (float) $leaveDaysThisMonth,
            'team_size' => (int) $teamSize,
            'month' => $month->format('Y-m'),
        ];
    }

    /**
     * Get manager pending approvals with details.
     *
     * @param User $manager
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getManagerPendingApprovals(User $manager, int $perPage = 15)
    {
        return ApprovalRequest::query()
            ->with([
                'workflow',
                'requester',
                'reference.leaveType',
                'reference.employee',
                'reference.delegate',
                'currentStep.workflowLevel',
            ])
            ->where('current_status', 'pending')
            ->whereHas('steps', function ($q) use ($manager) {
                $q->where('approver_id', $manager->id)
                    ->where('status', 'pending');
            })
            ->where('module_name', 'leave_request')
            ->orderByDesc('submitted_at')
            ->paginate($perPage);
    }

    /**
     * Get manager team leaves today.
     *
     * @param User $manager
     * @return array
     */
    public function getManagerTeamOnLeaveToday(User $manager): array
    {
        $today = Carbon::now()->startOfDay();

        $days = LeaveApplicationDay::where('leave_date', $today->format('Y-m-d'))
            ->where('counts_as_leave', true)
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->where('leave_applications.status', 'approved')
            ->whereIn('leave_applications.user_id', function ($q) use ($manager) {
                $q->select('id')->from('users')->where('reporting_manager_id', $manager->id);
            })
            ->with(['application.leaveType', 'application.employee', 'application.delegate'])
            ->orderBy('leave_applications.user_id')
            ->get();

        return $days->map(function ($day) {
            $application = $day->application;
            return [
                'employee_id' => $application->employee->id,
                'employee_name' => $application->employee->name,
                'employee_code' => $application->employee->employee_id,
                'department' => $application->employee->department?->name,
                'leave_type' => $application->leaveType->leave_name,
                'leave_code' => $application->leaveType->leave_code,
                'date' => $day->leave_date,
                'leave_days' => (float) $day->leave_days,
                'session' => $day->session,
                'day_type' => $day->day_type,
                'application_no' => $application->application_no,
                'delegate' => $application->delegate ? $application->delegate->name : null,
            ];
        })->toArray();
    }

    /**
     * Get HR dashboard KPIs.
     *
     * @param User $hrUser
     * @param Carbon|null $month
     * @return array
     */
    public function getHRDashboard(User $hrUser, ?Carbon $month = null): array
    {
        $month = $month ?: Carbon::now();
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $today = Carbon::now()->startOfDay();

        // Build company scope query
        $companyScope = $this->getCompanyScopeQuery($hrUser);

        // Total employees in scope
        $totalEmployees = User::query()
            ->where('status', User::STATUS_ACTIVE)
            ->when($companyScope['company_id'], fn($q) => $q->where('company_id', $companyScope['company_id']))
            ->when($companyScope['branch_id'], fn($q) => $q->where('branch_id', $companyScope['branch_id']))
            ->count();

        // Employees on leave today
        $onLeaveToday = LeaveApplicationDay::where('leave_date', $today->format('Y-m-d'))
            ->where('counts_as_leave', true)
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->where('leave_applications.status', 'approved')
            ->where(function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->whereIn('leave_applications.user_id', function ($q2) use ($companyScope) {
                        $q2->select('id')->from('users')->where('company_id', $companyScope['company_id']);
                    });
                }
                if ($companyScope['branch_id']) {
                    $q->whereIn('leave_applications.user_id', function ($q2) use ($companyScope) {
                        $q2->select('id')->from('users')->where('branch_id', $companyScope['branch_id']);
                    });
                }
            })
            ->distinct('leave_applications.user_id')
            ->count('leave_applications.user_id');

        // Approved leaves this month
        $approvedLeaves = LeaveApplication::where('status', 'approved')
            ->whereHas('employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->whereBetween('start_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->count();

        // Pending leaves
        $pendingLeaves = LeaveApplication::where('status', 'pending')
            ->whereHas('employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->count();

        // Rejected leaves this month
        $rejectedLeaves = LeaveApplication::where('status', 'rejected')
            ->whereHas('employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->whereBetween('updated_at', [$monthStart, $monthEnd])
            ->count();

        // Cancelled leaves this month
        $cancelledLeaves = LeaveApplication::where('status', 'cancelled')
            ->whereHas('employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->whereBetween('updated_at', [$monthStart, $monthEnd])
            ->count();

        // Total leave days this month (approved)
        $totalLeaveDays = LeaveApplicationDay::whereBetween('leave_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->where('counts_as_leave', true)
            ->whereHas('application.employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->whereHas('application', function ($q) {
                $q->where('status', 'approved');
            })
            ->sum('leave_days');

        // Leave applications this month
        $applicationsThisMonth = LeaveApplication::whereHas('employee', function ($q) use ($companyScope) {
            if ($companyScope['company_id']) {
                $q->where('company_id', $companyScope['company_id']);
            }
            if ($companyScope['branch_id']) {
                $q->where('branch_id', $companyScope['branch_id']);
            }
        })
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->count();

        return [
            'total_employees' => (int) $totalEmployees,
            'on_leave_today' => (int) $onLeaveToday,
            'approved_leaves' => (int) $approvedLeaves,
            'pending_leaves' => (int) $pendingLeaves,
            'rejected_leaves' => (int) $rejectedLeaves,
            'cancelled_leaves' => (int) $cancelledLeaves,
            'total_leave_days' => (float) $totalLeaveDays,
            'applications_this_month' => (int) $applicationsThisMonth,
            'month' => $month->format('Y-m'),
        ];
    }

    /**
     * Get leave type distribution for HR dashboard.
     *
     * @param User $hrUser
     * @param Carbon|null $month
     * @return array
     */
    public function getLeaveTypeDistribution(User $hrUser, ?Carbon $month = null): array
    {
        $month = $month ?: Carbon::now();
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $companyScope = $this->getCompanyScopeQuery($hrUser);

        $distribution = LeaveApplicationDay::query()
            ->select('leave_types.leave_name', 'leave_types.leave_code', DB::raw('SUM(leave_application_days.leave_days) as total_days'))
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_applications.leave_type_id')
            ->whereBetween('leave_application_days.leave_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')])
            ->where('leave_application_days.counts_as_leave', true)
            ->whereHas('application.employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->whereHas('application', function ($q) {
                $q->where('status', 'approved');
            })
            ->groupBy('leave_types.id', 'leave_types.leave_name', 'leave_types.leave_code')
            ->orderByDesc('total_days')
            ->get();

        return $distribution->map(function ($item) {
            return [
                'leave_name' => $item->leave_name,
                'leave_code' => $item->leave_code,
                'total_days' => (float) $item->total_days,
            ];
        })->toArray();
    }

    /**
     * Get monthly leave trend.
     *
     * @param User $hrUser
     * @param int $months
     * @return array
     */
    public function getMonthlyTrend(User $hrUser, int $months = 12): array
    {
        $companyScope = $this->getCompanyScopeQuery($hrUser);
        $endDate = Carbon::now();
        $startDate = $endDate->copy()->subMonths($months - 1)->startOfMonth();

        $trend = LeaveApplicationDay::query()
            ->select(
                DB::raw('YEAR(leave_application_days.leave_date) as year'),
                DB::raw('MONTH(leave_application_days.leave_date) as month'),
                DB::raw('SUM(leave_application_days.leave_days) as total_days')
            )
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->whereBetween('leave_application_days.leave_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('leave_application_days.counts_as_leave', true)
            ->whereHas('application.employee', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->whereHas('application', function ($q) {
                $q->where('status', 'approved');
            })
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $result = [];
        foreach ($trend as $item) {
            $result[] = [
                'label' => Carbon::create($item->year, $item->month, 1)->format('M Y'),
                'value' => (float) $item->total_days,
                'year' => (int) $item->year,
                'month' => (int) $item->month,
            ];
        }

        return $result;
    }

    /**
     * Get department-wise analysis.
     *
     * @param User $hrUser
     * @param Carbon|null $month
     * @return array
     */
    public function getDepartmentAnalysis(User $hrUser, ?Carbon $month = null): array
    {
        $month = $month ?: Carbon::now();
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $today = Carbon::now()->format('Y-m-d');
        $companyScope = $this->getCompanyScopeQuery($hrUser);

        $departments = DB::table('master_data_items as dept')
            ->select(
                'dept.id',
                'dept.name as department_name',
                DB::raw('COUNT(DISTINCT users.id) as total_employees'),
                DB::raw('COUNT(DISTINCT CASE WHEN leave_applications.status = \'approved\' THEN leave_applications.id END) as approved_applications'),
                DB::raw('SUM(CASE WHEN leave_application_days.counts_as_leave = 1 AND leave_applications.status = \'approved\' THEN leave_application_days.leave_days ELSE 0 END) as approved_days'),
                DB::raw('COUNT(DISTINCT CASE WHEN leave_applications.status = \'pending\' THEN leave_applications.id END) as pending_applications'),
                DB::raw('COUNT(DISTINCT CASE WHEN leave_applications.status = \'approved\' AND leave_application_days.leave_date = \'' . $today . '\' THEN leave_applications.user_id END) as on_leave_today')
            )
            ->leftJoin('users', function ($join) {
                $join->on('users.department_id', '=', 'dept.id')
                    ->where('users.status', User::STATUS_ACTIVE);
            })
            ->leftJoin('leave_applications', function ($join) use ($monthStart, $monthEnd) {
                $join->on('leave_applications.user_id', '=', 'users.id')
                    ->whereBetween('leave_applications.start_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
            })
            ->leftJoin('leave_application_days', function ($join) use ($monthStart, $monthEnd) {
                $join->on('leave_application_days.leave_application_id', '=', 'leave_applications.id')
                    ->whereBetween('leave_application_days.leave_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
            })
            ->where('dept.category', 'department')
            ->when($companyScope['company_id'], fn($q) => $q->where('users.company_id', $companyScope['company_id']))
            ->when($companyScope['branch_id'], fn($q) => $q->where('users.branch_id', $companyScope['branch_id']))
            ->groupBy('dept.id', 'dept.name')
            ->orderByDesc('approved_days')
            ->get();

        return $departments->map(function ($dept) {
            return [
                'id' => $dept->id,
                'department_name' => $dept->department_name,
                'total_employees' => (int) $dept->total_employees,
                'approved_applications' => (int) $dept->approved_applications,
                'approved_days' => (float) $dept->approved_days,
                'pending_applications' => (int) $dept->pending_applications,
                'on_leave_today' => (int) $dept->on_leave_today,
            ];
        })->toArray();
    }

    /**
     * Get branch-wise analysis.
     *
     * @param User $hrUser
     * @param Carbon|null $month
     * @return array
     */
    public function getBranchAnalysis(User $hrUser, ?Carbon $month = null): array
    {
        $month = $month ?: Carbon::now();
        $monthStart = $month->copy()->startOfMonth();
        $monthEnd = $month->copy()->endOfMonth();
        $today = Carbon::now()->format('Y-m-d');
        $companyScope = $this->getCompanyScopeQuery($hrUser);

        $branches = DB::table('master_data_items as branch')
            ->select(
                'branch.id',
                'branch.name as branch_name',
                DB::raw('COUNT(DISTINCT users.id) as total_employees'),
                DB::raw('SUM(CASE WHEN leave_application_days.counts_as_leave = 1 AND leave_applications.status = \'approved\' THEN leave_application_days.leave_days ELSE 0 END) as approved_days'),
                DB::raw('COUNT(DISTINCT CASE WHEN leave_applications.status = \'pending\' THEN leave_applications.id END) as pending_applications'),
                DB::raw('COUNT(DISTINCT CASE WHEN leave_applications.status = \'approved\' AND leave_application_days.leave_date = \'' . $today . '\' THEN leave_applications.user_id END) as on_leave_today')
            )
            ->leftJoin('users', function ($join) {
                $join->on('users.branch_id', '=', 'branch.id')
                    ->where('users.status', User::STATUS_ACTIVE);
            })
            ->leftJoin('leave_applications', function ($join) use ($monthStart, $monthEnd) {
                $join->on('leave_applications.user_id', '=', 'users.id')
                    ->whereBetween('leave_applications.start_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
            })
            ->leftJoin('leave_application_days', function ($join) use ($monthStart, $monthEnd) {
                $join->on('leave_application_days.leave_application_id', '=', 'leave_applications.id')
                    ->whereBetween('leave_application_days.leave_date', [$monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]);
            })
            ->where('branch.category', 'branch')
            ->when($companyScope['company_id'], fn($q) => $q->where('users.company_id', $companyScope['company_id']))
            ->groupBy('branch.id', 'branch.name')
            ->orderByDesc('approved_days')
            ->get();

        return $branches->map(function ($branch) {
            return [
                'id' => $branch->id,
                'branch_name' => $branch->branch_name,
                'total_employees' => (int) $branch->total_employees,
                'approved_days' => (float) $branch->approved_days,
                'pending_applications' => (int) $branch->pending_applications,
                'on_leave_today' => (int) $branch->on_leave_today,
            ];
        })->toArray();
    }

    /**
     * Get pending leave report for HR.
     *
     * @param User $hrUser
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPendingLeaveReport(User $hrUser, int $perPage = 15)
    {
        $companyScope = $this->getCompanyScopeQuery($hrUser);

        $approvalRequests = ApprovalRequest::query()
            ->with([
                'workflow',
                'requester',
                'reference.leaveType',
                'reference.employee',
                'reference.delegate',
                'steps.approver',
                'steps.workflowLevel',
            ])
            ->where('current_status', ApprovalStatus::Pending)
            ->where('module_name', 'leave_request')
            ->whereHas('steps', function ($q) {
                $q->where('status', ApprovalStatus::Pending);
            })
            ->whereHas('requester', function ($q) use ($companyScope) {
                if ($companyScope['company_id']) {
                    $q->where('company_id', $companyScope['company_id']);
                }
                if ($companyScope['branch_id']) {
                    $q->where('branch_id', $companyScope['branch_id']);
                }
            })
            ->orderByDesc('submitted_at')
            ->paginate($perPage);
    //dd($approvalRequests->toArray()); 
   
        return $approvalRequests;
    }

    /**
     * Get company scope for HR user.
     *
     * @param User $user
     * @return array
     */
    private function getCompanyScopeQuery(User $user): array
    {
        // Admin can see everything
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return ['company_id' => null, 'branch_id' => null];
        }

        // HR users are scoped by their company/branch assignment
        return [
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
        ];
    }
}