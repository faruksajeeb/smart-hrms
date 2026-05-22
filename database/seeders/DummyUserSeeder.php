<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyUserSeeder extends Seeder
{
    /**
     * Seed realistic demo users with system and custom roles.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Nusrat Jahan',
                'email' => 'hr.manager@smart-hr.test',
                'employee_id' => 'HR-1001',
                'status' => User::STATUS_ACTIVE,
                'roles' => [User::ROLE_HR, 'recruiter'],
                'permissions' => [],
            ],
            [
                'name' => 'Tanvir Hasan',
                'email' => 'payroll.lead@smart-hr.test',
                'employee_id' => 'HR-1002',
                'status' => User::STATUS_ACTIVE,
                'roles' => [User::ROLE_HR, 'finance-manager'],
                'permissions' => ['view reports'],
            ],
            [
                'name' => 'Sadia Rahman',
                'email' => 'onboarding@smart-hr.test',
                'employee_id' => 'HR-1003',
                'status' => User::STATUS_ACTIVE,
                'roles' => [User::ROLE_HR, 'onboarding-coordinator'],
                'permissions' => [],
            ],
            [
                'name' => 'Rakib Hossain',
                'email' => 'team.lead@smart-hr.test',
                'employee_id' => 'EMP-2001',
                'status' => User::STATUS_ACTIVE,
                'roles' => [User::ROLE_EMPLOYEE, 'team-lead'],
                'permissions' => ['view reports'],
            ],
            [
                'name' => 'Maliha Sultana',
                'email' => 'employee.one@smart-hr.test',
                'employee_id' => 'EMP-2002',
                'status' => User::STATUS_ACTIVE,
                'roles' => [User::ROLE_EMPLOYEE],
                'permissions' => [],
            ],
            [
                'name' => 'Farhan Kabir',
                'email' => 'employee.two@smart-hr.test',
                'employee_id' => 'EMP-2003',
                'status' => User::STATUS_INACTIVE,
                'roles' => [User::ROLE_EMPLOYEE],
                'permissions' => ['manage attendance'],
            ],
        ];

        foreach ($users as $attributes) {
            $user = User::query()
                ->where('email', $attributes['email'])
                ->orWhere('employee_id', $attributes['employee_id'])
                ->firstOrNew();

            $user->fill([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'employee_id' => $attributes['employee_id'],
                'password' => Hash::make('password'),
                'status' => $attributes['status'],
            ]);
            $user->email_verified_at = now();

            $user->save();
            $user->syncRoles($attributes['roles']);
            $user->syncPermissions($attributes['permissions']);
        }
    }
}
