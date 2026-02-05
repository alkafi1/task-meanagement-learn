<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\TeamController as SuperAdminTeamController;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Super Admin Routes
    Route::prefix('super-admin')->group(function () {
        Route::post('/login', [SuperAdminAuthController::class, 'login']);

        Route::middleware(['auth:sanctum', SuperAdminMiddleware::class])->group(function () {
            Route::post('/logout', [SuperAdminAuthController::class, 'logout']);

            // User Management
            Route::apiResource('users', SuperAdminUserController::class);

            // Team Management
            Route::apiResource('teams', SuperAdminTeamController::class);
        });
    });
    // Admin Routes
    Route::prefix('admin')->group(function () {

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

});
