<?php

namespace App\Support;

use App\Models\User;

class AccessControl
{
    /**
     * Return the system roles that map directly to application dashboards.
     *
     * @return array<int, string>
     */
    public static function systemRoles(): array
    {
        return [
            User::ROLE_ADMIN,
            User::ROLE_HR,
            User::ROLE_EMPLOYEE,
        ];
    }

    public static function systemPermissions(): array
    {
        return [
            'manage users',
            'manage roles',
            'manage permissions',
            'manage employees',
            'manage attendance',
            'manage payroll',
            'manage leave requests',
            'view reports',
            'doc-analyzer.view-doc-analyzer',
        ];
    }

    /**
     * Return the default permissions assigned to each system role.
     *
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            User::ROLE_ADMIN => self::systemPermissions(),
            User::ROLE_HR => [
                'manage employees',
                'manage attendance',
                'manage payroll',
                'manage leave requests',
                'view reports',
            ],
            User::ROLE_EMPLOYEE => [
                'manage attendance',
                'manage leave requests',
            ],
        ];
    }

    /**
     * Determine if the given role name is one of the protected system roles.
     */
    public static function isProtectedRole(string $name): bool
    {
        return in_array($name, self::systemRoles(), true);
    }

    /**
     * Determine if the given permission name is one of the protected system permissions.
     */
    public static function isProtectedPermission(string $name): bool
    {
        return in_array($name, self::systemPermissions(), true);
    }
}
