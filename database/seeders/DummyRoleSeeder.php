<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DummyRoleSeeder extends Seeder
{
    /**
     * Seed additional demo roles for the admin screens.
     */
    public function run(): void
    {
        $roles = [
            'recruiter' => [
                'manage employees',
                'recruitment.view-candidates',
                'recruitment.schedule-interviews',
            ],
            'finance-manager' => [
                'manage payroll',
                'view reports',
                'finance.approve-expenses',
                'finance.process-reimbursements',
            ],
            'team-lead' => [
                'manage attendance',
                'manage leave requests',
                'performance.review-team',
                'performance.approve-goals',
            ],
            'onboarding-coordinator' => [
                'manage employees',
                'onboarding.manage-checklists',
                'onboarding.assign-buddy',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
