<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        if (!Gate::allows('view-users')) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $users = User::all();
        
        return response()->json(['data' => $users]);
    }

    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UserRequest $userRequest): JsonResponse
    {
        if (!Gate::allows('manage-users')) {
            return response()->json(['message' => 'unauthorized'], 403);
        }

        $validatedData = $userRequest->validated();

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        ActivityLog::log($request->user()->id, 'create_user', "created user {$user->email}");

        return response()->json([
            'message' => 'user created successfully',
            'data' => $user,
        ], 201);
    }

}
