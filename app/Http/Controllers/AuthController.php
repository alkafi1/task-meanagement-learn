<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return ApiResponse::created(
            __('messages.register_success'),
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return ApiResponse::success(
            200,
            __('messages.login_success'),
            [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ]
        );
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return ApiResponse::success(
            200,
            __('messages.logout_success')
        );
    }

    /**
     * Get current authenticated user.
     */
    public function currentUser(Request $request): JsonResponse
    {
        return ApiResponse::success(
            200,
            __('messages.user_retrieved'),
            [
                'user' => new UserResource($request->user()),
            ]
        );
    }

    /**
     * Change password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated('new_password'));

        return ApiResponse::success(
            200,
            __('messages.password_changed')
        );
    }
}
