<?php

namespace App\Providers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define('view-users', function (User $user) {
            return $user->isAdmin() || $user->isManager();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-tasks', function (User $user, Task $task) {
            if ($user->isAdmin()) {
                return true;
            }

            return $task->created_by === $user->id || $task->assigned_to === $user->id;
        });

        Gate::define('manage-tasks', function (User $user, Task $task) {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->isManager()) {
                if ($task) {
                    return $task->created_by === $user->id;
                }
                return true;
            }

            if ($user->isStaff()) {
                // staff can only manage their own tasks
                if ($task) {
                    return $task->assigned_to === $user->id;
                }
                return true;
            }
            
            return false;
        });

        Gate::define('assign-tasks', function (User $user, User $assignee = null) {
            if ($user->isAdmin()) {
                return true;
            }
            
            if ($user->isManager() && $assignee && $assignee->isStaff()) {
                return true;
            }
            
            return false;
        });

        // activity log permissions
        Gate::define('view-logs', function (User $user) {
            return $user->isAdmin();
        });
    }
}
