<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\RoleRequest;
use App\Http\Resources\SuperAdmin\RoleResource;
use App\Services\SuperAdmin\RoleService;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
        $this->authorizeResource(Role::class, 'role');
    }

    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $roles = $this->roleService->getAllRoles($request->query('team_id'));
        return ApiResponse::success(200, __('messages.user_retrieved'), RoleResource::collection($roles));
    }

    public function store(RoleRequest $request): JsonResponse
    {
        $role = $this->roleService->createRole($request->validated());
        return ApiResponse::success(201, 'Role created successfully', new RoleResource($role));
    }

    public function show(Role $role): JsonResponse
    {
        return ApiResponse::success(200, __('messages.user_retrieved'), new RoleResource($role->load('permissions')));
    }

    public function update(RoleRequest $request, Role $role): JsonResponse
    {
        $updatedRole = $this->roleService->updateRole($role, $request->validated());
        return ApiResponse::success(200, 'Role updated successfully', new RoleResource($updatedRole));
    }

    public function destroy(Role $role): JsonResponse
    {
        try {
            $this->roleService->deleteRole($role);
            return ApiResponse::success(200, 'Role deleted successfully');
        } catch (\Exception $e) {
            return ApiResponse::error(403, $e->getMessage());
        }
    }

    /**
     * List all available permissions defined in config.
     */
    public function permissions(): JsonResponse
    {
        $this->authorize('view permissions', Role::class);

        $permissionConfig = config('permissions.codes', []);
        $formatted = [];

        foreach ($permissionConfig as $name => $data) {
            $formatted[] = [
                'name' => $name,
                'code' => $data['code'],
                'group' => explode(' ', $name)[1] ?? 'other'
            ];
        }

        return ApiResponse::success(200, __('messages.user_retrieved'), $formatted);
    }
}
