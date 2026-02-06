<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdmin\AuthController as SuperAdminAuthController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\TeamController as SuperAdminTeamController;
use App\Http\Controllers\SuperAdmin\RoleController as SuperAdminRoleController;
use App\Http\Controllers\TeamAuthController;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Super Admin Routes
    Route::prefix('super-admin')->name('super-admin.')->group(function () {
        Route::post('/login', [SuperAdminAuthController::class, 'login'])->name('login');

        Route::middleware(['auth:sanctum', SuperAdminMiddleware::class])->group(function () {
            Route::post('/logout', [SuperAdminAuthController::class, 'logout'])->name('logout');

            // User Management
            Route::get('users/roles', [SuperAdminUserController::class, 'roles'])->name('users.roles');

            Route::apiResource('users', SuperAdminUserController::class)->names([
                'index' => 'users.index',
                'store' => 'users.store',
                'show' => 'users.show',
                'update' => 'users.update',
                'destroy' => 'users.destroy',
            ]);

            // Team Management
            Route::apiResource('teams', SuperAdminTeamController::class)->names([
                'index' => 'teams.index',
                'store' => 'teams.store',
                'show' => 'teams.show',
                'update' => 'teams.update',
                'destroy' => 'teams.destroy',
            ]);

            // Role Management
            Route::get('permissions', [SuperAdminRoleController::class, 'permissions'])->name('permissions.index');
            Route::apiResource('roles', SuperAdminRoleController::class);
        });
    });

    // Team Routes (SaaS)
    Route::prefix('team')->name('team.')->group(function () {
        Route::post('/login', [TeamAuthController::class, 'login'])->name('login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [TeamAuthController::class, 'logout'])->name('logout');
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
