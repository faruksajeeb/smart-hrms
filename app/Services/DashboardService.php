<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardService
{
    public function getAdminSummary(): array
    {
        return Cache::tags(['dashboard'])->remember(
            'admin-summary',
            now()->addMinutes(5),
            function () {
                return [
                    'activeUsers' => User::where(
                        'status',
                        User::STATUS_ACTIVE
                    )->count(),

                    'openRoles' => Role::count(),

                    'permissionCount' => Permission::count(),
                ];
            }
        );
        // return Cache::remember(
        //     'dashboard:admin:summary',
        //     now()->addMinutes(10),
        //     function () {
        //         return [
        //             'activeUsers' => User::where(
        //                 'status',
        //                 User::STATUS_ACTIVE
        //             )->count(),

        //             'openRoles' => Role::count(),

        //             'permissionCount' => Permission::count(),
        //         ];
        //     }
        // );
    }
}