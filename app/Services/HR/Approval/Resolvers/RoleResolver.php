<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RoleResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        $roleId = $context['role_id'] ?? null;

        if (!$roleId) {
            return null;
        }

        $role = Role::find($roleId);

        if (!$role) {
            return null;
        }

        return User::role($role->name)->first();
    }
}
