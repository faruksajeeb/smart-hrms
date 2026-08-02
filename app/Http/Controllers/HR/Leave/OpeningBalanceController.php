<?php

namespace App\Http\Controllers\HR\Leave;

use App\Http\Controllers\Controller;
use App\Models\LeaveOpeningBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\HR\Leave\LeaveBalanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

use App\Http\Requests\HR\Leave\StoreOpeningBalanceRequest;
use App\Http\Requests\HR\Leave\UpdateOpeningBalanceRequest;
use App\Http\Requests\HR\Leave\ImportOpeningBalanceRequest;

class OpeningBalanceController extends Controller
{
    public function __construct(
        protected LeaveBalanceService $service
    ) {}

    public function index(Request $request): Response
    {
        $query = LeaveOpeningBalance::query()->with(['employee', 'leaveType']);

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->integer('employee_id'));
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->integer('leave_type_id'));
        }

        $balances = $query->orderBy('effective_date', 'desc')->paginate(15);

        return Inertia::render('HR/Leave/OpeningBalances/Index', [
            'balances' => $balances,
        ]);
    }

    public function create(): Response
    {
        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $users = User::where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        return Inertia::render('HR/Leave/OpeningBalances/Create', [
            'leaveTypes' => $leaveTypes,
            'users' => $users,
        ]);
    }

    public function store(StoreOpeningBalanceRequest $request)
    {
        $validated = $request->validated();

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            $balance = \App\Models\LeaveOpeningBalance::create([
                'user_id' => $validated['user_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'opening_balance' => $validated['opening_balance'],
                'effective_date' => $validated['effective_date'],
                'remarks' => $validated['remarks'] ?? null,
                'reason' => $validated['reason'] ?? null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $currentBalance = $this->service->getBalance(
                \App\Models\User::find($validated['user_id']),
                \App\Models\LeaveType::find($validated['leave_type_id']),
                $validated['effective_date']
            ) ?? 0;

            $newBalance = $currentBalance + $validated['opening_balance'];

            $this->service->createLedgerEntry([
                'user_id' => $validated['user_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'transaction_type' => 'opening',
                'reference_type' => LeaveOpeningBalance::class,
                'reference_id' => $balance->id,
                'transaction_date' => $validated['effective_date'],
                'days' => $validated['opening_balance'],
                'balance_after' => $newBalance,
                'remarks' => $validated['remarks'] ?? null,
            ]);
        });

        return redirect()
            ->route('hr.leave.opening-balances.index')
            ->with('success', 'Opening balance created successfully.');
    }

    public function show(LeaveOpeningBalance $openingBalance): Response
    {
        $openingBalance->load(['employee', 'leaveType', 'creator', 'updater']);

        return Inertia::render('HR/Leave/OpeningBalances/Show', [
            'balance' => $openingBalance,
        ]);
    }

    public function edit(LeaveOpeningBalance $openingBalance): Response
    {
        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        $users = User::where('status', User::STATUS_ACTIVE)
            ->orderBy('name')
            ->get(['id', 'name', 'employee_id']);

        return Inertia::render('HR/Leave/OpeningBalances/Edit', [
            'balance' => $openingBalance,
            'leaveTypes' => $leaveTypes,
            'users' => $users,
        ]);
    }

    public function update(UpdateOpeningBalanceRequest $request, LeaveOpeningBalance $openingBalance)
    {
        $validated = $request->validated();

        $openingBalance->update([
            'user_id' => $validated['user_id'],
            'leave_type_id' => $validated['leave_type_id'],
            'opening_balance' => $validated['opening_balance'],
            'effective_date' => $validated['effective_date'],
            'remarks' => $validated['remarks'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        return redirect()
            ->route('hr.leave.opening-balances.index')
            ->with('success', 'Opening balance updated successfully.');
    }

    public function destroy(LeaveOpeningBalance $openingBalance)
    {
        $openingBalance->delete();

        return back()->with('success', 'Opening balance deleted successfully.');
    }

    public function sync()
    {
        $results = $this->service->syncOpeningBalances();

        return back()->with('success', "Sync completed. Created: {$results['success']}, Skipped: {$results['skipped']}, Errors: " . count($results['errors']));
    }

    public function import(): Response
    {
        $leaveTypes = LeaveType::where('status', 'active')
            ->orderBy('display_order')
            ->orderBy('leave_name')
            ->get(['id', 'leave_name', 'leave_code']);

        return Inertia::render('HR/Leave/OpeningBalances/Import', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function preview(ImportOpeningBalanceRequest $request)
    {
        $file = $request->file('file');
        $path = $file->getRealPath();
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $header = fgetcsv($handle);
            if ($header) {
                while (($row = fgetcsv($handle)) !== false) {
                    $rows[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        $previewData = [];
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $entry = [
                'row' => $rowNum,
                'employee_code' => $row['employee_code'] ?? $row['employee_id'] ?? '',
                'employee_name' => '',
                'leave_code' => $row['leave_code'] ?? $row['leave_type_code'] ?? '',
                'leave_name' => '',
                'opening_balance' => $row['opening_balance'] ?? '',
                'remarks' => $row['remarks'] ?? '',
                'valid' => true,
                'errors' => [],
            ];

            $employee = User::where('employee_id', $entry['employee_code'])->first();
            if (!$employee) {
                $entry['valid'] = false;
                $entry['errors'][] = 'Employee not found';
            } else {
                $entry['employee_name'] = $employee->name;
            }

            $leaveType = LeaveType::where('leave_code', $entry['leave_code'])->first();
            if (!$leaveType) {
                $entry['valid'] = false;
                $entry['errors'][] = 'Leave type not found';
            } else {
                $entry['leave_name'] = $leaveType->leave_name;
            }

            if (!isset($row['opening_balance']) || $row['opening_balance'] === '' || !is_numeric($row['opening_balance'])) {
                $entry['valid'] = false;
                $entry['errors'][] = 'Invalid opening balance';
            }

            $previewData[] = $entry;

            if (!$entry['valid']) {
                $errors[] = [
                    'row' => $rowNum,
                    'message' => implode(', ', $entry['errors']),
                ];
            }
        }

        return Inertia::render('HR/Leave/OpeningBalances/ImportPreview', [
            'previewData' => $previewData,
            'summary' => [
                'total' => count($rows),
                'valid' => count(array_filter($previewData, fn ($item) => $item['valid'])),
                'invalid' => count(array_filter($previewData, fn ($item) => !$item['valid'])),
            ],
            'errors' => $errors,
            'effective_date' => $request->input('effective_date'),
            'reason' => $request->input('reason'),
        ]);
    }

    public function commit(Request $request)
    {
        $request->validate([
            'rows' => ['required', 'array'],
            'rows.*' => ['array'],
            'effective_date' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $validRows = collect($request->input('rows', [][]))->filter(fn ($row) => !empty($row['employee_code']) && !empty($row['leave_code']) && isset($row['opening_balance']))->values()->toArray();

        $results = $this->service->importOpeningBalances(
            $validRows,
            $request->input('effective_date'),
            $request->input('reason')
        );

        return redirect()
            ->route('hr.leave.opening-balances.index')
            ->with('success', "Import completed. Success: {$results['success']}, Failed: {$results['failed']}")
            ->with('import_errors', $results['errors']);
    }
}
