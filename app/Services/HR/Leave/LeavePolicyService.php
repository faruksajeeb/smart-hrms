<?php

namespace App\Services\HR\Leave;

use App\Enums\LeavePolicyStatus;
use App\Models\LeavePolicy;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeavePolicyService
{
    public function create(array $data, ?int $userId = null): LeavePolicy
    {
        return LeavePolicy::create([
            'policy_name' => $data['policy_name'],
            'policy_code' => $data['policy_code'],
            'description' => $data['description'] ?? null,
            'effective_from' => $data['effective_from'],
            'effective_to' => $data['effective_to'] ?? null,
            'status' => $data['status'] ?? LeavePolicyStatus::Active,
            'created_by' => $userId ?? Auth::id(),
            'updated_by' => $userId ?? Auth::id(),
        ]);
    }

    public function update(LeavePolicy $leavePolicy, array $data, ?int $userId = null): LeavePolicy
    {
        $leavePolicy->update([
            'policy_name' => $data['policy_name'] ?? $leavePolicy->policy_name,
            'policy_code' => $data['policy_code'] ?? $leavePolicy->policy_code,
            'description' => $data['description'] ?? $leavePolicy->description,
            'effective_from' => $data['effective_from'] ?? $leavePolicy->effective_from,
            'effective_to' => $data['effective_to'] ?? $leavePolicy->effective_to,
            'status' => $data['status'] ?? $leavePolicy->status,
            'updated_by' => $userId ?? Auth::id(),
        ]);

        return $leavePolicy->fresh();
    }

    public function delete(LeavePolicy $leavePolicy): void
    {
        $leavePolicy->delete();
    }

    public function getActivePolicies()
    {
        return LeavePolicy::where('status', LeavePolicyStatus::Active)
            ->orderBy('policy_name')
            ->get();
    }
}
