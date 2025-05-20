<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

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
        $today = now()->startOfDay();
        
        // get tasks that are overdue but not marked as done
        $overdueTasks = Task::where('status', '!=', 'done')
            ->whereDate('due_date', '<', $today)
            ->get();
            
        $count = 0;
        
        foreach ($overdueTasks as $task) {
            // check if the task has already been marked as overdue in logs
            // this prevents multiple logs for the same overdue task
            ActivityLog::log(
                'task_overdue',
                "task overdue: {$task->id} - {$task->title}",
                $task->created_by
            );
            
            $count++;
        }
        
        $this->info("found {$count} overdue tasks and logged them.");
        
        return Command::SUCCESS;
    }
}