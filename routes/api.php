<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;

/**
 * public route
 */
Route::post('/login', [AuthController::class, 'login']);

/**
 * protected route
 */
Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    
    // Tasks
    Route::apiResource('tasks', TaskController::class);
    
    // Logs
    Route::get('/logs', [ActivityLogController::class, 'index']);
});
