<?php

namespace App\Services\HR\Approval\Resolvers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HRManagerResolver implements ApproverResolverInterface
{
    public function resolve(User $requestedBy, ?array $context = []): ?User
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'HR Manager');
        })->first();
    }
}
