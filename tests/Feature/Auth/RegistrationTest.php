<?php

use App\Models\User;

test('admins can access the user creation screen', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_ADMIN);

    $response = $this->actingAs($admin)->get(route('admin.users.create'));

    $response->assertStatus(200);
});

test('guests can not access the registration screen', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

test('admins can create new users with roles', function () {
    $admin = User::factory()->create();
    $admin->assignRole(User::ROLE_ADMIN);

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Team Member',
        'email' => 'team.member@example.com',
        'employee_id' => 'EMP-019',
        'primary_role' => User::ROLE_EMPLOYEE,
        'additional_roles' => [],
        'status' => User::STATUS_ACTIVE,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect(route('admin.users.index', absolute: false));

    $this->assertDatabaseHas('users', [
        'email' => 'team.member@example.com',
        'employee_id' => 'EMP-019',
        'status' => User::STATUS_ACTIVE,
    ]);

    $user = User::where('email', 'team.member@example.com')->firstOrFail();

    expect($user->hasRole(User::ROLE_EMPLOYEE))->toBeTrue();
});
