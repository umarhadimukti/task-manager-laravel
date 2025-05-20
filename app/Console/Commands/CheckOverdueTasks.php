<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:check-overdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check for overdue tasks and log them';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $overdueTasks = Task::where('status', '!=', 'done')
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->get();

        $count = 0;
        foreach ($overdueTasks as $task) {
            // Create activity log for each overdue task
            ActivityLog::create([
                'user_id' => $task->assigned_to,
                'action' => 'task_overdue',
                'description' => "Task overdue: {$task->id}",
                'logged_at' => now(),
            ]);

            // Also log to application log file
            Log::channel('daily')->warning("Task {$task->id} is overdue. Due date was: {$task->due_date}");
            
            $count++;
        }

        $this->info("Found {$count} overdue tasks");
        return Command::SUCCESS;
    }
}