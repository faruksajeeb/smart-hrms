<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;

interface ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User;
}
