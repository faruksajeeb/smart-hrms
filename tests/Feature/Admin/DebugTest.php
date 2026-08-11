<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

beforeEach(function () {
    $this->withoutVite();
});

test('simple permission creation', function () {
    $admin = User::factory()->create([
        'status' => User::STATUS_ACTIVE,
    ]);
    $admin->assignRole(User::ROLE_ADMIN);

    $response = $this->actingAs($admin)->post(route('admin.permissions.store'), [
        'name' => 'test permission ' . uniqid(),
    ]);
    
    $response->assertStatus(302);
    $response->assertSessionHas('success');
});
