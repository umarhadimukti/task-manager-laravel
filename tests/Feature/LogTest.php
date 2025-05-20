// File: tests/Feature/LogsTest.php
<?php

use App\Models\User;
use App\Models\ActivityLog;
use Laravel\Sanctum\Sanctum;

test('admin can view activity logs', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/logs');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('non-admin cannot view activity logs', function () {
    $manager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->getJson('/api/logs');

    $response->assertStatus(403);
});