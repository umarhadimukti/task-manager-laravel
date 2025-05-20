<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function getTasks(User $user)
    {
        // admin can see all the tasks
        if ($user->isAdmin()) {
            return Task::with(['assignedTo', 'createdBy'])->get();
        }

        // manager can see tasks assigned to staff and tasks they created
        if ($user->isManager()) {
            return Task::with(['assignedTo', 'createdBy'])
                ->where(function ($query) use ($user) {
                    $query->where('created_by', $user->id)
                        ->orWhereHas('assignedTo', function ($query) use ($user) {
                            $query->where('role', 'staff');
                        });
                })->get();
        }

        // staff can only see tasks assigned to them and tasks created by them
        if ($user->isStaff()) {
            return Task::with(['assignedTo', 'createdBy'])
                ->where(function ($query) use ($user) {
                    $query->where('assigned_to', $user->id)
                        ->orWhere('created_by', $user->id);
                })->get();
        }
    }
    
}