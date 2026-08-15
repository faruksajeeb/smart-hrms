<?php

namespace App\Services\HR\Approval;

use App\Enums\ApprovalStatus;
use App\Enums\ApprovalType;
use App\Enums\ApprovalWorkflowLevelStatus;
use App\Enums\ApprovalWorkflowStatus;
use App\Models\ApprovalRequest;
use App\Models\ApprovalRequestStep;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowLevel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ApprovalEngineService
{
    public function __construct(
        protected ApproverResolverService $approverResolver,
    ) {}

    public function submit(
        User $requestedBy,
        string $moduleName,
        string $referenceType,
        int $referenceId,
        ?int $workflowId = null
    ): ApprovalRequest {
        $workflow = $this->resolveWorkflow($moduleName, $workflowId);

        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $requestedBy,
            $moduleName,
            $referenceType,
            $referenceId,
            $workflow
        ) {
            $approvalRequest = ApprovalRequest::create([
                'workflow_id' => $workflow->id,
                'module_name' => $moduleName,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'requested_by' => $requestedBy->id,
                'current_level' => 1,
                'current_status' => ApprovalStatus::Pending,
                'submitted_at' => now(),
            ]);

            $this->generateApprovalSteps($approvalRequest, $workflow, $requestedBy);

            return $approvalRequest;
        });
    }

    public function approve(
        ApprovalRequest $approvalRequest,
        User $approver,
        ?string $remarks = null
    ): ApprovalRequest {
        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $approvalRequest,
            $approver,
            $remarks
        ) {
            $approvalRequest = ApprovalRequest::whereKey($approvalRequest->id)->lockForUpdate()->with(['workflow','steps','requester'])->firstOrFail();
            $currentStep = $approvalRequest->currentStep;

            if (!$currentStep || $currentStep->approver_id !== $approver->id) {
                throw new \RuntimeException('You are not authorized to approve this request.');
            }

            $currentStep->update([
                'status' => ApprovalStatus::Approved,
                'approved_at' => now(),
                'remarks' => $remarks,
            ]);

            $workflow = $approvalRequest->workflow;
            $nextLevel = $approvalRequest->current_level + 1;
            $nextLevelExists = $workflow->levels()->where('level_no', $nextLevel)->exists();

            if (!$nextLevelExists) {
                $approvalRequest->update([
                    'current_level' => $approvalRequest->current_level,
                    'current_status' => ApprovalStatus::Approved,
                    'completed_at' => now(),
                ]);

                $this->completeRequest($approvalRequest);
            } else {
                $approvalRequest->update([
                    'current_level' => $nextLevel,
                ]);

                $this->createStepForLevel($approvalRequest, $nextLevel, $approvalRequest->requester);
            }

            return $approvalRequest->fresh();
        });
    }

    public function reject(
        ApprovalRequest $approvalRequest,
        User $approver,
        ?string $remarks = null
    ): ApprovalRequest {
        return \Illuminate\Support\Facades\DB::transaction(function () use (
            $approvalRequest,
            $approver,
            $remarks
        ) {
            $approvalRequest = ApprovalRequest::whereKey($approvalRequest->id)->lockForUpdate()->with(['workflow','steps','requester'])->firstOrFail();
            $currentStep = $approvalRequest->currentStep;

            if (!$currentStep || $currentStep->approver_id !== $approver->id) {
                throw new \RuntimeException('You are not authorized to reject this request.');
            }

            $currentStep->update([
                'status' => ApprovalStatus::Rejected,
                'approved_at' => now(),
                'remarks' => $remarks,
            ]);

            $approvalRequest->update([
                'current_status' => ApprovalStatus::Rejected,
                'completed_at' => now(),
            ]);

            return $approvalRequest->fresh();
        });
    }

    protected function resolveWorkflow(string $moduleName, ?int $workflowId): ApprovalWorkflow
    {
        if ($workflowId) {
            return ApprovalWorkflow::findOrFail($workflowId);
        }

        $workflow = ApprovalWorkflow::where('module_name', $moduleName)
            ->where('status', ApprovalWorkflowStatus::Active)
            ->orderBy('id')
            ->first();

        if (!$workflow) {
            throw new \RuntimeException("No active approval workflow found for module: {$moduleName}");
        }

        return $workflow;
    }

    protected function generateApprovalSteps(
        ApprovalRequest $approvalRequest,
        ApprovalWorkflow $workflow,
        User $requestedBy
    ): void {
        $levels = $workflow->levels()
            ->where('status', ApprovalWorkflowLevelStatus::Active)
            ->orderBy('level_no')
            ->get();

        $level = $levels->first();
        if ($level) $this->createStepForLevel($approvalRequest, $level->level_no, $requestedBy, $level);
    }

    protected function createStepForLevel(
        ApprovalRequest $approvalRequest,
        int $levelNo,
        User $requestedBy,
        ?ApprovalWorkflowLevel $workflowLevel = null
    ): void {
        $workflowLevel = $workflowLevel ?? $approvalRequest->workflow->levels()
            ->where('level_no', $levelNo)
            ->first();

        if (!$workflowLevel) {
            return;
        }

        $approver = $this->approverResolver->resolve(
            ApprovalType::from($workflowLevel->approval_type),
            $requestedBy,
            [
                'role_id' => $workflowLevel->role_id,
                'specific_user_id' => $workflowLevel->specific_user_id,
            ]
        );

        ApprovalRequestStep::create([
            'approval_request_id' => $approvalRequest->id,
            'workflow_level_id' => $workflowLevel->id,
            'level_no' => $levelNo,
            'approver_id' => $approver?->id,
            'status' => ApprovalStatus::Pending,
        ]);
    }

    protected function completeRequest(ApprovalRequest $approvalRequest): void
    {
        // Override this in child classes if needed
    }
}
