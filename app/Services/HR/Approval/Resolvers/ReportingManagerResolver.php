<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ReportingManagerResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        return $requestedBy->reportingManager;
    }
}
