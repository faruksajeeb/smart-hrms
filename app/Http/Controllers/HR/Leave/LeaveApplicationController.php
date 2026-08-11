<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveApplication;
use App\Models\LeavePolicy;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HR\Leave\LeaveApplicationService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreLeaveApplicationRequest;
use App\Http\Requests\HR\Leave\UpdateLeaveApplicationRequest;

class LeaveApplicationController extends Controller
{
    public function __construct(
        protected LeaveApplicationService $service
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = LeaveApplication::query()->with(['employee', 'leaveType', 'leavePolicy']);

        if ($user->hasRole(User::ROLE_EMPLOYEE)) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->filled('employee_id')) {
                $query->where('user_id', $request->integer('employee_id'));
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->integer('leave_type_id'));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        $applications->getCollection()->transform(function ($application) {
            return [
                'id' => $application->id,
                'application_no' => $application->application_no,
                'leave_type_id' => $application->leave_type_id,
                'leavePolicy' => $application->leavePolicy,
                'leaveType' => $application->leaveType,
                'employee' => $application->employee,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
                'total_days' => $application->total_days,
                'status' => $application->status,
                'delegate_user_id' => $application->delegate_user_id,
                'delegate' => $application->delegate,
                'delegate_status' => $application->delegate_status,
                'can_edit' => $application->canEdit(),
                'can_submit' => $application->canSubmit(),
                'can_delete' => $application->canDelete(),
                'can_cancel' => $application->canCancel(),
            ];
        });

        $employees = User::where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $statuses = \App\Enums\LeaveApplicationStatus::options();

        return Inertia::render('HR/Leave/LeaveApplications/Index', [
            'applications' => $applications,
            'employees' => $employees,
            'leaveTypes' => $leaveTypes,
            'statuses' => $statuses,
            'filters' => [
                'employee_id' => $request->integer('employee_id'),
                'status' => $request->string('status'),
                'leave_type_id' => $request->integer('leave_type_id'),
            ],
        ]);
    }

    public function employeeIndex(Request $request): Response
    {
        $user = $request->user();

        $query = LeaveApplication::query()->with(['leaveType', 'leavePolicy'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->integer('leave_type_id'));
        }

        $applications = $query->orderBy('created_at', 'desc')->paginate(15);

        $applications->getCollection()->transform(function ($application) {
            return [
                'id' => $application->id,
                'application_no' => $application->application_no,
                'leave_type_id' => $application->leave_type_id,
                'leavePolicy' => $application->leavePolicy,
                'leaveType' => $application->leaveType,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
                'total_days' => $application->total_days,
                'status' => $application->status,
                'delegate_user_id' => $application->delegate_user_id,
                'delegate' => $application->delegate,
                'delegate_status' => $application->delegate_status,
                'can_edit' => $application->canEdit(),
                'can_submit' => $application->canSubmit(),
                'can_delete' => $application->canDelete(),
                'can_cancel' => $application->canCancel(),
            ];
        });

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $statuses = \App\Enums\LeaveApplicationStatus::options();

        return Inertia::render('Employee/Leave/Applications/Index', [
            'applications' => $applications,
            'leaveTypes' => $leaveTypes,
            'statuses' => $statuses,
            'filters' => [
                'status' => $request->string('status'),
                'leave_type_id' => $request->integer('leave_type_id'),
            ],
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $policies = LeavePolicy::where('status', 'active')
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', now()->toDateString());
            })
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);

        $assignments = \App\Models\LeavePolicyAssignment::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('effective_from', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('effective_to')->orWhere('effective_to', '>=', now()->toDateString());
            })
            ->with('policy')
            ->get();

        if (request()->routeIs('employee.*')) {
            $startDate = now();
            $assignment = $this->service->resolvePolicy($user, $startDate);

            if (!$assignment) {
                return Inertia::render('Employee/Leave/Applications/Create', [
                    'leaveTypes' => $leaveTypes,
                    'policies' => $policies,
                    'policyError' => 'You are not assigned to any Leave Policy. Please contact HR.',
                ]);
            }

            $eligibleLeaveTypes = $assignment->policy->details()
                ->where('status', 'active')
                ->with('leaveType')
                ->get()
                ->map(fn($detail) => $detail->leaveType)
                ->filter();

            $policyDetails = $assignment->policy->details()
                ->where('status', 'active')
                ->with('leaveType')
                ->get();

            $leaveBalances = $this->service->getAllBalances($user);

            return Inertia::render('Employee/Leave/Applications/Create', [
                'leaveTypes' => $eligibleLeaveTypes,
                'allLeaveTypes' => $leaveTypes,
                'activePolicy' => $assignment->policy,
                'policyDetails' => $policyDetails,
                'leaveBalances' => $leaveBalances,
                'user' => $user,
                'assignment' => $assignment,
                'employees' => User::where('status', User::STATUS_ACTIVE)
                    ->where('id', '!=', $user->id)
                    ->where('company_id', $user->company_id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'employee_id', 'department_id', 'branch_id']),
            ]);
        }

        return Inertia::render('HR/Leave/LeaveApplications/Create', [
            'leaveTypes' => $leaveTypes,
            'policies' => $policies,
            'assignments' => $assignments,
        ]);
    }

    public function store(StoreLeaveApplicationRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            $application = $this->service->createDraft($user, $validated, $user->id);

            if (request()->routeIs('employee.*')) {
                return redirect()
                    ->route('employee.leave.applications.index')
                    ->with('success', 'Leave application created as draft successfully.');
            }

            return redirect()
                ->route('hr.leave.applications.edit', $application)
                ->with('success', 'Leave application created as draft successfully.');
        } catch (\RuntimeException $e) {
            if (request()->routeIs('employee.*')) {
                return redirect()
                    ->route('employee.leave.applications.create')
                    ->with('error', $e->getMessage());
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function show(LeaveApplication $application): Response
    {
        $user = request()->user();

        if (request()->routeIs('employee.*') && $application->user_id !== $user->id) {
            abort(403);
        }

        $application->load(['employee', 'leaveType', 'leavePolicy', 'days', 'attachments.uploader', 'creator', 'updater']);

        if (request()->routeIs('employee.*')) {
            $startDate = \Carbon\Carbon::parse($application->start_date ?? now());
            $assignment = $this->service->resolvePolicy($application->employee, $startDate);

            $policyDetails = [];
            $activePolicy = $application->leavePolicy;

            if ($activePolicy) {
                $policyDetails = $activePolicy->details()
                    ->where('status', 'active')
                    ->with('leaveType')
                    ->get();
            }

            return Inertia::render('Employee/Leave/Applications/Show', [
                'application' => $application,
                'activePolicy' => $activePolicy,
                'policyDetails' => $policyDetails,
            ]);
        }

        return Inertia::render('HR/Leave/LeaveApplications/Show', [
            'application' => [
                'id' => $application->id,
                'application_no' => $application->application_no,
                'employee' => $application->employee,
                'leave_type' => $application->leaveType,
                'leave_policy' => $application->leavePolicy,
                'application_type' => $application->application_type,
                'start_date' => $application->start_date,
                'end_date' => $application->end_date,
                'total_days' => $application->total_days,
                'requested_days' => $application->requested_days,
                'is_half_day' => $application->is_half_day,
                'half_day_session' => $application->half_day_session,
                'is_emergency' => $application->is_emergency,
                'reason' => $application->reason,
                'remarks' => $application->remarks,
                'status' => $application->status,
                'delegate' => $application->delegate,
                'delegate_status' => $application->delegate_status,
                'delegate_responded_at' => $application->delegate_responded_at,
                'delegate_remarks' => $application->delegate_remarks,
                'can_edit' => $application->canEdit(),
                'can_submit' => $application->canSubmit(),
                'can_delete' => $application->canDelete(),
                'can_cancel' => $application->canCancel(),
                'days' => $application->days,
                'attachments' => $application->attachments,
            ],
        ]);
    }

    public function edit(LeaveApplication $application): Response
    {
        $user = request()->user();

        if (request()->routeIs('employee.*') && $application->user_id !== $user->id) {
            abort(403);
        }

        if (!$application->canEdit()) {
            return redirect()->route('hr.leave.applications.index')->with('error', 'Only draft applications can be edited.');
        }

        $application->load(['days', 'attachments']);

        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $policies = LeavePolicy::where('status', 'active')
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);

        if (request()->routeIs('employee.*')) {
            $startDate = \Carbon\Carbon::parse($application->start_date ?? now());
            $assignment = $this->service->resolvePolicy($application->employee, $startDate);

            if (!$assignment) {
                return Inertia::render('Employee/Leave/Applications/Edit', [
                    'application' => $application,
                    'leaveTypes' => $leaveTypes,
                    'policies' => $policies,
                    'policyError' => 'You are not assigned to any Leave Policy. Please contact HR.',
                ]);
            }

            $eligibleLeaveTypes = $assignment->policy->details()
                ->where('status', 'active')
                ->with('leaveType')
                ->get()
                ->map(fn($detail) => $detail->leaveType)
                ->filter();

            $policyDetails = $assignment->policy->details()
                ->where('status', 'active')
                ->with('leaveType')
                ->get();

            $leaveBalances = $this->service->getAllBalances($application->employee);

            return Inertia::render('Employee/Leave/Applications/Edit', [
                'application' => $application,
                'leaveTypes' => $eligibleLeaveTypes,
                'allLeaveTypes' => $leaveTypes,
                'activePolicy' => $assignment->policy,
                'policyDetails' => $policyDetails,
                'leaveBalances' => $leaveBalances,
                'user' => $application->employee,
                'assignment' => $assignment,
                'employees' => User::where('status', User::STATUS_ACTIVE)
                    ->where('id', '!=', $application->employee->id)
                    ->where('company_id', $application->employee->company_id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'employee_id', 'department_id', 'branch_id']),
            ]);
        }

        return Inertia::render('HR/Leave/LeaveApplications/Edit', [
            'application' => $application,
            'leaveTypes' => $leaveTypes,
            'policies' => $policies,
        ]);
    }

    public function update(UpdateLeaveApplicationRequest $request, LeaveApplication $application)
    {
        $validated = $request->validated();

        $this->service->updateDraft($application, $validated, $application->updated_by ?? auth()->id());

        if (request()->routeIs('employee.*')) {
            return redirect()
                ->route('employee.leave.applications.index')
                ->with('success', 'Leave application updated successfully.');
        }

        return redirect()
            ->route('hr.leave.applications.edit', $application)
            ->with('success', 'Leave application updated successfully.');
    }

    public function submit(LeaveApplication $application)
    {
        try {
            $this->service->submit($application, auth()->id());

            if (request()->routeIs('employee.*')) {
                return redirect()->route('employee.leave.applications.index')->with('success', 'Your Leave application submitted successfully.');
            }

            return redirect()->route('hr.leave.applications.index')->with('success', 'Leave application submitted successfully.');
        } catch (\RuntimeException $e) {
            if (request()->routeIs('employee.*')) {
                return redirect()->route('employee.leave.applications.index')->with('error', $e->getMessage());
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, LeaveApplication $application)
    {
        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->service->cancel($application, $request->string('remarks'), auth()->id());
            return back()->with('success', 'Leave application cancelled successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function withdraw(Request $request, LeaveApplication $application)
    {
        $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->service->withdraw($application, $request->string('remarks'), auth()->id());
            return back()->with('success', 'Leave application withdrawn successfully.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(LeaveApplication $application)
    {
        if (!$application->canDelete()) {
            return back()->with('error', 'Only draft applications can be deleted.');
        }

        $application->delete();

        return redirect()->route('hr.leave.applications.index')->with('success', 'Leave application deleted successfully.');
    }
}
