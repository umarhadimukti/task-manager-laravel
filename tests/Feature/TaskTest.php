<?php

use App\Models\Task;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('admin can view all tasks', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('manager can view team tasks', function () {
    $manager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('staff can only view their assigned tasks', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    $otherStaff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    $task = Task::factory()->create([
        'assigned_to' => $staff->id,
        'created_by' => $otherStaff->id,
    ]);

    $otherTask = Task::factory()->create([
        'assigned_to' => $otherStaff->id,
        'created_by' => $otherStaff->id,
    ]);

    Sanctum::actingAs($staff);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonStructure(['data'])
        ->assertJsonCount(1, 'data');
});

test('admin can create tasks', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'Task description',
        'assigned_to' => $staff->id,
        'status' => 'pending',
        'due_date' => now()->addDays(3)->format('Y-m-d'),
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data']);
});

test('manager can create tasks for staff', function () {
    $manager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'Task description',
        'assigned_to' => $staff->id,
        'status' => 'pending',
        'due_date' => now()->addDays(3)->format('Y-m-d'),
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data']);
});

test('manager cannot assign tasks to other managers', function () {
    $manager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    $otherManager = User::factory()->create([
        'role' => 'manager',
        'status' => true,
    ]);

    Sanctum::actingAs($manager);

    $response = $this->postJson('/api/tasks', [
        'title' => 'Test Task',
        'description' => 'Task description',
        'assigned_to' => $otherManager->id,
        'status' => 'pending',
        'due_date' => now()->addDays(3)->format('Y-m-d'),
    ]);

    $response->assertStatus(403);
});

test('staff can update their own tasks', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    $task = Task::factory()->create([
        'assigned_to' => $staff->id,
        'created_by' => $staff->id,
    ]);

    Sanctum::actingAs($staff);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'status' => 'in_progress',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data']);
});

test('staff cannot delete tasks', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    $task = Task::factory()->create([
        'assigned_to' => $staff->id,
        'created_by' => $staff->id,
    ]);

    Sanctum::actingAs($staff);

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(403);
});

test('admin can delete any task', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'status' => true,
    ]);

    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    $task = Task::factory()->create([
        'assigned_to' => $staff->id,
        'created_by' => $staff->id,
    ]);

    Sanctum::actingAs($admin);

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(200);
});