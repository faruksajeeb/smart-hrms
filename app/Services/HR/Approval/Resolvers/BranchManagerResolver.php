<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class BranchManagerResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        $branchId = $requestedBy->branch_id;

        if (!$branchId) {
            $currentHistory = $requestedBy->currentEmploymentHistory;
            if ($currentHistory) {
                $branchId = $currentHistory->branch_id;
            }
        }

        if (!$branchId) {
            return null;
        }

        return User::where('branch_id', $branchId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Branch Manager');
            })
            ->first();
    }
}
