<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admin can view all users', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('manager can view users', function () {
    $manager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->getJson('/api/users');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('staff cannot view users', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    Sanctum::actingAs($staff);

    $response = $this->getJson('/api/users');

    $response->assertStatus(403);
});

test('admin can create users', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/users', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'role' => 'staff',
        'status' => true,
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data']);
});

test('non-admin cannot create users', function () {
    $manager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->postJson('/api/users', [
        'name' => 'New User',
        'email' => 'new@example.com',
        'password' => 'password',
        'role' => 'staff',
        'status' => true,
    ]);

    $response->assertStatus(403);
});