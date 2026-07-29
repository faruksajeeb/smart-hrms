<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\EmployeeTransfer\StoreEmployeeTransferRequest;
use App\Http\Requests\HR\EmployeeTransfer\UpdateEmployeeTransferRequest;
use App\Models\EmployeeTransfer;
use App\Models\MasterDataItem;
use App\Models\User;
use App\Services\HR\EmployeeTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeTransferController extends Controller
{
    public function __construct(
        protected EmployeeTransferService $service
    ) {}

    public function index(Request $request): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $query = EmployeeTransfer::query()
            ->with([
                'employee' => function ($q) {
                    $q->select('id', 'employee_id', 'name', 'company_id', 'branch_id', 'cluster_id', 'division_id', 'department_id', 'section_id', 'unit_id')
                      ->with('masterDataItems');
                },
                'employee.company:id,name',
                'employee.branch:id,name',
                'employee.cluster:id,name',
                'employee.division:id,name',
                'employee.department:id,name',
                'employee.section:id,name',
                'employee.unit:id,name',
                'toCompany:id,name',
                'toBranch:id,name',
                'toCluster:id,name',
                'toDivision:id,name',
                'toDepartment:id,name',
                'toSection:id,name',
                'toUnit:id,name',
                'approver:id,name',
            ]);

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('to_company_id', $request->integer('company_id'));
        }

        if ($request->filled('branch_id')) {
            $query->where('to_branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('department_id')) {
            $query->where('to_department_id', $request->integer('department_id'));
        }

        if ($request->filled('section_id')) {
            $query->where('to_section_id', $request->integer('section_id'));
        }

        if ($request->filled('status')) {
            $query->where('approval_status', $request->string('status'));
        }

        if ($request->filled('reason')) {
            $query->where('transfer_reason', $request->string('reason'));
        }

        if ($request->filled('effective_from')) {
            $query->whereDate('effective_from', $request->string('effective_from'));
        }

        $transfers = $query->latest('effective_from')->paginate(15);

        $transfers->getCollection()->transform(function ($transfer) {
            if ($transfer->employee && $transfer->employee->masterDataItems) {
                $transfer->employee->populateOrgFromPivot($transfer->employee->masterDataItems);
            }

            return $transfer;
        });

        return Inertia::render('HR/EmployeeTransfers/Index', [
            'transfers' => $transfers,
            'employees' => User::query()
                ->select('id', 'employee_id', 'name', 'company_id', 'branch_id', 'cluster_id', 'division_id', 'department_id', 'section_id', 'unit_id')
                ->with('masterDataItems')
                ->orderBy('employee_id')
                ->get()
                ->each(function ($user) {
                    $user->populateOrgFromPivot($user->masterDataItems);
                }),
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
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'pending', 'label' => 'Pending'],
                ['value' => 'approved', 'label' => 'Approved'],
                ['value' => 'rejected', 'label' => 'Rejected'],
            ],
            'reasons' => [
                ['value' => 'promotion', 'label' => 'Promotion'],
                ['value' => 'business_requirement', 'label' => 'Business Requirement'],
                ['value' => 'department_restructure', 'label' => 'Department Restructure'],
                ['value' => 'branch_relocation', 'label' => 'Branch Relocation'],
                ['value' => 'employee_request', 'label' => 'Employee Request'],
                ['value' => 'temporary_assignment', 'label' => 'Temporary Assignment'],
                ['value' => 'project_assignment', 'label' => 'Project Assignment'],
                ['value' => 'administrative_decision', 'label' => 'Administrative Decision'],
                ['value' => 'other', 'label' => 'Other'],
            ],
            'filters' => $request->only([
                'search',
                'company_id',
                'branch_id',
                'department_id',
                'section_id',
                'status',
                'reason',
                'effective_from',
            ]),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employees = User::with([
            'company:id,name',
            'branch:id,name',
            'cluster:id,name',
            'division:id,name',
            'department:id,name',
            'section:id,name',
            'unit:id,name',
            'masterDataItems',
        ])->select(
                'id',
                'employee_id',
                'name',
                'company_id',
                'branch_id',
                'cluster_id',
                'division_id',
                'department_id',
                'section_id',
                'unit_id',
            )
            ->orderBy('employee_id')
            ->get()
            ->each(function ($user) {
                $user->populateOrgFromPivot($user->masterDataItems);
            });

        $preselectedEmployee = null;

        if ($request->filled('employee_id')) {
            $preselectedEmployee = User::query()
                ->with([
                    'company:id,name',
                    'branch:id,name',
                    'cluster:id,name',
                    'division:id,name',
                    'department:id,name',
                    'section:id,name',
                    'unit:id,name',
                    'masterDataItems',
                ])
                ->select(
                    'id',
                    'employee_id',
                    'name',
                    'company_id',
                    'branch_id',
                    'cluster_id',
                    'division_id',
                    'department_id',
                    'section_id',
                    'unit_id',
                )
                ->find($request->integer('employee_id'));

            if ($preselectedEmployee) {
                $preselectedEmployee->populateOrgFromPivot($preselectedEmployee->masterDataItems);
            }
        }

        return Inertia::render('HR/EmployeeTransfers/Create', [
            'employees' => $employees,
            'preselectedEmployee' => $preselectedEmployee,
            'companies' => MasterDataItem::query()
                ->where('category', 'company')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'branches' => MasterDataItem::query()
                ->where('category', 'branch')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'clusters' => MasterDataItem::query()
                ->where('category', 'cluster')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'divisions' => MasterDataItem::query()
                ->where('category', 'division')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'departments' => MasterDataItem::query()
                ->where('category', 'department')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'sections' => MasterDataItem::query()
                ->where('category', 'section')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'units' => MasterDataItem::query()
                ->where('category', 'unit')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'parent_code']),
        ]);
    }

    public function store(StoreEmployeeTransferRequest $request): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $employee = User::with(['company','branch','cluster','division','department','section','unit','masterDataItems'])->findOrFail($request->employee_id);

        $employee->populateOrgFromPivot($employee->masterDataItems);

        $this->service->store(
            $employee,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.transfers.index')
            ->with('success', 'Transfer created successfully.');
    }

    public function show(EmployeeTransfer $transfer): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $transfer->load([
            'employee',
            'fromCompany',
            'toCompany',
            'fromBranch',
            'toBranch',
            'fromCluster',
            'toCluster',
            'fromDivision',
            'toDivision',
            'fromDepartment',
            'toDepartment',
            'fromSection',
            'toSection',
            'fromUnit',
            'toUnit',
            'approver',
            'creator',
            'updater',
        ]);

        return Inertia::render('HR/EmployeeTransfers/Show', [
            'transfer' => $transfer,
        ]);
    }

    public function edit(EmployeeTransfer $transfer): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        if ($transfer->approval_status === EmployeeTransfer::STATUS_APPROVED) {
            return redirect()
                ->route('hr.transfers.index')
                ->with('error', 'Approved transfers cannot be edited.');
        }

        $transfer->load('employee');

        if ($transfer->employee && $transfer->employee->masterDataItems) {
            $transfer->employee->populateOrgFromPivot($transfer->employee->masterDataItems);
        }

        return Inertia::render('HR/EmployeeTransfers/Edit', [
            'transfer' => $transfer,
            'companies' => MasterDataItem::query()
                ->where('category', 'company')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'branches' => MasterDataItem::query()
                ->where('category', 'branch')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'clusters' => MasterDataItem::query()
                ->where('category', 'cluster')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'divisions' => MasterDataItem::query()
                ->where('category', 'division')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'departments' => MasterDataItem::query()
                ->where('category', 'department')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'sections' => MasterDataItem::query()
                ->where('category', 'section')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code']),
            'units' => MasterDataItem::query()
                ->where('category', 'unit')
                ->where('status', MasterDataItem::STATUS_ACTIVE)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'parent_code']),
        ]);
    }

    public function update(
        UpdateEmployeeTransferRequest $request,
        EmployeeTransfer $transfer
    ): RedirectResponse {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $this->service->update(
            $transfer,
            $request->validated(),
            auth()->id()
        );

        return redirect()
            ->route('hr.transfers.index')
            ->with('success', 'Transfer updated successfully.');
    }

    public function approve(Request $request, EmployeeTransfer $transfer): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $transfer->update([
            'approval_status' => EmployeeTransfer::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'updated_by' => auth()->id(),
        ]);

        $this->applyTransfer($transfer);

        return back()->with('success', 'Transfer approved successfully.');
    }

    public function reject(Request $request, EmployeeTransfer $transfer): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $transfer->update([
            'approval_status' => EmployeeTransfer::STATUS_REJECTED,
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'Transfer rejected successfully.');
    }

    public function history(User $employee): Response
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $history = $this->service->history($employee);

        return Inertia::render('HR/EmployeeTransfers/History', [
            'employee' => $employee,
            'history' => $history,
        ]);
    }

    public function destroy(EmployeeTransfer $transfer): RedirectResponse
    {
        abort_unless(
            auth()->user()->can('manage attendance'),
            403
        );

        $this->service->delete($transfer);

        return back()->with('success', 'Transfer removed successfully.');
    }

    protected function applyTransfer(EmployeeTransfer $transfer): void
    {
        $employee = $transfer->employee()->first();

        if (!$employee) {
            return;
        }

        $employee->update([
            'company_id' => $transfer->to_company_id,
            'branch_id' => $transfer->to_branch_id,
            'cluster_id' => $transfer->to_cluster_id,
            'division_id' => $transfer->to_division_id,
            'department_id' => $transfer->to_department_id,
            'section_id' => $transfer->to_section_id,
            'unit_id' => $transfer->to_unit_id,
            'updated_by' => auth()->id(),
        ]);

        $newEffectiveFrom = \Carbon\Carbon::parse($transfer->effective_from);

        $employee->currentTransfer()->update([
            'effective_to' => $newEffectiveFrom->copy()->subDay(),
            'updated_by' => auth()->id(),
        ]);
    }
}
