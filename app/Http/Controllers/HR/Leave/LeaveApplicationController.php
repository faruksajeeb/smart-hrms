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
        } elseif ($user->hasRole(User::ROLE_HR)) {
            if ($request->filled('employee_id')) {
                $query->where('user_id', $request->integer('employee_id'));
            }
        } elseif ($user->hasRole(User::ROLE_HR)) {
            if ($request->filled('employee_id')) {
                $query->where('user_id', $request->integer('employee_id'));
            }
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

        $application = $this->service->createDraft($user, $validated, $user->id);

        return redirect()
            ->route('hr.leave.applications.edit', $application)
            ->with('success', 'Leave application created as draft successfully.');
    }

    public function show(LeaveApplication $application): Response
    {
        $application->load(['employee', 'leaveType', 'leavePolicy', 'days', 'attachments.uploader', 'creator', 'updater']);

        return Inertia::render('HR/Leave/LeaveApplications/Show', [
            'application' => $application,
        ]);
    }

    public function edit(LeaveApplication $application): Response
    {
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

        return redirect()
            ->route('hr.leave.applications.edit', $application)
            ->with('success', 'Leave application updated successfully.');
    }

    public function submit(LeaveApplication $application)
    {
        try {
            $this->service->submit($application, auth()->id());
            return redirect()->route('hr.leave.applications.index')->with('success', 'Leave application submitted successfully.');
        } catch (\RuntimeException $e) {
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
