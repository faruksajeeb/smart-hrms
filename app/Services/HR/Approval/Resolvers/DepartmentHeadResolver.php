<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\MasterDataItem;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DepartmentHeadResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        $departmentId = $requestedBy->department_id;

        if (!$departmentId) {
            $currentHistory = $requestedBy->currentEmploymentHistory;
            if ($currentHistory) {
                $departmentId = $currentHistory->department_id;
            }
        }

        if (!$departmentId) {
            return null;
        }

        return User::where('department_id', $departmentId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Department Head');
            })
            ->first();
    }
}
