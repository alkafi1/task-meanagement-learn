<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        // Auth routes
        Route::post('/logout', [AuthController::class, 'logout']);

        // User profile routes
        Route::get('/user', [UserController::class, 'currentUser']);
        Route::put('/user', [UserController::class, 'updateProfile']);
        Route::put('/user/change-password', [UserController::class, 'changePassword']);
    });
});
