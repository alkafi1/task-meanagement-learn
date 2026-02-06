<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $permissionConfig = config('permissions.codes', []);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'guard_name' => $this->guard_name,
            'permissions' => $this->permissions->map(function ($permission) use ($permissionConfig) {
                return [
                    'name' => $permission->name,
                    'code' => $permissionConfig[$permission->name]['code'] ?? null,
                ];
            }),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
