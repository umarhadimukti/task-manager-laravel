<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Services\TaskService;
use Illuminate\Validation\Rule;
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

    /**
     * Display the specified task.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task): JsonResponse
    {
        if (!Gate::allows('view-task', $task)) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        return response()->json([
            'data' => $task->load(['assignedTo', 'createdBy']),
        ]);
    }

    /**
     * Update the specified task.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TaskRequest $taskRequest, Task $task)
    {
        $user = $request->user();

        if (!Gate::allows('manage-tasks', $task)) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $validatedData = $taskRequest->validated();

        // check permission for task assignment if changing assignment
        if (isset($validatedData['assigned_to']) && $validatedData['assigned_to'] !== $task->assigned_to) {
            $assignee = User::find($validatedData['assigned_to']);
            
            if (!$assignee) {
                return response()->json(['message' => 'assignee not found'], 404);
            }
            
            if (!Gate::allows('assign-tasks', $assignee)) {
                return response()->json([
                    'message' => 'you are not authorized to assign tasks to this user'
                ], 403);
            }
        }

        $task->update($validatedData);

        ActivityLog::log($user->id, 'update_task', "updated task '{$task->title}'");

        return response()->json([
            'message' => 'task updated successfully',
            'data' => $task->load(['assignedTo', 'createdBy']),
        ]);
    }

    /**
     * Remove the specified task.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Task $task)
    {
        $user = auth()->user;
        
        // Only admin or the task creator can delete tasks
        if (!($user->isAdmin() || $task->created_by === $user->id)) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $taskTitle = $task->title;
        $task->delete();

        ActivityLog::log($user->id, 'delete_task', "deleted task '{$taskTitle}'");

        return response()->json([
            'message' => 'task deleted successfully',
        ]);
    }
}
