<?php

namespace App\Services\HR\Leave;

use App\Models\LeaveBalanceLedger;
use App\Models\LeaveType;
use App\Models\LeaveYearEndProcess;
use App\Models\LeaveYearEndProcessItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LeaveYearEndProcessingService
{
    public function __construct(protected LeavePolicyResolver $policyResolver) {}

    public function preview(User $actor, array $filters): array
    {
      
        $processingYear = (int) $filters['processing_year'];
        $targetYear = (int) $filters['target_year'];
        $this->validateYears($processingYear, $targetYear);

        $rows = [];
        $types = LeaveType::query()->where('status', 'active')->when($filters['leave_type_id'] ?? null, fn ($q, $id) => $q->whereKey($id))->orderBy('id')->get();
        $employees = $this->employeesQuery($actor, $filters)->orderBy('id')->lazyById(250);

        foreach ($employees as $employee) {
            foreach ($types as $leaveType) {
                $row = $this->calculate($employee, $leaveType, $processingYear, (bool) ($filters['auto_encashment'] ?? false));
                if ($row['previous_balance'] != 0 || $row['eligible_carry_forward'] != 0 || $row['expired_days'] != 0 || $row['encashment_days'] != 0 || $row['leave_policy_id']) {
                    $rows[] = $row;
                }
            }
        }
// dd($employees, $types, $rows);
        return [
            'processing_year' => $processingYear,
            'target_year' => $targetYear,
            'rows' => $rows,
            'summary' => [
                'employees' => collect($rows)->pluck('user_id')->unique()->count(),
                'leave_types' => collect($rows)->pluck('leave_type_id')->unique()->count(),
                'closing_balance' => round(collect($rows)->sum('previous_balance'), 2),
                'carry_forward' => round(collect($rows)->sum('eligible_carry_forward'), 2),
                'expired' => round(collect($rows)->sum('expired_days'), 2),
                'encashment' => round(collect($rows)->sum('encashment_days'), 2),
            ],
        ];
    }

    public function createProcess(User $actor, array $filters): LeaveYearEndProcess
    {
        $preview = $this->preview($actor, $filters);
        return DB::transaction(function () use ($actor, $filters, $preview) {
            $batchKey = implode(':', [$preview['processing_year'], $preview['target_year'], $filters['company_id'] ?? 'all', $filters['branch_id'] ?? 'all']);
            $existing = LeaveYearEndProcess::query()
                ->where('batch_key', $batchKey)
                ->whereIn('status', ['processing', 'completed'])
                ->lockForUpdate()->first();
            if ($existing) {
                throw new RuntimeException('This year-end period has already been processed or is currently processing.');
            }

            $process = LeaveYearEndProcess::create([
                'processing_year' => $preview['processing_year'], 'target_year' => $preview['target_year'], 'batch_key' => $batchKey,
                'company_id' => $filters['company_id'] ?? null, 'branch_id' => $filters['branch_id'] ?? null,
                'status' => 'processing', 'started_at' => now(), 'processed_by' => $actor->id,
                'total_employees' => $preview['summary']['employees'], 'total_leave_types' => $preview['summary']['leave_types'],
                'total_closing_balance' => $preview['summary']['closing_balance'],
                'total_carry_forward_days' => $preview['summary']['carry_forward'],
                'total_expired_days' => $preview['summary']['expired'], 'total_encashment_days' => $preview['summary']['encashment'],
            ]);

            try {
                foreach ($preview['rows'] as $row) {
                    $item = $process->items()->create([
                        'user_id' => $row['user_id'], 'leave_type_id' => $row['leave_type_id'], 'leave_policy_id' => $row['leave_policy_id'],
                        'previous_balance' => $row['previous_balance'], 'eligible_carry_forward' => $row['eligible_carry_forward'],
                        'expired_days' => $row['expired_days'], 'encashment_days' => $row['encashment_days'],
                        'closing_balance' => $row['closing_balance'], 'status' => 'processing', 'remarks' => $row['remarks'],
                    ]);
                    $this->writeTransactions($process, $item, $actor, $preview['processing_year'], $preview['target_year']);
                    $item->update(['status' => 'completed']);
                }
                $process->update(['status' => 'completed', 'completed_at' => now()]);
            } catch (\Throwable $e) {
                $process->update(['status' => 'failed', 'completed_at' => now(), 'remarks' => $e->getMessage()]);
                throw $e;
            }
            return $process->fresh(['items.employee', 'items.leaveType']);
        });
    }

    protected function calculate(User $employee, LeaveType $leaveType, int $year, bool $autoEncashment): array
    {
        $date = Carbon::create($year, 12, 31);
        $assignment = $this->policyResolver->resolve($employee, $date);
        $detail = $assignment?->policy ? $this->policyResolver->getPolicyDetail($assignment->policy, $leaveType->id) : null;
        $balance = (float) (LeaveBalanceLedger::query()->where('user_id', $employee->id)->where('leave_type_id', $leaveType->id)->where('transaction_date', '<=', $date->toDateString())->latest('transaction_date')->latest('id')->value('balance_after') ?? 0);
        $positive = max($balance, 0);
        $carry = $detail?->carry_forward_allowed ? $positive : 0;
        if ($detail?->maximum_carry_forward !== null) $carry = min($carry, (float) $detail->maximum_carry_forward);
        $remaining = max($positive - $carry, 0);
        $encashment = ($autoEncashment && $detail?->encashment_allowed) ? $remaining : 0;
        if ($detail?->maximum_encashment !== null) $encashment = min($encashment, (float) $detail->maximum_encashment);
        $expired = max($remaining - $encashment, 0);

        return [
            'user_id' => $employee->id, 'employee_name' => $employee->name, 'employee_id' => $employee->employee_id,
            'leave_type_id' => $leaveType->id, 'leave_type' => $leaveType->leave_name, 'leave_code' => $leaveType->leave_code,
            'leave_policy_id' => $assignment?->leave_policy_id, 'policy' => $assignment?->policy?->policy_name,
            'previous_balance' => round($balance, 2), 'eligible_carry_forward' => round($carry, 2),
            'expired_days' => round($expired, 2), 'encashment_days' => round($encashment, 2),
            'closing_balance' => round($balance < 0 ? $balance : $carry, 2),
            'remarks' => $detail ? null : 'No applicable leave policy detail found; no year-end entitlement was created.',
        ];
    }

    protected function writeTransactions(LeaveYearEndProcess $process, LeaveYearEndProcessItem $item, User $actor, int $year, int $targetYear): void
    {
        $reference = sprintf('YE-%d-%d-%06d', $year, $targetYear, $process->id);
        $balance = (float) $item->previous_balance;
        $date = Carbon::create($year, 12, 31)->toDateString();

        foreach ([['expiry', (float) $item->expired_days], ['encashment', (float) $item->encashment_days], ['carry_forward', (float) $item->eligible_carry_forward]] as [$type, $days]) {
            if ($days <= 0) continue;
            $transactionReference = $reference . '-' . $item->user_id . '-' . $item->leave_type_id . '-' . $type;
            if (LeaveBalanceLedger::where('transaction_reference', $transactionReference)->exists()) continue;
            $balance += $type === 'carry_forward' ? $days : -$days;
            LeaveBalanceLedger::create([
                'user_id' => $item->user_id, 'leave_type_id' => $item->leave_type_id, 'transaction_type' => $type,
                'reference_type' => LeaveYearEndProcess::class, 'reference_id' => $process->id,
                'transaction_reference' => $transactionReference, 'transaction_date' => $date,
                'effective_date' => Carbon::create($targetYear, 1, 1)->toDateString(), 'days' => $type === 'carry_forward' ? $days : -$days,
                'credit_days' => $type === 'carry_forward' ? $days : 0, 'debit_days' => $type === 'carry_forward' ? 0 : $days,
                'balance_after' => round($balance, 2), 'remarks' => "Year-end {$year} to {$targetYear} ({$type})",
                'transaction_source' => 'manual', 'leave_policy_id' => $item->leave_policy_id,
                'performed_by' => $actor->id, 'processed_by' => $actor->id, 'created_by' => $actor->id,
            ]);
        }
    }

    protected function employeesQuery(User $actor, array $filters): Builder
    {
        return User::query()->where('status', User::STATUS_ACTIVE)
            ->when(!$actor->hasRole(User::ROLE_ADMIN), function ($q) use ($actor) {
                $actor->company_id ? $q->where('company_id', $actor->company_id) : $q->whereNull('company_id');
                if ($actor->branch_id) $q->where('branch_id', $actor->branch_id);
            })
            ->when($filters['company_id'] ?? null, fn ($q, $v) => $q->where('company_id', $v))
            ->when($filters['branch_id'] ?? null, fn ($q, $v) => $q->where('branch_id', $v))
            ->when($filters['division_id'] ?? null, fn ($q, $v) => $q->where('division_id', $v))
            ->when($filters['department_id'] ?? null, fn ($q, $v) => $q->where('department_id', $v))
            ->when($filters['section_id'] ?? null, fn ($q, $v) => $q->where('section_id', $v))
            ->when($filters['unit_id'] ?? null, fn ($q, $v) => $q->where('unit_id', $v))
            ->when($filters['employee'] ?? null, fn ($q, $v) => $q->where(fn ($s) => $s->where('name', 'like', "%{$v}%")->orWhere('employee_id', 'like', "%{$v}%")));
    }

    protected function validateYears(int $processingYear, int $targetYear): void
    {
        if ($targetYear !== $processingYear + 1) throw new RuntimeException('Target year must immediately follow the processing year.');
        if ($processingYear < 1970 || $processingYear > 2100) throw new RuntimeException('Processing year is outside the supported range.');
    }
}
