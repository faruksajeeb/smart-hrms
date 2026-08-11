<?php

namespace App\Services\HR\Leave;

use App\Models\LeaveBalanceLedger;
use App\Models\LeavePolicyDetail;
use App\Models\LeavePolicyAssignment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveAccrualService
{
    public function __construct(
        protected LeavePolicyResolver $policyResolver,
        protected LeaveEligibilityService $eligibilityService,
        protected LeaveBalanceService $balanceService,
    ) {}

    /**
     * Calculate accrual for a specific employee, leave type and period.
     *
     * @param User $employee
     * @param LeavePolicyDetail $policyDetail
     * @param Carbon $period
     * @return float|null
     */
    public function accrue(User $employee, LeavePolicyDetail $policyDetail, Carbon $period): ?float
    {
        // Check if accrual is already processed for this period
        if ($this->isAccrualProcessed($employee, $policyDetail, $period)) {
            return null;
        }

        // Check if employee is eligible
        $eligibility = $this->eligibilityService->canApply($employee, $policyDetail->leave_type_id, $period);
        if (!$eligibility['eligible']) {
            return null;
        }

        // Check if accrual method is set
        $accrualMethod = $policyDetail->accrual_method;
        if (!$accrualMethod || $accrualMethod === \App\Enums\AccrualMethod::None->value) {
            return null;
        }

        // Check if employee is active
        if ($employee->status !== User::STATUS_ACTIVE) {
            return null;
        }

        // Calculate accrual amount
        $accrualAmount = $this->calculateAccrualAmount($employee, $policyDetail, $period);
        if ($accrualAmount <= 0) {
            return null;
        }

        // Check annual entitlement ceiling
        $accrualAmount = $this->applyAnnualEntitlementCeiling($employee, $policyDetail, $accrualAmount, $period);

        if ($accrualAmount <= 0) {
            return null;
        }

        return $accrualAmount;
    }

    /**
     * Process accrual for a period for eligible employees.
     *
     * @param Carbon $period
     * @param array $options
     * @return array
     */
    public function processPeriod(Carbon $period, array $options = []): array
    {
        $results = [
            'processed' => 0,
            'skipped' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        $batchSize = $options['batch_size'] ?? 100;
        $leavePolicyId = $options['leave_policy_id'] ?? null;

        $query = LeavePolicyAssignment::query()
            ->where('status', 'active')
            ->where('effective_from', '<=', $period->format('Y-m-d'))
            ->where(function ($query) use ($period) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $period->format('Y-m-d'));
            })
            ->with(['policy.details']);

        if ($leavePolicyId) {
            $query->where('leave_policy_id', $leavePolicyId);
        }

        $query->chunk($batchSize, function ($assignments) use ($period, &$results) {
            foreach ($assignments as $assignment) {
                try {
                    $this->processAssignment($assignment, $period, $results);
                } catch (\Throwable $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'assignment_id' => $assignment->id,
                        'employee_id' => $assignment->user_id,
                        'message' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Process a single policy assignment for a period.
     */
    private function processAssignment(LeavePolicyAssignment $assignment, Carbon $period, array &$results): void
    {
        if (!$assignment->user_id) {
            $results['skipped']++;
            return;
        }

        $employee = User::find($assignment->user_id);
        if (!$employee || $employee->status !== User::STATUS_ACTIVE) {
            $results['skipped']++;
            return;
        }

        foreach ($assignment->policy->details as $detail) {
            if ($detail->status !== 'active') {
                continue;
            }

            $accrualAmount = $this->accrue($employee, $detail, $period);
            if ($accrualAmount === null) {
                $results['skipped']++;
                continue;
            }

            DB::transaction(function () use ($employee, $detail, $accrualAmount, $period, $assignment) {
                $balanceAfter = $this->balanceService->getBalance($employee, $detail->leaveType, $period->format('Y-m-d')) ?? 0;
                $newBalance = $balanceAfter + $accrualAmount;

                $this->balanceService->createLedgerEntry([
                    'user_id' => $employee->id,
                    'leave_type_id' => $detail->leave_type_id,
                    'transaction_type' => 'accrual',
                    'transaction_reference' => $this->generateAccrualReference($employee, $detail, $period),
                    'transaction_date' => $period->format('Y-m-d'),
                    'effective_date' => $period->format('Y-m-d'),
                    'days' => $accrualAmount,
                    'credit_days' => $accrualAmount,
                    'debit_days' => 0,
                    'balance_after' => $newBalance,
                    'remarks' => 'Accrual for ' . $period->format('Y-m'),
                    'transaction_source' => 'cron',
                    'company_id' => $employee->company_id,
                    'branch_id' => $employee->branch_id,
                    'division_id' => $employee->division_id,
                    'department_id' => $employee->department_id,
                    'section_id' => $employee->section_id,
                    'unit_id' => $employee->unit_id,
                    'designation_id' => $employee->designation_id,
                    'employment_type' => $employee->employment_type,
                    'leave_policy_id' => $assignment->leave_policy_id,
                    'performed_by' => null,
                ]);
            });

            $results['processed']++;
        }
    }

    /**
     * Calculate accrual amount for an employee, policy detail and period.
     */
    private function calculateAccrualAmount(User $employee, LeavePolicyDetail $policyDetail, Carbon $period): float
    {
        $accrualMethod = $policyDetail->accrual_method;
        $monthlyAccrual = (float) ($policyDetail->monthly_accrual ?? 0);
        $annualEntitlement = (float) ($policyDetail->annual_entitlement ?? 0);

        if ($monthlyAccrual <= 0) {
            return 0;
        }

        $joiningDate = $employee->employeeProfile?->joining_date;
        if (!$joiningDate) {
            return 0;
        }

        $joining = Carbon::parse($joiningDate);

        // If joining date is in the future, no accrual
        if ($joining->gt($period->endOfMonth())) {
            return 0;
        }

        // Calculate prorated amount based on joining date
        $daysInMonth = $period->daysInMonth;
        $daysFromJoining = $joining->startOfDay()->diffInDays($period->endOfMonth()->startOfDay()) + 1;
        
        // If employee joined after the period end, no accrual
        if ($joining->gt($period->endOfMonth())) {
            return 0;
        }

        // Calculate actual days employee was present in the month
        $actualDays = min($daysFromJoining, $daysInMonth);

        // Proration calculation
        $proratedAmount = ($monthlyAccrual / $daysInMonth) * $actualDays;

        // Round to 2 decimal places
        return round($proratedAmount, 2);
    }

    /**
     * Apply annual entitlement ceiling.
     */
    private function applyAnnualEntitlementCeiling(User $employee, LeavePolicyDetail $policyDetail, float $accrualAmount, Carbon $period): float
    {
        $annualEntitlement = (float) ($policyDetail->annual_entitlement ?? 0);
        if ($annualEntitlement <= 0) {
            return $accrualAmount;
        }

        $yearStart = Carbon::create($period->year, 1, 1);
        $yearEnd = Carbon::create($period->year, 12, 31);

        // Calculate total accrued so far this year
        $totalAccrued = LeaveBalanceLedger::where('user_id', $employee->id)
            ->where('leave_type_id', $policyDetail->leave_type_id)
            ->where('transaction_type', 'accrual')
            ->where('transaction_date', '>=', $yearStart->format('Y-m-d'))
            ->where('transaction_date', '<=', $period->format('Y-m-d'))
            ->sum('credit_days');

        $remainingEntitlement = $annualEntitlement - $totalAccrued;

        if ($remainingEntitlement <= 0) {
            return 0;
        }

        return min($accrualAmount, $remainingEntitlement);
    }

    /**
     * Check if accrual is already processed for an employee, leave type and period.
     */
    private function isAccrualProcessed(User $employee, LeavePolicyDetail $policyDetail, Carbon $period): bool
    {
        $periodKey = $this->generateAccrualReference($employee, $policyDetail, $period);

        return LeaveBalanceLedger::where('user_id', $employee->id)
            ->where('leave_type_id', $policyDetail->leave_type_id)
            ->where('transaction_type', 'accrual')
            ->where('transaction_reference', $periodKey)
            ->exists();
    }

    /**
     * Generate a deterministic accrual reference for idempotency.
     */
    private function generateAccrualReference(User $employee, LeavePolicyDetail $policyDetail, Carbon $period): string
    {
        return sprintf(
            'ACCR-%s-%d-%d-%s',
            $employee->employee_id,
            $policyDetail->leave_type_id,
            $policyDetail->leave_policy_id,
            $period->format('Y-m')
        );
    }
}