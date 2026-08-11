<?php

namespace App\Services\HR\Leave;

use App\Enums\LeaveTransactionType;
use App\Models\LeaveBalanceLedger;
use App\Models\LeaveOpeningBalance;
use App\Models\LeavePolicyAssignment;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveBalanceService
{
    public function __construct(
        protected LeavePolicyResolver $policyResolver,
    ) {}

    public function createLedgerEntry(array $data): LeaveBalanceLedger
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $employee = User::find($data['user_id']);
            $leaveType = LeaveType::find($data['leave_type_id']);

            $days = (float) ($data['days'] ?? 0);
            $creditDays = $days > 0 ? $days : 0;
            $debitDays = $days < 0 ? abs($days) : 0;

            $assignment = LeavePolicyAssignment::where('user_id', $data['user_id'])
                ->where(function ($query) use ($data) {
                    $query->whereNull('effective_to')
                        ->orWhere('effective_to', '>=', $data['transaction_date'] ?? now()->toDateString());
                })
                ->where('status', 'active')
                ->latest('effective_from')
                ->first();

            $ledger = LeaveBalanceLedger::create([
                'user_id' => $data['user_id'],
                'leave_type_id' => $data['leave_type_id'],
                'transaction_type' => $data['transaction_type'],
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'transaction_reference' => $data['transaction_reference'] ?? null,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'effective_date' => $data['effective_date'] ?? $data['transaction_date'] ?? now()->toDateString(),
                'days' => $days,
                'credit_days' => $creditDays,
                'debit_days' => $debitDays,
                'balance_after' => $data['balance_after'],
                'remarks' => $data['remarks'] ?? null,
                'transaction_source' => $data['transaction_source'] ?? 'system',
                'company_id' => $data['company_id'] ?? ($employee?->company_id),
                'branch_id' => $data['branch_id'] ?? ($employee?->branch_id),
                'division_id' => $data['division_id'] ?? ($employee?->division_id),
                'department_id' => $data['department_id'] ?? ($employee?->department_id),
                'section_id' => $data['section_id'] ?? ($employee?->section_id),
                'unit_id' => $data['unit_id'] ?? ($employee?->unit_id),
                'designation_id' => $data['designation_id'] ?? ($employee?->designation_id),
                'employment_type' => $data['employment_type'] ?? ($employee?->employment_type),
                'leave_policy_id' => $data['leave_policy_id'] ?? ($assignment?->leave_policy_id),
                'performed_by' => $data['performed_by'] ?? Auth::id(),
                'approved_by' => $data['approved_by'] ?? null,
                'processed_by' => $data['processed_by'] ?? null,
                'created_by' => Auth::id(),
            ]);

            return $ledger;
        });
    }

    public function getBalance(User $user, LeaveType $leaveType, ?string $asOfDate = null): ?float
    {
        $query = LeaveBalanceLedger::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id);

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        $latest = $query->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $latest ? (float) $latest->balance_after : null;
    }

    public function getAvailableBalance(User $user, LeaveType $leaveType, ?string $asOfDate = null): ?float
    {
        $balance = $this->getBalance($user, $leaveType, $asOfDate);
        if ($balance === null) {
            return null;
        }

        // TODO: Subtract pending leave reservations if implemented
        return $balance;
    }

    public function getLedger(User $user, LeaveType $leaveType, ?Carbon $from = null, ?Carbon $to = null)
    {
        $query = LeaveBalanceLedger::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc');

        if ($from) {
            $query->where('transaction_date', '>=', $from->format('Y-m-d'));
        }

        if ($to) {
            $query->where('transaction_date', '<=', $to->format('Y-m-d'));
        }

        return $query->get();
    }

    public function getBalanceBreakdown(User $user, LeaveType $leaveType, ?string $asOfDate = null): array
    {
        $query = LeaveBalanceLedger::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id);

        if ($asOfDate) {
            $query->where('transaction_date', '<=', $asOfDate);
        }

        $breakdown = [
            'opening' => 0,
            'accrued' => 0,
            'carry_forward' => 0,
            'used' => 0,
            'cancelled' => 0,
            'adjustment' => 0,
            'expired' => 0,
            'encashment' => 0,
            'available' => 0,
        ];

        $ledgers = $query->get();

        foreach ($ledgers as $ledger) {
            switch ($ledger->transaction_type) {
                case 'opening':
                    $breakdown['opening'] += $ledger->credit_days;
                    break;
                case 'accrual':
                    $breakdown['accrued'] += $ledger->credit_days;
                    break;
                case 'carry_forward':
                    $breakdown['carry_forward'] += $ledger->credit_days;
                    break;
                case 'leave_approved':
                    $breakdown['used'] += $ledger->debit_days;
                    break;
                case 'leave_cancelled':
                    $breakdown['cancelled'] += $ledger->credit_days;
                    break;
                case 'adjustment':
                    $breakdown['adjustment'] += $ledger->days;
                    break;
                case 'expiry':
                    $breakdown['expired'] += $ledger->debit_days;
                    break;
                case 'encashment':
                    $breakdown['encashment'] += $ledger->debit_days;
                    break;
            }
        }

        $breakdown['available'] = $breakdown['opening'] + $breakdown['accrued'] + $breakdown['carry_forward'] 
            - $breakdown['used'] - $breakdown['cancelled'] - $breakdown['expired'] - $breakdown['encashment']
            + $breakdown['adjustment'];

        return $breakdown;
    }

    public function importOpeningBalances(array $rows, string $effectiveDate, ?string $reason = null, ?int $userId = null): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $user = auth()->user();

        foreach ($rows as $index => $row) {
            try {
                $employeeCode = trim($row['employee_code'] ?? $row['employee_id'] ?? '');
                $leaveCode = trim($row['leave_code'] ?? $row['leave_type_code'] ?? '');
                $openingBalance = isset($row['opening_balance']) ? (float) $row['opening_balance'] : null;
                $remarks = trim($row['remarks'] ?? '');

                if (!$employeeCode || !$leaveCode || is_null($openingBalance)) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 2,
                        'message' => 'Missing required fields: employee_code, leave_code, opening_balance',
                    ];
                    continue;
                }

                $employee = User::where('employee_id', $employeeCode)->first();
                if (!$employee) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 2,
                        'message' => "Employee not found: {$employeeCode}",
                    ];
                    continue;
                }

                $leaveType = LeaveType::where('leave_code', $leaveCode)->first();
                if (!$leaveType) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 2,
                        'message' => "Leave type not found: {$leaveCode}",
                    ];
                    continue;
                }

                $currentBalance = $this->getBalance($employee, $leaveType, $effectiveDate) ?? 0;
                $newBalance = $currentBalance + $openingBalance;

                \Illuminate\Support\Facades\DB::transaction(function () use ($employee, $leaveType, $openingBalance, $effectiveDate, $remarks, $newBalance, $userId) {
                    $balance = LeaveOpeningBalance::create([
                        'user_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'opening_balance' => $openingBalance,
                        'effective_date' => $effectiveDate,
                        'remarks' => $remarks ?: 'Imported from CSV',
                        'reason' => 'CSV Import',
                        'created_by' => $userId ?? auth()->id(),
                        'updated_by' => $userId ?? auth()->id(),
                    ]);

                    $this->createLedgerEntry([
                        'user_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'transaction_type' => 'opening',
                        'reference_type' => LeaveOpeningBalance::class,
                        'reference_id' => $balance->id,
                        'transaction_reference' => 'IMP-' . $balance->id,
                        'transaction_date' => $effectiveDate,
                        'effective_date' => $effectiveDate,
                        'days' => $openingBalance,
                        'balance_after' => $newBalance,
                        'remarks' => $remarks ?: 'Imported from CSV',
                        'transaction_source' => 'import',
                    ]);
                });

                $results['success']++;
            } catch (\Throwable $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'row' => $index + 2,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function getAllBalances(User $user): array
    {
        $latestLedgers = LeaveBalanceLedger::where('user_id', $user->id)
            ->orderBy('leave_type_id')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('leave_type_id');

        $balances = [];
        foreach ($latestLedgers as $ledger) {
            $leaveType = $ledger->leaveType;
            if (!$leaveType) {
                continue;
            }

            $balances[] = [
                'leave_type_id' => $ledger->leave_type_id,
                'leave_name' => $leaveType->leave_name,
                'leave_code' => $leaveType->leave_code,
                'balance' => (float) $ledger->balance_after,
                'last_transaction_date' => $ledger->transaction_date,
            ];
        }

        return $balances;
    }

    public function syncOpeningBalances(): array
    {
        $results = [
            'success' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $openingBalances = LeaveOpeningBalance::with(['employee', 'leaveType'])->get();

        foreach ($openingBalances as $balance) {
            try {
                $exists = LeaveBalanceLedger::where('user_id', $balance->user_id)
                    ->where('leave_type_id', $balance->leave_type_id)
                    ->where('transaction_type', 'opening')
                    ->where('reference_type', LeaveOpeningBalance::class)
                    ->where('reference_id', $balance->id)
                    ->exists();

                if ($exists) {
                    $results['skipped']++;
                    continue;
                }

                $this->createLedgerEntry([
                    'user_id' => $balance->user_id,
                    'leave_type_id' => $balance->leave_type_id,
                    'transaction_type' => 'opening',
                    'reference_type' => LeaveOpeningBalance::class,
                    'reference_id' => $balance->id,
                    'transaction_reference' => 'SYNC-' . $balance->id,
                    'transaction_date' => $balance->effective_date,
                    'effective_date' => $balance->effective_date,
                    'days' => $balance->opening_balance,
                    'balance_after' => $balance->opening_balance,
                    'remarks' => $balance->remarks ?: 'Synced from opening balance',
                    'transaction_source' => 'system',
                ]);

                $results['success']++;
            } catch (\Throwable $e) {
                $results['errors'][] = [
                    'balance_id' => $balance->id,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}