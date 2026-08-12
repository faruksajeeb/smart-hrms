<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CompanyAdminResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        $companyId = $requestedBy->company_id;

        if (!$companyId) {
            $currentHistory = $requestedBy->currentEmploymentHistory;
            if ($currentHistory) {
                $companyId = $currentHistory->company_id;
            }
        }

        if (!$companyId) {
            return null;
        }

        return User::where('company_id', $companyId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Company Admin');
            })
            ->first();
    }
}
