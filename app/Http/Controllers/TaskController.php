<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\TaskRequest;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    protected $taskService;

    /**
     * create a new controller instance.
     *
     * @param \App\Services\TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * display a listing of the tasks.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $tasks = $this->taskService->getTasks($user);

        return response()->json(['tasks' => $tasks], 200);
    }

    /**
     * store task with some rules
     */
    public function store(Request $request, TaskRequest $taskRequest): JsonResponse
    {
        $user = $request->user();
        $assignedTo = User::findOrFail($taskRequest->assigned_to);

        if (Gate::forUser($user)->denies('assign-tasks', [$assignedTo])) {
            return response()->json(['message' => 'you are not allowed to assign tasks to this user'], 403);
        }

        $createdTask = Task::create([
            'title' => $taskRequest->title,
            'description' => $taskRequest->description,
            'assigned_to' => $taskRequest->assigned_to,
            'status' => $taskRequest->status ?? 'pending',
            'due_date' => $taskRequest->due_date,
            'created_by' => $user->id,
        ]);

        // log the activity
        ActivityLog::log($user->id, 'create_task', "task {$createdTask->id} created.");

        return response()->json([
            'message' => 'task created',
            'task' => $createdTask,
        ], 201);
    }
}
