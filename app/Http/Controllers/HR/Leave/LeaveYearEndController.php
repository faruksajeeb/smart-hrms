<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Models\LeaveYearEndProcess;
use App\Models\MasterDataItem;
use App\Models\User;
use App\Services\HR\Leave\LeaveYearEndProcessingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveYearEndController extends Controller
{
    public function __construct(protected LeaveYearEndProcessingService $service) {}

    public function index(Request $request): Response
    {
        return Inertia::render('HR/Leave/YearEnd/Index', [
            'filters' => $request->only(['processing_year', 'target_year', 'company_id', 'branch_id', 'division_id', 'department_id', 'section_id', 'unit_id', 'leave_type_id', 'employee', 'auto_encashment']),
            'years' => range(now()->year - 3, now()->year + 5),
            'options' => $this->options($request->user()),
            'recentProcesses' => LeaveYearEndProcess::withCount('items')->latest()->limit(10)->get(),
        ]);
    }

    public function preview(Request $request)
    {
        $filters = $this->validated($request);
  
        return response()->json($this->service->preview($request->user(), $filters));
    }

    public function process(Request $request)
    {
        $filters = $this->validated($request);
        $process = $this->service->createProcess($request->user(), $filters);
        return response()->json(['id' => $process->id, 'status' => $process->status, 'message' => 'Year-end processing completed successfully.']);
    }

    public function show(Request $request, LeaveYearEndProcess $process): Response
    {
        abort_unless($request->user()->hasRole(User::ROLE_ADMIN)
            || ($process->company_id === null || $process->company_id === $request->user()->company_id)
            && ($process->branch_id === null || $process->branch_id === $request->user()->branch_id), 403);
        return Inertia::render('HR/Leave/YearEnd/Show', ['process' => $process->load(['items.employee', 'items.leaveType', 'items.leavePolicy', 'processor'])]);
    }

    public function export(Request $request, LeaveYearEndProcess $process)
    {
        abort_unless($request->user()->hasRole(User::ROLE_ADMIN)
            || (($process->company_id === null || $process->company_id === $request->user()->company_id)
            && ($process->branch_id === null || $process->branch_id === $request->user()->branch_id)), 403);

        return response()->streamDownload(function () use ($process) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Employee', 'Employee ID', 'Leave Type', 'Policy', 'Previous Balance', 'Carry Forward', 'Expired', 'Encashed', 'Final Balance']);
            $process->items()->with(['employee:id,name,employee_id', 'leaveType:id,leave_name', 'leavePolicy:id,policy_name'])->chunkById(500, function ($items) use ($handle) {
                foreach ($items as $item) {
                    fputcsv($handle, [$item->employee?->name, $item->employee?->employee_id, $item->leaveType?->leave_name, $item->leavePolicy?->policy_name, $item->previous_balance, $item->eligible_carry_forward, $item->expired_days, $item->encashment_days, $item->closing_balance]);
                }
            });
            fclose($handle);
        }, "year-end-{$process->processing_year}-{$process->target_year}.csv", ['Content-Type' => 'text/csv']);
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'processing_year' => ['required', 'integer', 'between:1970,2100'],
            'target_year' => ['required', 'integer', 'between:1971,2101'],
            'company_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'branch_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'division_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'department_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'section_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'unit_id' => ['nullable', 'integer', 'exists:master_data_items,id'],
            'leave_type_id' => ['nullable', 'integer', 'exists:leave_types,id'],
            'employee' => ['nullable', 'string', 'max:100'],
            'auto_encashment' => ['boolean'],
        ]);
    }

    protected function options(User $actor): array
    {
        $item = fn (string $category) => MasterDataItem::query()->where('category', $category)->where('status', 'active')
            ->when(!$actor->hasRole(User::ROLE_ADMIN) && $category === MasterDataItem::CATEGORY_COMPANY, fn ($q) => $q->whereKey($actor->company_id))
            ->when(!$actor->hasRole(User::ROLE_ADMIN) && $category === MasterDataItem::CATEGORY_BRANCH, fn ($q) => $q->whereKey($actor->branch_id))
            ->orderBy('name')->get(['id', 'parent_id', 'name', 'code']);
        return [
            'companies' => $item(MasterDataItem::CATEGORY_COMPANY), 'branches' => $item(MasterDataItem::CATEGORY_BRANCH),
            'divisions' => $item(MasterDataItem::CATEGORY_DIVISION), 'departments' => $item(MasterDataItem::CATEGORY_DEPARTMENT),
            'sections' => $item(MasterDataItem::CATEGORY_SECTION), 'units' => $item(MasterDataItem::CATEGORY_UNIT),
            'leaveTypes' => LeaveType::where('status', 'active')->orderBy('leave_name')->get(['id', 'leave_name', 'leave_code']),
        ];
    }
}
