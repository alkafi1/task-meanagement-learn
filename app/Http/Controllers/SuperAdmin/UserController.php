<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\UserRequest;
use App\Http\Resources\SuperAdmin\UserResource;
use App\Models\SuperAdminUser;
use App\Services\SuperAdmin\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
        $this->authorizeResource(SuperAdminUser::class, 'user');
    }

    /**
     * Display a listing of users.
     */
    public function index(): JsonResponse
    {
        $users = $this->userService->getAllUsers();

        return ApiResponse::success(200, __('messages.user_retrieved'), UserResource::collection($users));
    }

    /**
     * Store a newly created user.
     */
    public function store(UserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return ApiResponse::success(201, __('messages.register_success'), new UserResource($user));
    }

    /**
     * Display the specified user.
     */
    public function show(SuperAdminUser $user): JsonResponse
    {
        return ApiResponse::success(200, __('messages.user_retrieved'), new UserResource($user));
    }

    /**
     * Update the specified user.
     *
     * @param UserRequest $request
     * @param SuperAdminUser $user
     * @return JsonResponse
     */
    public function update(UserRequest $request, SuperAdminUser $user): JsonResponse
    {
        // update user
        $updatedUser = $this->userService->updateUser($user, $request->validated());

        // return
        return ApiResponse::success(200, __('messages.profile_updated'), new UserResource($updatedUser));
    }

    /**
     * Remove the specified user.
     *
     * @param SuperAdminUser $user
     * @return JsonResponse
     */
    public function destroy(SuperAdminUser $user): JsonResponse
    {
        $this->userService->deleteUser($user);

        return ApiResponse::success(200, __('messages.logout_success'));
    }

    /**
     * Get available roles for super admin users.
     */
    public function roles(): JsonResponse
    {
        return ApiResponse::success(200, __('messages.user_retrieved'), ['super-admin', 'admin']);
    }
}
