<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * handle a login request to the application.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request, LoginRequest $loginRequest)
    {
        $user = User::where('email', $loginRequest->email)->first();

        if (!$user || !Hash::check($loginRequest->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['invalid email address or password.'],
            ]);
        }

        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['your account is inactive. please contact an administrator.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;
        
        ActivityLog::log($user->id, 'user_login', "user {$user->email} logged in");

        return response()->json([
            'success' => true,
            'message' => 'login successful.',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log the logout activity
        ActivityLog::log($user->id, 'user_logout', 'user logged out');
        
        // Revoke the token that was used to authenticate the current request
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'logged out successfully']);
    }
}
