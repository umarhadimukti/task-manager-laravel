<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // check if the user can view users
        if (Gate::denies('view-users')) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        // get users
        $users = User::query()
            ->when($request->user()->isManager(), function ($query) {
                $query->where('role', 'staff');
            })->get();

        return response()->json(['users' => $users], 200);
    }

    public function store(UserRequest $request): JsonResponse
    {
        // check if user can create users
        if (Gate::denies('create-user')) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        // create user
        $createdUser = User::create([
            'id' => Str::uuid()->toString(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
            'status' => $request->status ?? true,
        ]);

        // log the activity
        ActivityLog::log($request->user()->id, 'create_user', "user {$createdUser->id} created.");

        return response()->json([
            'message' => 'user created',
            'data' => ['id' => $createdUser->id, 'name' => $createdUser->name, 'email' => $createdUser->email],
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        // check if user can show user
        if (Gate::denies('show-user', $user)) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        return response()->json(['user' => $user], 200);
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        // check if user can update user
        if (Gate::denies('update-user', $user)) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $user->update([
            'name' => $request->name ?? $user->status,
            'email' => $request->email ?? $user->email,
            'role' => $request->role ?? $user->role,
            'status' => $request->has('status') ? $request->status : $user->status,
        ]);

        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
            $user->save();
        }

        // log the activity
        ActivityLog::log($request->user()->id, 'update-user', "user {$user->id} updated.");

        return response()->json([
            'message' => 'user updated',
            'user' => $user,
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        // check if user can delete user
        if (Gate::denies('delete-user', $user)) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        // log the activity
        ActivityLog::log($request->user()->id, 'delete-user', "user {$user->id} deleted.");

        // remove user
        $user->delete();

        return response()->json(['message' => "user {$user->id} deleted"], 200);
    }
}
