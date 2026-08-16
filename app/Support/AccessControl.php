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
            'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
            'attendance.policy.view', 'attendance.policy.create', 'attendance.policy.edit', 'attendance.policy.delete',
            'attendance.policy_assignment.view', 'attendance.policy_assignment.create', 'attendance.policy_assignment.edit', 'attendance.policy_assignment.delete',
            'attendance.status.view', 'attendance.status.create', 'attendance.status.edit', 'attendance.status.delete',
            'attendance.configuration.view', 'attendance.configuration.create', 'attendance.configuration.edit',
            'attendance.view_own', 'attendance.view_all', 'attendance.process', 'attendance.reprocess', 'attendance.finalize', 'attendance.unlock',
            'attendance.regularization.view', 'attendance.regularization.create', 'attendance.regularization.edit', 'attendance.regularization.submit', 'attendance.regularization.cancel', 'attendance.regularization.withdraw', 'attendance.regularization.approve', 'attendance.regularization.reject',
            'attendance.approval.view', 'attendance.approval.approve', 'attendance.approval.reject', 'attendance.approval.delegate', 'attendance.report.view',
            'attendance.dashboard.view', 'attendance.dashboard.view_team', 'attendance.calendar.view', 'attendance.calendar.view_all', 'attendance.calendar.view_team', 'attendance.dashboard.export', 'attendance.dashboard.view_department', 'attendance.dashboard.view_branch', 'attendance.dashboard.view_company', 'attendance.calendar.view_own', 'attendance.dashboard.view_own',
            'attendance.reports.view', 'attendance.reports.daily', 'attendance.reports.monthly', 'attendance.reports.employee', 'attendance.reports.department', 'attendance.reports.late_early', 'attendance.reports.absenteeism', 'attendance.reports.overtime', 'attendance.reports.summary', 'attendance.reports.export',
            'attendance.payroll.view', 'attendance.payroll.process', 'attendance.payroll.finalize', 'attendance.payroll.lock', 'attendance.payroll.unlock', 'attendance.payroll.reprocess', 'attendance.payroll.export', 'attendance.payroll.override',
            'attendance-period.view', 'attendance-period.create', 'attendance-period.update', 'attendance-period.lock', 'attendance-period.reopen', 'attendance-backdated.view', 'attendance-backdated.override', 'attendance-audit.view', 'attendance-correction-history.view', 'attendance-correction-history.create', 'attendance-import.view', 'attendance-import.create', 'attendance-import.process', 'attendance-device.view', 'attendance-device.sync', 'attendance-reconciliation.view', 'attendance-reconciliation.resolve', 'attendance-exception.view', 'attendance-exception.update', 'attendance-exception.resolve', 'attendance-archive.view', 'attendance-archive.execute', 'attendance-admin.override', 'attendance.device.view', 'attendance.device.create', 'attendance.device.update', 'attendance.device.delete', 'attendance.device.test_connection', 'attendance.device.sync', 'attendance.device_mapping.view', 'attendance.device_mapping.create', 'attendance.device_mapping.update', 'attendance.device_mapping.delete', 'attendance.device_log.view', 'attendance.device_log.retry', 'attendance.device_log.reprocess', 'attendance.device_sync.view', 'attendance.device_sync.retry', 'attendance.devices.view', 'attendance.devices.create', 'attendance.devices.update', 'attendance.devices.delete', 'attendance.devices.test_connection', 'attendance.devices.activate', 'attendance.devices.deactivate', 'attendance.devices.sync',
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
                'attendance.view', 'attendance.policy.view', 'attendance.policy.create', 'attendance.policy.edit',
                'attendance.policy_assignment.view', 'attendance.policy_assignment.create', 'attendance.policy_assignment.edit',
                'attendance.status.view', 'attendance.status.create', 'attendance.status.edit', 'attendance.configuration.view', 'attendance.configuration.create', 'attendance.configuration.edit',
                'attendance.view_all', 'attendance.process', 'attendance.reprocess', 'attendance.finalize', 'attendance.unlock', 'attendance.regularization.view', 'attendance.regularization.approve', 'attendance.regularization.reject', 'attendance.approval.view', 'attendance.approval.approve', 'attendance.approval.reject', 'attendance.report.view',
                'attendance.dashboard.view', 'attendance.calendar.view', 'attendance.calendar.view_all', 'attendance.dashboard.export', 'attendance.dashboard.view_department', 'attendance.dashboard.view_branch', 'attendance.dashboard.view_company',
                'attendance.reports.view', 'attendance.reports.daily', 'attendance.reports.monthly', 'attendance.reports.employee', 'attendance.reports.department', 'attendance.reports.late_early', 'attendance.reports.absenteeism', 'attendance.reports.overtime', 'attendance.reports.summary', 'attendance.reports.export',
                'attendance.payroll.view', 'attendance.payroll.process', 'attendance.payroll.finalize', 'attendance.payroll.lock', 'attendance.payroll.unlock', 'attendance.payroll.reprocess', 'attendance.payroll.export', 'attendance.payroll.override',
                'attendance-period.view', 'attendance-period.create', 'attendance-period.update', 'attendance-period.lock', 'attendance-period.reopen', 'attendance-backdated.view', 'attendance-audit.view', 'attendance-correction-history.view', 'attendance-correction-history.create', 'attendance-import.view', 'attendance-import.create', 'attendance-import.process', 'attendance-device.view', 'attendance-device.sync', 'attendance-reconciliation.view', 'attendance-reconciliation.resolve', 'attendance-exception.view', 'attendance-exception.update', 'attendance-exception.resolve', 'attendance-archive.view', 'attendance-archive.execute', 'attendance-admin.override', 'attendance.device.view', 'attendance.device.create', 'attendance.device.update', 'attendance.device.delete', 'attendance.device.test_connection', 'attendance.device.sync', 'attendance.device_mapping.view', 'attendance.device_mapping.create', 'attendance.device_mapping.update', 'attendance.device_mapping.delete', 'attendance.device_log.view', 'attendance.device_log.retry', 'attendance.device_log.reprocess', 'attendance.device_sync.view', 'attendance.device_sync.retry',
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
                'attendance.view_own', 'attendance.regularization.view', 'attendance.regularization.create', 'attendance.regularization.edit', 'attendance.regularization.submit', 'attendance.regularization.cancel', 'attendance.regularization.withdraw',
                'attendance.dashboard.view_own', 'attendance.calendar.view_own',
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
