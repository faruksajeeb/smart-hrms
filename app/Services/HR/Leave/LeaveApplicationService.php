<?php

namespace App\Services\HR\Leave;

use App\Enums\ApprovalStatus;
use App\Enums\ApprovalWorkflowStatus;
use App\Enums\LeaveApplicationStatus;
use App\Enums\LeaveApplicationType;
use App\Enums\LeaveTransactionType;
use App\Models\ApprovalWorkflow;
use App\Models\LeaveApplication;
use App\Models\LeaveApplicationDay;
use App\Models\LeaveAttachment;
use App\Models\LeaveBalanceLedger;
use App\Models\LeavePolicyAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\HR\Approval\ApprovalEngineService;

class LeaveApplicationService
{
    public function __construct(
        protected LeaveCalculationService $calculationService,
        protected LeaveValidationService $validationService,
        protected LeaveBalanceService $balanceService,
        protected ApprovalEngineService $approvalEngine,
    ) {}

    public function resolvePolicy(User $employee, \DateTimeInterface $date): ?LeavePolicyAssignment
    {
        return $this->validationService->resolvePolicy($employee, $date);
    }

    public function getAllBalances(User $user): array
    {
        return $this->balanceService->getAllBalances($user);
    }

    public function createDraft(User $employee, array $data, ?int $userId = null): LeaveApplication
    {
        return DB::transaction(function () use ($employee, $data, $userId) {
            $leaveType = \App\Models\LeaveType::findOrFail($data['leave_type_id']);

            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);

            $assignment = $this->validationService->resolvePolicy($employee, $startDate);
            if (!$assignment) {
                throw new \RuntimeException("You are not assigned to any Leave Policy. Please contact HR.");
            }

            $policy = \App\Models\LeavePolicy::findOrFail($assignment->leave_policy_id);

            $policyDetail = $policy->details()
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'active')
                ->first();

            if (!$policyDetail) {
                throw new \RuntimeException("Leave type {$leaveType->leave_name} is not covered by your leave policy.");
            }

            $days = $this->calculationService->calculateDays(
                $employee,
                $policy,
                $policyDetail,
                $startDate,
                $endDate,
                $data['is_half_day'] ?? false,
                $data['half_day_session'] ?? null,
                $data['is_emergency'] ?? false
            );

            $totalDays = array_sum(array_column($days, 'leave_days'));

            $application = LeaveApplication::create([
                'application_no' => $this->generateApplicationNo(),
                'user_id' => $employee->id,
                'leave_policy_id' => $policy->id,
                'leave_type_id' => $leaveType->id,
                'application_type' => $data['is_emergency'] ? LeaveApplicationType::Emergency->value : LeaveApplicationType::Normal->value,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
                'requested_days' => $totalDays,
                'is_half_day' => $data['is_half_day'] ?? false,
                'half_day_session' => $data['half_day_session'] ?? null,
                'is_emergency' => $data['is_emergency'] ?? false,
                'reason' => $data['reason'] ?? null,
                'delegate_user_id' => $data['delegate_user_id'] ?? null,
                'status' => LeaveApplicationStatus::Draft,
                'created_by' => $userId ?? Auth::id(),
                'updated_by' => $userId ?? Auth::id(),
            ]);

            foreach ($days as $day) {
                $application->days()->create($day + ['created_by' => $userId ?? Auth::id(), 'updated_by' => $userId ?? Auth::id()]);
            }

            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    $application->attachments()->create([
                        'file_name' => $attachment['file_name'],
                        'file_path' => $attachment['file_path'],
                        'file_size' => $attachment['file_size'] ?? null,
                        'mime_type' => $attachment['mime_type'] ?? null,
                        'uploaded_by' => $userId ?? Auth::id(),
                    ]);
                }
            }

            return $application->fresh(['days', 'attachments']);
        });
    }

    public function updateDraft(LeaveApplication $application, array $data, ?int $userId = null): LeaveApplication
    {
        if (!$application->canEdit()) {
            throw new \RuntimeException('Only draft applications can be edited.');
        }

        return DB::transaction(function () use ($application, $data, $userId) {
            $leaveType = \App\Models\LeaveType::findOrFail($data['leave_type_id']);

            $startDate = \Carbon\Carbon::parse($data['start_date']);
            $endDate = \Carbon\Carbon::parse($data['end_date']);

            $assignment = $this->validationService->resolvePolicy($application->employee, $startDate);
            if (!$assignment) {
                throw new \RuntimeException("You are not assigned to any Leave Policy. Please contact HR.");
            }

            $policy = \App\Models\LeavePolicy::findOrFail($assignment->leave_policy_id);

            $policyDetail = $policy->details()
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'active')
                ->first();

            if (!$policyDetail) {
                throw new \RuntimeException("Leave type {$leaveType->leave_name} is not covered by your leave policy.");
            }

            $days = $this->calculationService->calculateDays(
                $application->employee,
                $policy,
                $policyDetail,
                $startDate,
                $endDate,
                $data['is_half_day'] ?? false,
                $data['half_day_session'] ?? null,
                $data['is_emergency'] ?? false
            );

            $totalDays = array_sum(array_column($days, 'leave_days'));

            $application->update([
                'leave_policy_id' => $policy->id,
                'leave_type_id' => $leaveType->id,
                'application_type' => $data['is_emergency'] ? LeaveApplicationType::Emergency->value : LeaveApplicationType::Normal->value,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'total_days' => $totalDays,
                'requested_days' => $totalDays,
                'is_half_day' => $data['is_half_day'] ?? false,
                'half_day_session' => $data['half_day_session'] ?? null,
                'is_emergency' => $data['is_emergency'] ?? false,
                'reason' => $data['reason'] ?? null,
                'delegate_user_id' => $data['delegate_user_id'] ?? null,
                'updated_by' => $userId ?? Auth::id(),
            ]);

            $application->days()->delete();
            foreach ($days as $day) {
                $application->days()->create($day + ['created_by' => $userId ?? Auth::id(), 'updated_by' => $userId ?? Auth::id()]);
            }

            if (isset($data['attachments'])) {
                $application->attachments()->delete();
                foreach ($data['attachments'] as $attachment) {
                    $application->attachments()->create([
                        'file_name' => $attachment['file_name'],
                        'file_path' => $attachment['file_path'],
                        'file_size' => $attachment['file_size'] ?? null,
                        'mime_type' => $attachment['mime_type'] ?? null,
                        'uploaded_by' => $userId ?? Auth::id(),
                    ]);
                }
            }

            return $application->fresh(['days', 'attachments']);
        });
    }

    public function submit(LeaveApplication $application, ?int $userId = null): LeaveApplication
    {
        if (!$application->canSubmit()) {
            throw new \RuntimeException('Only draft applications can be submitted.');
        }

        $assignment = $this->validationService->resolvePolicy($application->employee, \Carbon\Carbon::parse($application->getRawOriginal('start_date')));
        if (!$assignment) {
            throw new \RuntimeException('Leave policy not found. Please contact HR to assign a leave policy.');
        }

        $startDate = \Carbon\Carbon::parse($application->getRawOriginal('start_date'));
        $endDate = \Carbon\Carbon::parse($application->getRawOriginal('end_date'));

        $errors = $this->validationService->validate(
            $application->employee,
            $application->leaveType,
            $application->leavePolicy,
            $startDate,
            $endDate,
            $application
        );

        if (!empty($errors)) {
            throw new \RuntimeException(implode(', ', $errors));
        }

        $workflow = ApprovalWorkflow::where('module_name', 'leave_request')
            ->where('status', ApprovalWorkflowStatus::Active)
            ->orderBy('id')
            ->first();

        return DB::transaction(function () use ($application, $userId, $workflow) {
            $application->update([
                'status' => $workflow ? LeaveApplicationStatus::Pending : LeaveApplicationStatus::Approved,
                'submitted_at' => now(),
                'updated_by' => $userId ?? Auth::id(),
            ]);

            if ($workflow) {
                $this->approvalEngine->submit(
                    $application->employee,
                    'leave_request',
                    LeaveApplication::class,
                    $application->id,
                    $workflow->id
                );
            } else {
                $this->finalizeApproval($application, $userId ?? Auth::id());
            }

            if ($application->delegate_user_id) {
                $detail = $application->leavePolicy->details()
                    ->where('leave_type_id', $application->leave_type_id)
                    ->where('status', 'active')
                    ->first();

                if ($detail && $detail->delegate_acknowledgement_required) {
                    $application->update([
                        'delegate_status' => \App\Enums\DelegateStatus::Pending,
                        'updated_by' => $userId ?? Auth::id(),
                    ]);

                    $delegate = User::find($application->delegate_user_id);
                    if ($delegate) {
                        $delegate->notify(new \App\Notifications\DelegateAssignedNotification($application->fresh()));
                    }
                } else {
                    $application->update([
                        'delegate_status' => \App\Enums\DelegateStatus::Accepted,
                        'delegate_responded_at' => now(),
                        'updated_by' => $userId ?? Auth::id(),
                    ]);

                    $delegate = User::find($application->delegate_user_id);
                    if ($delegate) {
                        $delegate->notify(new \App\Notifications\DelegateAssignedNotification($application->fresh()));
                    }
                }
            }

            return $application->fresh();
        });
    }

    public function acceptDelegate(LeaveApplication $application, ?string $remarks = null, ?int $userId = null): LeaveApplication
    {
        if (!$application->delegate_user_id) {
            throw new \RuntimeException('No delegate assigned to this leave application.');
        }

        if ($application->delegate_status !== \App\Enums\DelegateStatus::Pending) {
            throw new \RuntimeException('Delegate acknowledgement is not pending.');
        }

        $application->update([
            'delegate_status' => \App\Enums\DelegateStatus::Accepted,
            'delegate_remarks' => $remarks,
            'delegate_responded_at' => now(),
            'updated_by' => $userId ?? Auth::id(),
        ]);

        $application->employee->notify(new \App\Notifications\DelegateAcceptedNotification($application->fresh()));

        return $application->fresh();
    }

    public function declineDelegate(LeaveApplication $application, ?string $remarks = null, ?int $userId = null): LeaveApplication
    {
        if (!$application->delegate_user_id) {
            throw new \RuntimeException('No delegate assigned to this leave application.');
        }

        if ($application->delegate_status !== \App\Enums\DelegateStatus::Pending) {
            throw new \RuntimeException('Delegate acknowledgement is not pending.');
        }

        $application->update([
            'delegate_status' => \App\Enums\DelegateStatus::Declined,
            'delegate_remarks' => $remarks,
            'delegate_responded_at' => now(),
            'updated_by' => $userId ?? Auth::id(),
        ]);

        $application->employee->notify(new \App\Notifications\DelegateDeclinedNotification($application->fresh()));

        return $application->fresh();
    }

    public function cancel(LeaveApplication $application, ?string $remarks = null, ?int $userId = null): LeaveApplication
    {
        if (!$application->canCancel()) {
            throw new \RuntimeException('This leave application cannot be cancelled.');
        }

        return DB::transaction(function () use ($application, $remarks, $userId) {
            $application->update([
                'status' => LeaveApplicationStatus::Cancelled,
                'cancelled_at' => now(),
                'remarks' => $remarks,
                'updated_by' => $userId ?? Auth::id(),
            ]);

            if ($application->status === LeaveApplicationStatus::Approved) {
                $this->reverseLedgerEntries($application, $userId ?? Auth::id());
            }

            return $application->fresh();
        });
    }

    public function withdraw(LeaveApplication $application, ?string $remarks = null, ?int $userId = null): LeaveApplication
    {
        if (!$application->canWithdraw()) {
            throw new \RuntimeException('Only pending applications can be withdrawn.');
        }

        return DB::transaction(function () use ($application, $remarks, $userId) {
            $application->update([
                'status' => LeaveApplicationStatus::Withdrawn,
                'withdrawn_at' => now(),
                'remarks' => $remarks,
                'updated_by' => $userId ?? Auth::id(),
            ]);

            return $application->fresh();
        });
    }

    public function finalizeApproval(LeaveApplication $application, int $userId): void
    {
        $application->update([
            'status' => LeaveApplicationStatus::Approved,
            'approved_at' => now(),
            'updated_by' => $userId,
        ]);

        $this->createLedgerEntries($application, $userId);
    }

    private function createLedgerEntries(LeaveApplication $application, int $userId): void
    {
        $balanceAfter = $this->balanceService->getBalance($application->employee, $application->leaveType, \Carbon\Carbon::parse($application->getRawOriginal('start_date'))->format('Y-m-d'));
        $debitDays = $application->requested_days;

        $this->balanceService->createLedgerEntry([
            'user_id' => $application->user_id,
            'leave_type_id' => $application->leave_type_id,
            'transaction_type' => LeaveTransactionType::LeaveApproved,
            'reference_type' => LeaveApplication::class,
            'reference_id' => $application->id,
            'transaction_reference' => $application->application_no,
            'transaction_date' => \Carbon\Carbon::parse($application->getRawOriginal('start_date'))->format('Y-m-d'),
            'effective_date' => \Carbon\Carbon::parse($application->getRawOriginal('start_date'))->format('Y-m-d'),
            'days' => -$debitDays,
            'credit_days' => 0,
            'debit_days' => $debitDays,
            'balance_after' => $balanceAfter - $debitDays,
            'remarks' => $application->reason,
            'transaction_source' => 'system',
            'performed_by' => $userId,
            'approved_by' => $userId,
            'created_by' => $userId,
        ]);
    }

    private function reverseLedgerEntries(LeaveApplication $application, int $userId): void
    {
        $balanceAfter = $this->balanceService->getBalance($application->employee, $application->leaveType, \Carbon\Carbon::parse($application->getRawOriginal('start_date'))->format('Y-m-d'));
        $creditDays = $application->requested_days;

        $this->balanceService->createLedgerEntry([
            'user_id' => $application->user_id,
            'leave_type_id' => $application->leave_type_id,
            'transaction_type' => LeaveTransactionType::LeaveCancelled,
            'reference_type' => LeaveApplication::class,
            'reference_id' => $application->id,
            'transaction_reference' => $application->application_no,
            'transaction_date' => now()->format('Y-m-d'),
            'effective_date' => now()->format('Y-m-d'),
            'days' => $creditDays,
            'credit_days' => $creditDays,
            'debit_days' => 0,
            'balance_after' => $balanceAfter + $creditDays,
            'remarks' => 'Cancelled: ' . ($application->reason ?? ''),
            'transaction_source' => 'manual',
            'performed_by' => $userId,
            'created_by' => $userId,
        ]);
    }

    private function generateApplicationNo(): string
    {
        $prefix = 'LA';
        $year = date('Y');
        $month = date('m');

        $lastApplication = LeaveApplication::where('application_no', 'like', "{$prefix}{$year}{$month}%")
            ->orderByDesc('id')
            ->first();

        if ($lastApplication) {
            $lastNumber = (int) substr($lastApplication->application_no, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $year . $month . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
