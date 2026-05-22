<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default Smart HR administrator account.
     */
    public function run(): void
    {
        $admin = User::query()
            ->where('email', 'admin@smart-hr.test')
            ->orWhere('employee_id', 'ADM-001')
            ->firstOrNew();

        $admin->fill([
            'name' => 'Smart HR Admin',
            'email' => 'admin@smart-hr.test',
            'employee_id' => 'ADM-001',
            'password' => Hash::make('password'),
            'status' => User::STATUS_ACTIVE,
        ]);

        $admin->save();

        $admin->syncRoles([User::ROLE_ADMIN]);
        $admin->syncPermissions(Permission::query()->pluck('name')->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
