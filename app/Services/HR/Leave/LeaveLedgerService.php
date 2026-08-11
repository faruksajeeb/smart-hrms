<?php

namespace App\Services\HR\Leave;

use App\Models\LeaveBalanceLedger;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LeaveLedgerService
{
    public function __construct(
        protected LeaveBalanceService $balanceService,
    ) {}

    /**
     * Create an adjustment ledger entry.
     */
    public function createAdjustment(
        User $employee,
        LeaveType $leaveType,
        float $days,
        string $reason,
        ?string $remarks = null,
        ?int $performedBy = null
    ): LeaveBalanceLedger {
        $currentBalance = $this->balanceService->getBalance($employee, $leaveType, now()->format('Y-m-d')) ?? 0;
        $newBalance = $currentBalance + $days;

        return $this->balanceService->createLedgerEntry([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'transaction_type' => 'adjustment',
            'transaction_reference' => 'ADJ-' . time() . '-' . $employee->id,
            'transaction_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'days' => $days,
            'credit_days' => $days > 0 ? $days : 0,
            'debit_days' => $days < 0 ? abs($days) : 0,
            'balance_after' => $newBalance,
            'remarks' => $reason . ($remarks ? ' - ' . $remarks : ''),
            'transaction_source' => 'manual',
            'performed_by' => $performedBy ?? Auth::id(),
        ]);
    }

    /**
     * Create a carry forward ledger entry.
     */
    public function createCarryForward(
        User $employee,
        LeaveType $leaveType,
        float $days,
        ?int $performedBy = null
    ): LeaveBalanceLedger {
        $currentBalance = $this->balanceService->getBalance($employee, $leaveType, now()->format('Y-m-d')) ?? 0;
        $newBalance = $currentBalance + $days;

        return $this->balanceService->createLedgerEntry([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'transaction_type' => 'carry_forward',
            'transaction_reference' => 'CF-' . date('Y') . '-' . $employee->id . '-' . $leaveType->id,
            'transaction_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'days' => $days,
            'credit_days' => $days,
            'debit_days' => 0,
            'balance_after' => $newBalance,
            'remarks' => 'Year-end carry forward',
            'transaction_source' => 'system',
            'performed_by' => $performedBy ?? Auth::id(),
        ]);
    }

    /**
     * Create an expiry ledger entry.
     */
    public function createExpiry(
        User $employee,
        LeaveType $leaveType,
        float $days,
        ?string $remarks = null,
        ?int $performedBy = null
    ): LeaveBalanceLedger {
        $currentBalance = $this->balanceService->getBalance($employee, $leaveType, now()->format('Y-m-d')) ?? 0;
        $newBalance = $currentBalance - $days;

        return $this->balanceService->createLedgerEntry([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'transaction_type' => 'expiry',
            'transaction_reference' => 'EXP-' . date('Y') . '-' . $employee->id . '-' . $leaveType->id,
            'transaction_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'days' => -$days,
            'credit_days' => 0,
            'debit_days' => $days,
            'balance_after' => $newBalance,
            'remarks' => $remarks ?: 'Expired leave balance',
            'transaction_source' => 'system',
            'performed_by' => $performedBy ?? Auth::id(),
        ]);
    }

    /**
     * Create an encashment ledger entry.
     */
    public function createEncashment(
        User $employee,
        LeaveType $leaveType,
        float $days,
        ?string $remarks = null,
        ?int $performedBy = null
    ): LeaveBalanceLedger {
        $currentBalance = $this->balanceService->getBalance($employee, $leaveType, now()->format('Y-m-d')) ?? 0;
        $newBalance = $currentBalance - $days;

        return $this->balanceService->createLedgerEntry([
            'user_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'transaction_type' => 'encashment',
            'transaction_reference' => 'ENC-' . time() . '-' . $employee->id,
            'transaction_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'days' => -$days,
            'credit_days' => 0,
            'debit_days' => $days,
            'balance_after' => $newBalance,
            'remarks' => $remarks ?: 'Leave encashment',
            'transaction_source' => 'manual',
            'performed_by' => $performedBy ?? Auth::id(),
        ]);
    }
}