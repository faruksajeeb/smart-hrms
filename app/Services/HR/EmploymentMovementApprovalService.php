<?php

namespace App\Services\HR;

use App\Enums\ApprovalStatus;
use App\Enums\ApprovalWorkflowStatus;
use App\Enums\EmploymentHistoryStatus;
use App\Enums\EmploymentMovementType;
use App\Models\ApprovalWorkflow;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmploymentMovementApprovalService
{
    public function __construct(
        protected EmployeeEmploymentMovementService $movementService,
        protected \App\Services\HR\Approval\ApprovalEngineService $approvalEngine,
    ) {}

    public function createMovement(
        User $employee,
        EmploymentMovementType $type,
        array $data,
        ?int $userId = null
    ): \App\Models\EmployeeEmploymentHistory {
        $workflow = ApprovalWorkflow::where('module_name', 'employment_movement')
            ->where('status', ApprovalWorkflowStatus::Active)
            ->orderBy('id')
            ->first();

        if (!$workflow) {
            return $this->movementService->createMovement($employee, $type, $data, $userId);
        }

        return DB::transaction(function () use ($employee, $type, $data, $userId, $workflow) {
            $history = $this->movementService->createMovement($employee, $type, $data, $userId);
            $history->update(['status' => EmploymentHistoryStatus::Pending]);

            $this->approvalEngine->submit(
                $history->creator ?? User::find($userId),
                'employment_movement',
                \App\Models\EmployeeEmploymentHistory::class,
                $history->id,
                $workflow->id
            );

            return $history;
        });
    }

    public function createInitialAppointment(
        User $employee,
        array $data,
        ?int $userId = null
    ): \App\Models\EmployeeEmploymentHistory {
        $workflow = ApprovalWorkflow::where('module_name', 'employment_movement')
            ->where('status', ApprovalWorkflowStatus::Active)
            ->orderBy('id')
            ->first();

        if (!$workflow) {
            return $this->movementService->createInitialAppointment($employee, $data, $userId);
        }

        return DB::transaction(function () use ($employee, $data, $userId, $workflow) {
            $history = $this->movementService->createInitialAppointment($employee, $data, $userId);
            $history->update(['status' => EmploymentHistoryStatus::Pending]);

            $this->approvalEngine->submit(
                $history->creator ?? User::find($userId),
                'employment_movement',
                \App\Models\EmployeeEmploymentHistory::class,
                $history->id,
                $workflow->id
            );

            return $history;
        });
    }

    public function historyQuery(User $employee)
    {
        return $this->movementService->historyQuery($employee);
    }
}
