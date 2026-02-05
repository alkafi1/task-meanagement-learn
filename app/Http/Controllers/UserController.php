<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected UserService $userService;

    /**
     * Inject UserService dependency
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get current authenticated user profile.
     *
     * Returns the authenticated user's profile information.
     * Uses auth()->user() to ensure multi-tenant awareness.
     *
     * @example GET /api/v1/user
     * @example Response: {"success": true, "code": 200, "message": "User retrieved", "data": {"user": {...}}}
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function currentUser(Request $request): JsonResponse
    {
        // Get authenticated user (multi-tenant safe)
        $user = $request->user();

        return ApiResponse::success(
            200,
            __('messages.user_retrieved'),
            [
                'user' => new UserResource($user),
            ]
        );
    }

    /**
     * Update user profile.
     *
     * Updates the authenticated user's name, email, and optionally avatar.
     * All business logic is handled by UserService.
     *
     * @example PUT /api/v1/user
     * @example Request Body: {"name": "John Doe", "email": "john@example.com", "avatar": <file>}
     * @example Response: {"success": true, "code": 200, "message": "Profile updated", "data": {"user": {...}}}
     *
     * @param  UpdateProfileRequest  $request  Validated request with name, email, and optional avatar
     * @param  UserService  $userService  Injected service for handling business logic
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request, UserService $userService): JsonResponse
    {
        // Get authenticated user (multi-tenant safe)
        $user = $request->user();

        // Delegate business logic to UserService
        $updatedUser = $userService->updateProfile($user, $request->validated());

        return ApiResponse::success(
            200,
            __('messages.profile_updated'),
            [
                'user' => new UserResource($updatedUser),
            ]
        );
    }

    /**
     * Change user password.
     *
     * Changes the authenticated user's password after validating the current password.
     * Returns 401 if current password is invalid (handled by current_password validation rule).
     * All business logic is handled by UserService.
     *
     * @example PUT /api/v1/user/change-password
     * @example Request Body: {"current_password": "old123", "new_password": "new123", "new_password_confirmation": "new123"}
     * @example Response: {"success": true, "code": 200, "message": "Password changed successfully"}
     *
     * @param  ChangePasswordRequest  $request  Validated request with current and new password
     * @param  UserService  $userService  Injected service for handling business logic
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request, UserService $userService): JsonResponse
    {
        // Get authenticated user (multi-tenant safe)
        $user = $request->user();

        // Delegate password change logic to UserService
        $userService->changePassword($user, $request->validated('new_password'));

        return ApiResponse::success(
            200,
            __('messages.password_changed')
        );
    }
}
