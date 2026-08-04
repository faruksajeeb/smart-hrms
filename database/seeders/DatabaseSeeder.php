<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DummyPermissionSeeder::class,
            RolePermissionSeeder::class,
            DummyRoleSeeder::class,
            AdminUserSeeder::class,
            DummyUserSeeder::class,
            DummyMasterDataItemSeeder::class,
            ShiftSeeder::class,
            WeeklyOffPolicySeeder::class,
        ]);
    }
}
