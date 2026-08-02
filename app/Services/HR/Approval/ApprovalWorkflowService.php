<?php

namespace App\Services\HR\Approval;

use App\Models\ApprovalWorkflow;
use App\Models\ApprovalWorkflowLevel;
use Illuminate\Support\Facades\Auth;

class ApprovalWorkflowService
{
    public function create(array $data, ?int $userId = null): ApprovalWorkflow
    {
        return ApprovalWorkflow::create([
            'workflow_name' => $data['workflow_name'],
            'workflow_code' => $data['workflow_code'],
            'module_name' => $data['module_name'],
            'company_id' => $data['company_id'] ?? null,
            'branch_id' => $data['branch_id'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'created_by' => $userId ?? Auth::id(),
            'updated_by' => $userId ?? Auth::id(),
        ]);
    }

    public function update(ApprovalWorkflow $workflow, array $data, ?int $userId = null): ApprovalWorkflow
    {
        $workflow->update([
            'workflow_name' => $data['workflow_name'] ?? $workflow->workflow_name,
            'workflow_code' => $data['workflow_code'] ?? $workflow->workflow_code,
            'module_name' => $data['module_name'] ?? $workflow->module_name,
            'company_id' => $data['company_id'] ?? $workflow->company_id,
            'branch_id' => $data['branch_id'] ?? $workflow->branch_id,
            'description' => $data['description'] ?? $workflow->description,
            'status' => $data['status'] ?? $workflow->status,
            'updated_by' => $userId ?? Auth::id(),
        ]);

        return $workflow->fresh();
    }

    public function delete(ApprovalWorkflow $workflow): void
    {
        $workflow->delete();
    }

    public function getActiveWorkflows(string $moduleName)
    {
        return ApprovalWorkflow::where('module_name', $moduleName)
            ->where('status', 'active')
            ->orderBy('id')
            ->get();
    }
}
