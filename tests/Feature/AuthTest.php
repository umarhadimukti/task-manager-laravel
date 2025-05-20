<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('users can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'status' => true,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['token', 'user']);
});

test('users cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'status' => true,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
});

test('inactive users cannot login', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'password' => bcrypt('password'),
        'status' => false,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'inactive@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(403)
        ->assertJson(['message' => 'User account is inactive']);
});
