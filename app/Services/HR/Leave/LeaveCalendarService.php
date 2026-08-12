<?php

namespace App\Services\HR\Leave;

use App\Models\LeaveApplication;
use App\Models\LeaveApplicationDay;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveCalendarService
{
    public function __construct(
        protected LeavePolicyResolver $policyResolver,
        protected LeaveEligibilityService $eligibilityService,
    ) {}

    /**
     * Get leave calendar events for an employee within a date range.
     *
     * @param User $employee
     * @param Carbon $from
     * @param Carbon $to
     * @param array $filters
     * @return array
     */
    public function getEmployeeCalendar(User $employee, Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = LeaveApplicationDay::query()
            ->whereHas('application', function ($q) use ($employee) {
                $q->where('user_id', $employee->id);
            })
            ->whereBetween('leave_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with(['application.leaveType', 'application.employee', 'application.delegate']);

        # Only show counts_as_leave = 1 for actual leave calendar entries
        if (!($filters['include_non_leave'] ?? false)) {
            $query->where('counts_as_leave', true);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->whereHas('application', function ($q) use ($filters) {
                $q->where('leave_type_id', $filters['leave_type_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->whereHas('application', function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        $days = $query->orderBy('leave_date')->orderBy('id')->get();
        // dd($days);
        $events = [];
        foreach ($days as $day) {
            $application = $day->application;
            $events[] = [
                'id' => $day->id,
                'date' => $day->leave_date,
                'day_type' => $day->day_type,
                'session' => $day->session,
                'leave_days' => (float) $day->leave_days,
                'is_holiday' => (bool) $day->is_holiday,
                'is_weekly_off' => (bool) $day->is_weekly_off,
                'counts_as_leave' => (bool) $day->counts_as_leave,
                'status' => $application->status?->value,
                'leave_type' => [
                    'id' => $application->leaveType->id,
                    'name' => $application->leaveType->leave_name,
                    'code' => $application->leaveType->leave_code,
                ],
                'application_no' => $application->application_no,
                'reason' => $application->reason,
                'delegate' => $application->delegate ? [
                    'id' => $application->delegate->id,
                    'name' => $application->delegate->name,
                    'employee_id' => $application->delegate->employee_id,
                ] : null,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
            ];
        }

        return $events;
    }

    /**
     * Get team leave calendar events for a manager within a date range.
     *
     * @param User $manager
     * @param Carbon $from
     * @param Carbon $to
     * @param array $filters
     * @return array
     */
    public function getTeamCalendar(User $manager, Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = LeaveApplicationDay::query()
            ->whereHas('application.employee', function ($q) use ($manager) {
                $q->where('reporting_manager_id', $manager->id);
            })
            ->whereBetween('leave_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->with(['application.leaveType', 'application.employee', 'application.delegate']);

        if (!($filters['include_non_leave'] ?? false)) {
            $query->where('counts_as_leave', true);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->whereHas('application', function ($q) use ($filters) {
                $q->where('leave_type_id', $filters['leave_type_id']);
            });
        }

        if (!empty($filters['status'])) {
            $query->whereHas('application', function ($q) use ($filters) {
                $q->where('status', $filters['status']);
            });
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('application.employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        if (!empty($filters['section_id'])) {
            $query->whereHas('application.employee', function ($q) use ($filters) {
                $q->where('section_id', $filters['section_id']);
            });
        }

        if (!empty($filters['unit_id'])) {
            $query->whereHas('application.employee', function ($q) use ($filters) {
                $q->where('unit_id', $filters['unit_id']);
            });
        }

        if (!empty($filters['employee_id'])) {
            $query->whereHas('application', function ($q) use ($filters) {
                $q->where('user_id', $filters['employee_id']);
            });
        }

        $days = $query->orderBy('leave_date')->orderBy('id')->get();

        $events = [];
        foreach ($days as $day) {
            $application = $day->application;
            $employee = $application->employee;
            $events[] = [
                'id' => $day->id,
                'date' => $day->leave_date,
                'day_type' => $day->day_type,
                'session' => $day->session,
                'leave_days' => (float) $day->leave_days,
                'is_holiday' => (bool) $day->is_holiday,
                'is_weekly_off' => (bool) $day->is_weekly_off,
                'counts_as_leave' => (bool) $day->counts_as_leave,
                'status' => $application->status?->value,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'employee_id' => $employee->employee_id,
                    'department' => $employee->department?->name,
                    'section' => $employee->section?->name,
                    'unit' => $employee->unit?->name,
                ],
                'leave_type' => [
                    'id' => $application->leaveType->id,
                    'name' => $application->leaveType->leave_name,
                    'code' => $application->leaveType->leave_code,
                ],
                'application_no' => $application->application_no,
                'delegate' => $application->delegate ? [
                    'id' => $application->delegate->id,
                    'name' => $application->delegate->name,
                    'employee_id' => $application->delegate->employee_id,
                ] : null,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
            ];
        }

        return $events;
    }

    /**
     * Get team leave summary for a specific date.
     *
     * @param User $manager
     * @param Carbon $date
     * @return array
     */
    public function getTeamLeaveSummary(User $manager, Carbon $date): array
    {
        $dateStr = $date->format('Y-m-d');

        $query = LeaveApplicationDay::query()
            ->whereHas('application.employee', function ($q) use ($manager) {
                $q->where('reporting_manager_id', $manager->id);
            })
            ->where('leave_date', $dateStr)
            ->where('counts_as_leave', true)
            ->whereHas('application', function ($q) {
                $q->whereIn('status', ['approved', 'pending']);
            })
            ->with(['application.leaveType', 'application.employee']);

        $days = $query->get();

        $employeesOnLeave = [];
        $leaveTypeBreakdown = [];
        $departmentBreakdown = [];

        foreach ($days as $day) {
            $application = $day->application;
            $employee = $application->employee;

            $employeesOnLeave[] = [
                'employee_id' => $employee->id,
                'name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'leave_type' => $application->leaveType->leave_name,
                'leave_days' => (float) $day->leave_days,
                'session' => $day->session,
                'department' => $employee->department?->name,
            ];

            $ltCode = $application->leaveType->leave_code;
            $leaveTypeBreakdown[$ltCode] = ($leaveTypeBreakdown[$ltCode] ?? 0) + (float) $day->leave_days;

            $dept = $employee->department?->name ?? 'Unknown';
            $departmentBreakdown[$dept] = ($departmentBreakdown[$dept] ?? 0) + (float) $day->leave_days;
        }

        return [
            'date' => $dateStr,
            'total_on_leave' => count($employeesOnLeave),
            'employees' => $employeesOnLeave,
            'by_leave_type' => $leaveTypeBreakdown,
            'by_department' => $departmentBreakdown,
        ];
    }

    /**
     * Get currently on leave employees for a date range.
     *
     * @param User $user
     * @param Carbon $from
     * @param Carbon $to
     * @param array $filters
     * @return array
     */
    public function getCurrentlyOnLeave(User $user, Carbon $from, Carbon $to, array $filters = []): array
    {
        $query = LeaveApplicationDay::query()
            ->whereBetween('leave_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->where('counts_as_leave', true)
            ->whereHas('application', function ($q) {
                $q->where('status', 'approved');
            })
            ->with(['application.leaveType', 'application.employee', 'application.delegate']);

        // Scope by user role
        if ($user->hasRole(User::ROLE_EMPLOYEE)) {
            $query->whereHas('application', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        } elseif (User::where('reporting_manager_id', $user->id)->exists()) {
            $query->whereHas('application.employee', function ($q) use ($user) {
                $q->where('reporting_manager_id', $user->id);
            });
        }

        if (!empty($filters['leave_type_id'])) {
            $query->whereHas('application', function ($q) use ($filters) {
                $q->where('leave_type_id', $filters['leave_type_id']);
            });
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('application.employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $days = $query->orderBy('leave_date')->get();

        $result = [];
        foreach ($days as $day) {
            $application = $day->application;
            $employee = $application->employee;
            $result[] = [
                'date' => $day->leave_date,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'employee_code' => $employee->employee_id,
                'department' => $employee->department?->name,
                'leave_type' => $application->leaveType->leave_name,
                'leave_code' => $application->leaveType->leave_code,
                'leave_days' => (float) $day->leave_days,
                'session' => $day->session,
                'day_type' => $day->day_type,
                'application_no' => $application->application_no,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
                'delegate' => $application->delegate ? $application->delegate->name : null,
            ];
        }

        return $result;
    }

    /**
     * Get upcoming approved leaves.
     *
     * @param User $user
     * @param int $days
     * @param array $filters
     * @return array
     */
    public function getUpcomingLeaves(User $user, int $days = 30, array $filters = []): array
    {
        $from = Carbon::now()->startOfDay();
        $to = $from->copy()->addDays($days);

        $query = LeaveApplication::query()
            ->where('status', 'approved')
            ->where('start_date', '>=', $from->format('Y-m-d'))
            ->where('start_date', '<=', $to->format('Y-m-d'))
            ->with(['leaveType', 'employee', 'delegate']);

        if ($user->hasRole(User::ROLE_EMPLOYEE)) {
            $query->where('user_id', $user->id);
        } elseif (User::where('reporting_manager_id', $user->id)->exists()) {
            $query->whereHas('employee', function ($q) use ($user) {
                $q->where('reporting_manager_id', $user->id);
            });
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->whereHas('employee', function ($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $applications = $query->orderBy('start_date')->get();

        return $applications->map(function ($application) {
            return [
                'id' => $application->id,
                'application_no' => $application->application_no,
                'employee_id' => $application->employee->id,
                'employee_name' => $application->employee->name,
                'employee_code' => $application->employee->employee_id,
                'department' => $application->employee->department?->name,
                'leave_type' => $application->leaveType->leave_name,
                'leave_code' => $application->leaveType->leave_code,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
                'total_days' => (float) $application->total_days,
                'is_half_day' => (bool) $application->is_half_day,
                'half_day_session' => $application->half_day_session,
                'delegate' => $application->delegate ? $application->delegate->name : null,
            ];
        })->toArray();
    }

    /**
     * Build company scope for HR users.
     *
     * @param User $user
     * @return array{company_id: int|null, branch_id: int|null}
     */
    public function getCompanyScope(User $user): array
    {
        if ($user->hasRole(User::ROLE_ADMIN)) {
            return ['company_id' => null, 'branch_id' => null];
        }

        return [
            'company_id' => $user->company_id,
            'branch_id' => $user->branch_id,
        ];
    }

    /**
     * Get HR leave calendar events and summary.
     *
     * @param User $hrUser
     * @param Carbon $from
     * @param Carbon $to
     * @param array $filters
     * @return array{events: array, summary: array}
     */
    public function getHRCalendar(User $hrUser, Carbon $from, Carbon $to, array $filters = []): array
    {
        $scope = $this->getCompanyScope($hrUser);
        // $test = LeaveApplicationDay::query()
        //     ->whereBetween('leave_date', [
        //         $from->format('Y-m-d'),
        //         $to->format('Y-m-d'),
        //     ])
        //     ->get();

        // dd($test);

        // dd($from->format('Y-m-d'), $to->format('Y-m-d'), $scope, $filters);
        $query = LeaveApplicationDay::query()
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->join('users', 'users.id', '=', 'leave_applications.user_id')
            ->leftJoin('users as delegate_users', 'delegate_users.id', '=', 'leave_applications.delegate_user_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_applications.leave_type_id')
            ->whereBetween('leave_application_days.leave_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->select(
                'leave_application_days.*',
                'leave_application_days.leave_date as calendar_date',
                'leave_applications.status as application_status',
                'leave_applications.application_no',
                'leave_applications.reason',
                'leave_applications.start_date as app_start_date',
                'leave_applications.end_date as app_end_date',
                'leave_applications.submitted_at',
                'leave_applications.approved_at',
                'leave_applications.delegate_user_id',
                'leave_applications.leave_type_id',
                'delegate_users.name as delegate_name',
                'delegate_users.employee_id as delegate_employee_code',
                'users.id as employee_user_id',
                'users.name as employee_name',
                'users.employee_id as employee_code',
                'users.company_id',
                'users.branch_id',
                'users.division_id',
                'users.department_id',
                'users.section_id',
                'users.unit_id',
                'users.designation_id',
                'users.employment_type_id',
                'leave_types.leave_name',
                'leave_types.leave_code',
                'leave_types.display_color'
            );

        // ============================================================
        // 1. HR ACCESS SCOPE
        // ============================================================

        if ($scope['company_id'] !== null) {
            $query->where('users.company_id', $scope['company_id']);
        }

        if ($scope['branch_id'] !== null) {
            $query->where('users.branch_id', $scope['branch_id']);
        }


        // ============================================================
        // 2. CALENDAR FILTERS
        // ============================================================

        $filterFields = [
            'company_id',
            'branch_id',
            'division_id',
            'department_id',
            'section_id',
            'unit_id',
            'designation_id',
            'employment_type_id',
        ];

        foreach ($filterFields as $field) {
            $value = (int) ($filters[$field] ?? 0);

            if ($value > 0) {
                $query->where("users.{$field}", $value);
            }
        }


        // ============================================================
        // 3. STATUS
        // ============================================================

        $status = $filters['status'] ?? 'approved';

        if ($status !== 'all') {
            $query->where('leave_applications.status', $status);
        }


        // ============================================================
        // 4. LEAVE TYPE
        // ============================================================

        $leaveTypeId = (int) ($filters['leave_type_id'] ?? 0);

        if ($leaveTypeId > 0) {
            $query->where(
                'leave_applications.leave_type_id',
                $leaveTypeId
            );
        }


        // ============================================================
        // 5. EMPLOYEE
        // ============================================================

        $employeeId = (int) ($filters['employee_id'] ?? 0);

        if ($employeeId > 0) {
            $query->where(
                'leave_applications.user_id',
                $employeeId
            );
        }


        // ============================================================
        // 6. EMPLOYEE SEARCH
        // ============================================================

        $employeeSearch = trim($filters['employee'] ?? '');

        if ($employeeSearch !== '') {
            $query->where(function ($q) use ($employeeSearch) {
                $q->where(
                    'users.employee_id',
                    'like',
                    "%{$employeeSearch}%"
                )->orWhere(
                    'users.name',
                    'like',
                    "%{$employeeSearch}%"
                );
            });
        }


        // ============================================================
        // 7. INCLUDE NON-LEAVE
        // ============================================================

        $includeNonLeave = (bool) ($filters['include_non_leave'] ?? false);

        if (!$includeNonLeave) {
            $query->where(
                'leave_application_days.counts_as_leave',
                true
            );
        }

        $days = $query->orderBy('leave_application_days.leave_date')->orderBy('leave_application_days.id')->get();
        // dd($days);
        $events = [];
        $employeeIds = [];
        $applicationIds = [];
        $totalLeaveDays = 0;

        foreach ($days as $day) {
            $events[] = [
                'id' => $day->id,
                'date' => $day->calendar_date,
                'day_type' => $day->day_type,
                'session' => $day->session,
                'leave_days' => (float) $day->leave_days,
                'is_holiday' => (bool) $day->is_holiday,
                'is_weekly_off' => (bool) $day->is_weekly_off,
                'counts_as_leave' => (bool) $day->counts_as_leave,
                'status' => $day->application_status,
                'employee' => [
                    'id' => (int) $day->employee_user_id,
                    'name' => $day->employee_name,
                    'employee_id' => $day->employee_code,
                    'company_id' => $day->company_id,
                    'branch_id' => $day->branch_id,
                    'division_id' => $day->division_id,
                    'department_id' => $day->department_id,
                    'section_id' => $day->section_id,
                    'unit_id' => $day->unit_id,
                    'designation_id' => $day->designation_id,
                    'employment_type_id' => $day->employment_type_id,
                ],
                'leave_type' => [
                    'id' => (int) $day->leave_type_id,
                    'name' => $day->leave_name,
                    'code' => $day->leave_code,
                    'display_color' => $day->display_color,
                ],
                'application_no' => $day->application_no,
                'reason' => $day->reason,
                'delegate' => $day->delegate_user_id ? [
                    'id' => (int) $day->delegate_user_id,
                    'name' => $day->delegate_name,
                    'employee_id' => $day->delegate_employee_code,
                ] : null,
                'delegate_user_id' => $day->delegate_user_id,
                'start_date' => $day->app_start_date,
                'end_date' => $day->app_end_date,
                'submitted_at' => $day->submitted_at,
                'approved_at' => $day->approved_at,
            ];

            if ($day->counts_as_leave) {
                $employeeIds[$day->employee_code] = true;
                $applicationIds[$day->application_no] = true;
                $totalLeaveDays += (float) $day->leave_days;
            }
        }

        $summary = [
            'selected_month' => $from->format('F Y'),
            'employees_on_leave' => count($employeeIds),
            'leave_days' => $totalLeaveDays,
            'applications' => count($applicationIds),
            'total_events' => count($events),
        ];

        return [
            'events' => $events,
            'summary' => $summary,
        ];
    }

    /**
     * Get day details for HR calendar.
     *
     * @param User $hrUser
     * @param Carbon $date
     * @param array $filters
     * @return array
     */
    public function getHRCalendarDayDetails(User $hrUser, Carbon $date, array $filters = []): array
    {
        $scope = $this->getCompanyScope($hrUser);

        $query = LeaveApplicationDay::query()
            ->join('leave_applications', 'leave_applications.id', '=', 'leave_application_days.leave_application_id')
            ->join('users', 'users.id', '=', 'leave_applications.user_id')
            ->leftJoin('users as delegate_users', 'delegate_users.id', '=', 'leave_applications.delegate_user_id')
            ->join('leave_types', 'leave_types.id', '=', 'leave_applications.leave_type_id')
            ->where('leave_application_days.leave_date', $date->format('Y-m-d'))
            ->select(
                'leave_application_days.*',
                'leave_application_days.leave_date as calendar_date',
                'leave_applications.status as application_status',
                'leave_applications.application_no',
                'leave_applications.reason',
                'leave_applications.start_date as app_start_date',
                'leave_applications.end_date as app_end_date',
                'leave_applications.submitted_at',
                'leave_applications.approved_at',
                'leave_applications.delegate_user_id',
                'leave_applications.leave_type_id',
                'delegate_users.name as delegate_name',
                'delegate_users.employee_id as delegate_employee_code',
                'users.id as employee_user_id',
                'users.name as employee_name',
                'users.employee_id as employee_code',
                'users.company_id',
                'users.branch_id',
                'users.division_id',
                'users.department_id',
                'users.section_id',
                'users.unit_id',
                'users.designation_id',
                'users.employment_type_id',
                'leave_types.leave_name',
                'leave_types.leave_code',
                'leave_types.display_color'
            );

        if (!$hrUser->hasRole(User::ROLE_ADMIN) && $scope['company_id'] === null) {
            $query->whereNull('users.company_id');
        } elseif ($scope['company_id'] !== null) {
            $query->where('users.company_id', $scope['company_id']);
        }
        if ($scope['branch_id'] !== null) {
            $query->where('users.branch_id', $scope['branch_id']);
        }

        if (!empty($filters['company_id'])) {
            $query->where('users.company_id', $filters['company_id']);
        }
        if (!empty($filters['branch_id'])) {
            $query->where('users.branch_id', $filters['branch_id']);
        }
        if (!empty($filters['division_id'])) {
            $query->where('users.division_id', $filters['division_id']);
        }
        if (!empty($filters['department_id'])) {
            $query->where('users.department_id', $filters['department_id']);
        }
        if (!empty($filters['section_id'])) {
            $query->where('users.section_id', $filters['section_id']);
        }
        if (!empty($filters['unit_id'])) {
            $query->where('users.unit_id', $filters['unit_id']);
        }
        if (!empty($filters['designation_id'])) {
            $query->where('users.designation_id', $filters['designation_id']);
        }
        if (!empty($filters['employment_type_id'])) {
            $query->where('users.employment_type_id', $filters['employment_type_id']);
        }

        $status = $filters['status'] ?? 'approved';
        if ($status !== 'all') {
            $query->where('leave_applications.status', $status);
        }

        if (!empty($filters['leave_type_id'])) {
            $query->where('leave_applications.leave_type_id', $filters['leave_type_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('leave_applications.user_id', $filters['employee_id']);
        }
        if (!empty($filters['employee'])) {
            $search = $filters['employee'];
            $query->where(function ($q) use ($search) {
                $q->where('users.employee_id', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if (empty($filters['include_non_leave'])) {
            $query->where('leave_application_days.counts_as_leave', true);
        }

        $days = $query->orderBy('users.name')->get();

        $employees = [];
        foreach ($days as $day) {
            $employees[] = [
                'employee_name' => $day->employee_name,
                'employee_code' => $day->employee_code,
                'leave_type' => $day->leave_name,
                'leave_code' => $day->leave_code,
                'employee_id' => (int) $day->employee_user_id,
                'leave_days' => (float) $day->leave_days,
                'status' => $day->application_status,
                'session' => $day->session,
                'day_type' => $day->day_type,
                'application_no' => $day->application_no,
                'reason' => $day->reason,
                'start_date' => $day->app_start_date,
                'end_date' => $day->app_end_date,
                'delegate' => $day->delegate_user_id ? [
                    'id' => (int) $day->delegate_user_id,
                    'name' => $day->delegate_name,
                    'employee_id' => $day->delegate_employee_code,
                ] : null,
            ];
        }

        return [
            'date' => $date->format('Y-m-d'),
            'employees' => $employees,
            'total' => count($employees),
        ];
    }
}
