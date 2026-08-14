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
            'manage master data',
            'master-data.view-master-data',
            'master-data.create-master-data',
            'master-data.edit-master-data',
            'master-data.delete-master-data',
            'manage attendance',
            'manage payroll',
            'manage leave requests',
            'manage leave types',
            'manage leave policies',
            'manage leave assignments',
            'manage holidays',
            'manage leave balances',
            'view leave ledger',
            'view reports',
            'leave.year_end.view',
            'leave.year_end.preview',
            'leave.year_end.process',
            'leave.year_end.reprocess',
            'leave.year_end.encashment',
            'leave.year_end.export',
            'leave.reports.view',
            'leave.reports.balance',
            'leave.reports.ledger',
            'leave.reports.employee_history',
            'leave.reports.department',
            'leave.reports.monthly',
            'leave.reports.utilization',
            'leave.reports.export',
            'leave.reports.view',
            'leave.reports.balance',
            'leave.reports.ledger',
            'leave.reports.employee_history',
            'leave.reports.department',
            'leave.reports.monthly',
            'leave.reports.utilization',
            'leave.reports.export',
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
                'manage master data',
                'master-data.view-master-data',
                'master-data.create-master-data',
                'master-data.edit-master-data',
                'master-data.delete-master-data',
                'manage attendance',
                'manage payroll',
                'manage leave requests',
                'manage leave types',
                'manage leave policies',
                'manage leave assignments',
                'manage holidays',
                'manage leave balances',
                'view leave ledger',
                'view reports',
                'leave.year_end.view',
                'leave.year_end.preview',
                'leave.year_end.process',
                'leave.year_end.encashment',
                'leave.reports.view',
                'leave.reports.balance',
                'leave.reports.ledger',
                'leave.reports.employee_history',
                'leave.reports.department',
                'leave.reports.monthly',
                'leave.reports.utilization',
                'leave.reports.export',
                'leave.reports.view',
                'leave.reports.balance',
                'leave.reports.ledger',
                'leave.reports.employee_history',
                'leave.reports.department',
                'leave.reports.monthly',
                'leave.reports.utilization',
                'leave.reports.export',
            ],
            User::ROLE_EMPLOYEE => [
                'manage attendance',
                'manage leave requests',
                'leave.view-own-applications',
                'leave.apply',
                'leave.edit-own-application',
                'leave.submit-application',
                'leave.cancel-own-application',
                'leave.withdraw-application',
                'leave.upload-attachment',
                'leave.delete-attachment',
                'leave.download-attachment',
                'leave.view-delegations',
                'leave.accept-delegation',
                'leave.decline-delegation',
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
