<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;

class SpecificUserResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        $userId = $context['specific_user_id'] ?? null;

        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }
}
