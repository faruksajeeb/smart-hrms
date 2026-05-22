<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();
    $user->assignRole(User::ROLE_EMPLOYEE);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('employee.dashboard', absolute: false));
});

test('admins are redirected to the admin dashboard after login', function () {
    $user = User::factory()->create();
    $user->assignRole(User::ROLE_ADMIN);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();
    $user->assignRole(User::ROLE_EMPLOYEE);

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('inactive users can not authenticate', function () {
    $user = User::factory()->create([
        'status' => User::STATUS_INACTIVE,
    ]);
    $user->assignRole(User::ROLE_EMPLOYEE);

    $response = $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertGuest();
    $response->assertRedirect('/login');
    $response->assertSessionHasErrors('email');
});

test('users can logout', function () {
    $user = User::factory()->create();
    $user->assignRole(User::ROLE_EMPLOYEE);

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
