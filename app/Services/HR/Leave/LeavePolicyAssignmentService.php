<?php

namespace App\Services\HR\Leave;

use App\Enums\LeavePolicyAssignmentStatus;
use App\Models\LeavePolicy;
use App\Models\LeavePolicyAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeavePolicyAssignmentService
{
    public function assign(array $data, ?int $userId = null): LeavePolicyAssignment
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $userId) {
            $assignment = LeavePolicyAssignment::create([
                'leave_policy_id' => $data['leave_policy_id'],
                'company_id' => $data['company_id'] ?? null,
                'branch_id' => $data['branch_id'] ?? null,
                'division_id' => $data['division_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'section_id' => $data['section_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'designation_id' => $data['designation_id'] ?? null,
                'employment_type' => $data['employment_type'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'effective_from' => $data['effective_from'],
                'effective_to' => $data['effective_to'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => $data['status'] ?? LeavePolicyAssignmentStatus::Active,
                'created_by' => $userId ?? Auth::id(),
                'updated_by' => $userId ?? Auth::id(),
            ]);

            return $assignment;
        });
    }

    public function update(LeavePolicyAssignment $assignment, array $data, ?int $userId = null): LeavePolicyAssignment
    {
        $assignment->update([
            'leave_policy_id' => $data['leave_policy_id'] ?? $assignment->leave_policy_id,
            'company_id' => $data['company_id'] ?? $assignment->company_id,
            'branch_id' => $data['branch_id'] ?? $assignment->branch_id,
            'division_id' => $data['division_id'] ?? $assignment->division_id,
            'department_id' => $data['department_id'] ?? $assignment->department_id,
            'section_id' => $data['section_id'] ?? $assignment->section_id,
            'unit_id' => $data['unit_id'] ?? $assignment->unit_id,
            'designation_id' => $data['designation_id'] ?? $assignment->designation_id,
            'employment_type' => $data['employment_type'] ?? $assignment->employment_type,
            'user_id' => $data['user_id'] ?? $assignment->user_id,
            'effective_from' => $data['effective_from'] ?? $assignment->effective_from,
            'effective_to' => $data['effective_to'] ?? $assignment->effective_to,
            'remarks' => $data['remarks'] ?? $assignment->remarks,
            'status' => $data['status'] ?? $assignment->status,
            'updated_by' => $userId ?? Auth::id(),
        ]);

        return $assignment->fresh();
    }

    public function delete(LeavePolicyAssignment $assignment): void
    {
        $assignment->delete();
    }

    public function getActiveAssignments()
    {
        return LeavePolicyAssignment::where('status', LeavePolicyAssignmentStatus::Active)
            ->with(['policy', 'company', 'branch', 'employee'])
            ->orderBy('effective_from', 'desc')
            ->get();
    }
}
