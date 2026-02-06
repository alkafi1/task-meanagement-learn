<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use App\Models\Team;
use App\Models\User;
use App\Http\Requests\Team\LoginRequest;
use App\Services\Team\TeamAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @var TeamAuthService
     */
    protected $authService;

    /**
     * AuthController constructor.
     *
     * @param TeamAuthService $authService
     */
    public function __construct(TeamAuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Team login.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());

        return ApiResponse::success(200, __('messages.login_success'), $result);
    }

    /**
     * Get authenticated user's permissions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function permissions(Request $request): JsonResponse
    {
        $permissions = $this->authService->getPermissions($request->user());

        return ApiResponse::success(200, __('messages.user_retrieved'), [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Team logout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(200, __('messages.logout_success3'));
    }
}
