<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyAssignment;
use App\Models\MasterDataItem;
use App\Services\HR\Leave\LeavePolicyAssignmentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreLeavePolicyAssignmentRequest;
use App\Http\Requests\HR\Leave\UpdateLeavePolicyAssignmentRequest;

class LeavePolicyAssignmentController extends Controller
{
    public function __construct(
        protected LeavePolicyAssignmentService $service
    ) {}

    public function index(Request $request): Response
    {
        $query = LeavePolicyAssignment::query()->with(['policy', 'company', 'branch', 'division', 'employee']);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('policy', function ($q) use ($search) {
                $q->where('policy_name', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('employee')) {
            $query->where('user_id', $request->integer('employee'));
        }

        if ($request->filled('company')) {
            $query->where('company_id', $request->integer('company'));
        }

        if ($request->filled('branch')) {
            $query->where('branch_id', $request->integer('branch'));
        }

        if ($request->filled('division')) {
            $query->where('division_id', $request->integer('division'));
        }

        if ($request->filled('policy')) {
            $query->where('leave_policy_id', $request->integer('policy'));
        }

        $assignments = $query->orderBy('effective_from', 'desc')->paginate(10)->withQueryString();

        $companies = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_COMPANY)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $divisions = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_DIVISION)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $policies = LeavePolicy::where('status', 'active')
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);

        $users = \App\Models\User::where('status', \App\Models\User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        return Inertia::render('HR/Leave/LeavePolicyAssignments/Index', [
            'assignments' => $assignments,
            'filters' => $request->only(['employee', 'company', 'branch', 'division', 'policy', 'search']),
            'companies' => $companies,
            'branches' => $branches,
            'divisions' => $divisions,
            'policies' => $policies,
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $policies = LeavePolicy::where('status', 'active')
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);

        $companies = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_COMPANY)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $designations = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $employmentTypes = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_EMPLOYEE_TYPE)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $users = \App\Models\User::where('status', \App\Models\User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        return Inertia::render('HR/Leave/LeavePolicyAssignments/Create', [
            'policies' => $policies,
            'companies' => $companies,
            'branches' => $branches,
            'designations' => $designations,
            'employmentTypes' => $employmentTypes,
            'users' => $users,
        ]);
    }

    public function store(StoreLeavePolicyAssignmentRequest $request)
    {
        $validated = $request->validated();

        $this->service->assign($validated);

        return redirect()
            ->route('hr.leave.assignments.index')
            ->with('success', 'Policy assignment created successfully.');
    }

    public function show(LeavePolicyAssignment $assignment): Response
    {
        $assignment->load(['policy', 'company', 'branch', 'division', 'department', 'section', 'unit', 'designation', 'employee']);

        return Inertia::render('HR/Leave/LeavePolicyAssignments/Show', [
            'assignment' => $assignment,
        ]);
    }

    public function edit(LeavePolicyAssignment $assignment): Response
    {
        $policies = LeavePolicy::where('status', 'active')
            ->orderBy('policy_name')
            ->get(['id', 'policy_name', 'policy_code']);

        $companies = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_COMPANY)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branches = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_BRANCH)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $designations = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $employmentTypes = MasterDataItem::query()
            ->where('category', MasterDataItem::CATEGORY_EMPLOYEE_TYPE)
            ->where('status', MasterDataItem::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $users = \App\Models\User::where('status', \App\Models\User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        $assignment->load(['policy', 'company', 'branch', 'division', 'department', 'section', 'unit', 'designation', 'employee']);

        return Inertia::render('HR/Leave/LeavePolicyAssignments/Edit', [
            'assignment' => $assignment,
            'policies' => $policies,
            'companies' => $companies,
            'branches' => $branches,
            'designations' => $designations,
            'employmentTypes' => $employmentTypes,
            'users' => $users,
        ]);
    }

    public function update(UpdateLeavePolicyAssignmentRequest $request, LeavePolicyAssignment $assignment)
    {
        $validated = $request->validated();

        $this->service->update($assignment, $validated);

        return redirect()
            ->route('hr.leave.assignments.index')
            ->with('success', 'Policy assignment updated successfully.');
    }

    public function destroy(LeavePolicyAssignment $assignment)
    {
        $this->service->delete($assignment);

        return back()->with('success', 'Policy assignment deleted successfully.');
    }
}
