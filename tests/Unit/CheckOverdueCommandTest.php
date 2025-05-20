<?php

use App\Console\Commands\CheckOverdueTasks;
use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Artisan;

test('check overdue tasks command creates logs', function () {
    $staff = User::factory()->create([
        'role' => 'staff',
        'status' => true,
    ]);

    // Create overdue task
    $overdueTask = Task::factory()->create([
        'assigned_to' => $staff->id,
        'created_by' => $staff->id,
        'due_date' => now()->subDays(1)->format('Y-m-d'),
        'status' => 'pending'
    ]);

    $initialLogCount = ActivityLog::count();

    Artisan::call('tasks:check-overdue');

    // Ensure a new log was created
    expect(ActivityLog::count())->toBe($initialLogCount + 1);
    
    // Verify the log contains the correct message
    $log = ActivityLog::latest()->first();
    expect($log->description)->toContain("Task overdue: {$overdueTask->id}");
});