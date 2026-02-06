<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $permissionConfig = config('permissions.codes', []);

        $originalTeamId = getPermissionsTeamId();
        if ($this->resource instanceof \App\Models\User && $this->team_id) {
            setPermissionsTeamId($this->team_id);
        }

        $permissions = $this->getAllPermissions()->pluck('name')->map(function ($name) use ($permissionConfig) {
            return $permissionConfig[$name]['code'] ?? $name;
        });

        if ($this->resource instanceof \App\Models\User) {
            setPermissionsTeamId($originalTeamId);
        }

        // If super-admin, they might have specific codes or all codes
        if ($this->hasRole('super-admin', 'super_admin')) {
             $permissions = array_column($permissionConfig, 'code');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ?? null,
            'role' => $this->role ?? 'user', // Legacy field
            'roles' => $this->getRoleNames(),
            'permissions' => $permissions,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
