<?php

namespace App\Http\Controllers\HR;

use App\Enums\EmploymentMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmploymentMovement\StoreEmploymentMovementRequest;
use App\Http\Requests\HR\EmploymentMovement\UpdateEmploymentMovementRequest;
use App\Models\EmployeeEmploymentHistory;
use App\Models\MasterDataItem;
use App\Models\User;
use App\Services\HR\EmploymentMovementApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmploymentMovementController extends Controller
{
    public function __construct(
        protected EmploymentMovementApprovalService $service
    ) {}

    public function index(Request $request): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $query = EmployeeEmploymentHistory::query()
            ->with([
                'employee:id,employee_id,name,company_id,branch_id,department_id,designation_id,reporting_manager_id',
                'employee.company:id,name',
                'employee.branch:id,name',
                'employee.department:id,name',
                'employee.designation:id,name',
                'employee.reportingManager:id,name',
                'company:id,name',
                'branch:id,name',
                'department:id,name',
                'designation:id,name',
                'employmentType:id,name',
                'reportingManager:id,name',
                'creator:id,name',
                'updater:id,name',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->integer('company_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->integer('section_id'));
        }

        if ($request->filled('designation_id')) {
            $query->where('designation_id', $request->integer('designation_id'));
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->string('event_type'));
        }

        if ($request->filled('employee')) {
            $query->where('user_id', $request->integer('employee'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('effective_from', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('effective_from', '<=', $request->string('date_to'));
        }

        $movements = $query->latest('effective_from')->paginate(15);

        return Inertia::render('HR/EmploymentMovements/Index', [
            'movements' => $movements,
            'employees' => User::query()
                ->select('id', 'employee_id', 'name', 'company_id', 'branch_id', 'department_id', 'designation_id', 'reporting_manager_id')
                ->orderBy('employee_id')
                ->get(),
            'companies' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_COMPANY)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'branches' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_BRANCH)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'departments' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DEPARTMENT)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'sections' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_SECTION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'designations' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'employmentTypes' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_EMPLOYEE_TYPE)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'eventTypes' => EmploymentMovementType::options(),
            'filters' => $request->only([
                'search',
                'company_id',
                'branch_id',
                'department_id',
                'section_id',
                'designation_id',
                'event_type',
                'employee',
                'date_from',
                'date_to',
            ]),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employees = User::query()
            ->select('id', 'employee_id', 'name', 'company_id', 'branch_id', 'cluster_id', 'division_id', 'department_id', 'section_id', 'unit_id', 'designation_id', 'employment_type_id', 'reporting_manager_id')
            ->orderBy('employee_id')
            ->get();

        $preselectedEmployee = null;

        if ($request->filled('employee_id')) {
            $preselectedEmployee = User::query()
                ->select('id', 'employee_id', 'name', 'company_id', 'branch_id', 'cluster_id', 'division_id', 'department_id', 'section_id', 'unit_id', 'designation_id', 'employment_type_id', 'reporting_manager_id')
                ->find($request->integer('employee_id'));
        }

        return Inertia::render('HR/EmploymentMovements/Create', [
            'employees' => $employees,
            'preselectedEmployee' => $preselectedEmployee,
            'companies' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_COMPANY)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'branches' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_BRANCH)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'clusters' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_CLUSTER)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'divisions' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DIVISION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'departments' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DEPARTMENT)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'sections' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_SECTION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'units' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_UNIT)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'parent_code']),
            'designations' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'employmentTypes' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_EMPLOYEE_TYPE)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'managers' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
            'eventTypes' => EmploymentMovementType::options(),
        ]);
    }

    public function store(StoreEmploymentMovementRequest $request): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = User::findOrFail($request->employee_id);

        if ($request->event_type === EmploymentMovementType::InitialAppointment->value) {
            $this->service->createInitialAppointment(
                $employee,
                $request->validated(),
                auth()->id()
            );
        } else {
            $type = EmploymentMovementType::from($request->event_type);

            $this->service->createMovement(
                $employee,
                $type,
                $request->validated(),
                auth()->id()
            );
        }

        return redirect()
            ->route('hr.employment-movements.index')
            ->with('success', 'Employment movement recorded successfully.');
    }

    public function show(EmployeeEmploymentHistory $movement): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $movement->load([
            'employee',
            'company',
            'branch',
            'cluster',
            'division',
            'department',
            'section',
            'unit',
            'designation',
            'employmentType',
            'reportingManager',
            'creator',
            'updater',
        ]);

        return Inertia::render('HR/EmploymentMovements/Show', [
            'movement' => $movement,
        ]);
    }

    public function edit(EmployeeEmploymentHistory $movement): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        if ($movement->effective_to) {
            return redirect()
                ->route('hr.employment-movements.index')
                ->with('error', 'Historical employment records cannot be edited.');
        }

        $movement->load('employee');

        return Inertia::render('HR/EmploymentMovements/Edit', [
            'movement' => $movement,
            'companies' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_COMPANY)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'branches' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_BRANCH)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'clusters' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_CLUSTER)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'divisions' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DIVISION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'departments' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DEPARTMENT)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'sections' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_SECTION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'units' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_UNIT)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'parent_code']),
            'designations' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_DESIGNATION)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'employmentTypes' => MasterDataItem::query()
                ->where('category', MasterDataItem::CATEGORY_EMPLOYEE_TYPE)
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'managers' => User::query()
                ->select('id', 'employee_id', 'name')
                ->orderBy('employee_id')
                ->get(),
            'eventTypes' => EmploymentMovementType::options(),
        ]);
    }

    public function update(
        UpdateEmploymentMovementRequest $request,
        EmployeeEmploymentHistory $movement
    ): RedirectResponse {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        if ($movement->effective_to) {
            return redirect()
                ->back()
                ->with('error', 'Historical employment records cannot be edited.');
        }

        $movement->update(array_merge(
            $request->validated(),
            ['updated_by' => auth()->id()]
        ));

        return redirect()
            ->route('hr.employment-movements.index')
            ->with('success', 'Employment movement updated successfully.');
    }

    public function history(Request $request, User $employee): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $query = $this->service->historyQuery($employee);

        if ($request->filled('movement_type')) {
            $query->where('event_type', $request->string('movement_type'));
        }

        $history = $query->orderByDesc('effective_from')->get();

        return Inertia::render('HR/EmploymentMovements/History', [
            'employee' => $employee,
            'history' => $history,
            'filters' => [
                'movement_type' => $request->string('movement_type')->toString(),
            ],
            'eventTypes' => EmploymentMovementType::options(),
        ]);
    }

    public function destroy(EmployeeEmploymentHistory $movement): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        if ($movement->effective_to) {
            return back()->with('error', 'Historical employment records cannot be deleted.');
        }

        $movement->delete();

        return back()->with('success', 'Employment movement removed successfully.');
    }
}
