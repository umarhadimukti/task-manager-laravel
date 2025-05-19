<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        dd($request);
        // Attempt to authenticate the user
        if (!Auth::attempt($loginRequest->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['invalid email or password.'],
            ]);
        }

        $user = $request->user();

        // check if the user is active
        if (!$user->isActive()) {
            throw ValidationException::withMessages([
                'email' => ['your account is inactive, please contact the administrator.'],
            ]);
        }

        // create a new token
        $token = $user->createToken('api_token')->plainTextToken;

        // log the login activity
        ActivityLog::log($user->id, 'login', 'user logged in');

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
        ActivityLog::log($user->id, 'logout', 'user logged out');
        
        // Revoke the token that was used to authenticate the current request
        $user->currentAccessToken()->delete();

        return response()->json(['message' => 'logged out successfully']);
    }
}
