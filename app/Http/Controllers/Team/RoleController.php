<?php

namespace App\Http\Controllers\Team;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Team\RoleRequest;
use App\Http\Resources\SuperAdmin\RoleResource;
use App\Services\Team\RoleService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use App\Models\Team;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(): JsonResponse
    {
        $roles = $this->roleService->getTeamRoles();
        return ApiResponse::success(200, __('messages.user_retrieved'), RoleResource::collection($roles));
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $team = current_team();

        if (!$team || $team->owner_id !== auth()->id()) {
            return ApiResponse::error(403, 'Only team owners can create roles.');
        }

        $role = $this->roleService->createTeamRole($request->validated());
        return ApiResponse::success(201, 'Role created successfully', new RoleResource($role));
    }

    public function show(Role $role): JsonResponse
    {
        return ApiResponse::success(200, __('messages.user_retrieved'), new RoleResource($role->load('permissions')));
    }

    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $team = current_team();

        if (!$team || $team->owner_id !== auth()->id()) {
            return ApiResponse::error(403, 'Only team owners can update roles.');
        }

        $updatedRole = $this->roleService->updateTeamRole($role, $request->validated());
        return ApiResponse::success(200, 'Role updated successfully', new RoleResource($updatedRole));
    }

    public function destroy(Role $role): JsonResponse
    {
        $team = current_team();

        if (!$team || $team->owner_id !== auth()->id()) {
            return ApiResponse::error(403, 'Only team owners can delete roles.');
        }

        try {
            $this->roleService->deleteTeamRole($role);
            return ApiResponse::success(200, 'Role deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error(403, $e->getMessage());
        }
    }

    /**
     * List all available team-scoped permissions.
     */
    public function permissions(): JsonResponse
    {
        $permissionConfig = config('permissions.codes', []);
        $teamPermissions = config('permissions.guards.team.permissions', []);

        $formatted = [];
        foreach ($teamPermissions as $name) {
            if (isset($permissionConfig[$name])) {
                $formatted[] = [
                    'name' => $name,
                    'code' => $permissionConfig[$name]['code'],
                    'group' => explode(' ', $name)[1] ?? 'other'
                ];
            }
        }

        return ApiResponse::success(200, __('messages.user_retrieved'), $formatted);
    }
}
