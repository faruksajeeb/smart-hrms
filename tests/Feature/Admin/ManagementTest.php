<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

test('admins can view management indexes', function () {
    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $this->actingAs($admin)->get(route('admin.users.index'))
        ->assertOk();

    $this->actingAs($admin)->get(route('admin.roles.index'))
        ->assertOk();

    $this->actingAs($admin)->get(route('admin.permissions.index'))
        ->assertOk();
});

test('admins can create custom permissions, roles, and users', function () {
    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $this->actingAs($admin)->post(route('admin.permissions.store'), [
        'name' => 'export reports',
    ])->assertRedirect(route('admin.permissions.index'));

    $this->assertDatabaseHas('permissions', [
        'name' => 'export reports',
    ]);

    $this->actingAs($admin)->post(route('admin.roles.store'), [
        'name' => 'team lead',
        'permissions' => ['export reports'],
    ])->assertRedirect(route('admin.roles.index'));

    $this->assertDatabaseHas('roles', [
        'name' => 'team lead',
    ]);

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Lead User',
        'email' => 'lead@example.com',
        'employee_id' => 'EMP-500',
        'primary_role' => User::ROLE_EMPLOYEE,
        'additional_roles' => ['team lead'],
        'status' => User::STATUS_ACTIVE,
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('admin.users.index', absolute: false));

    $user = User::where('email', 'lead@example.com')->firstOrFail();

    expect($user->hasRole(User::ROLE_EMPLOYEE))->toBeTrue();
    expect($user->hasRole('team lead'))->toBeTrue();
});

test('system roles and permissions can not be deleted', function () {
    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $role = Role::findByName(User::ROLE_ADMIN, 'web');
    $permission = Permission::findByName('manage users', 'web');

    $this->actingAs($admin)->delete(route('admin.roles.destroy', $role))
        ->assertSessionHas('error');

    $this->actingAs($admin)->delete(route('admin.permissions.destroy', $permission))
        ->assertSessionHas('error');

    expect(Role::findByName(User::ROLE_ADMIN, 'web'))->not->toBeNull();
    expect(Permission::findByName('manage users', 'web'))->not->toBeNull();
});
