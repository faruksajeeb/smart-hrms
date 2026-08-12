<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportingManagerResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        $manager = $requestedBy->reportingManager;

        if ($manager) {
            return $manager;
        }

        $currentHistory = $requestedBy->currentEmploymentHistory;
        if ($currentHistory && $currentHistory->reporting_manager_id) {
            return $currentHistory->reportingManager;
        }

        return null;
    }
}
