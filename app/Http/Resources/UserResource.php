<?php

namespace App\Http\Resources;

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
        $user = $this->resource;

        $permissions = collect([]);
        $roles = $this->getRoleNames();

        // Handle permissions based on team context if team_id exists
        if ($user->team_id) {
            $originalTeamId = getPermissionsTeamId();
            setPermissionsTeamId($user->team_id);

            // Re-fetch roles/permissions in team context
            $roles = $user->getRoleNames();

            $team = \App\Models\Team::find($user->team_id);

            if ($team && ($user->id === $team->owner_id || $user->hasRole('team-admin', 'team'))) {
                $webPermissions = config('permissions.guards.team.permissions', []);
                $permissions = collect($webPermissions)->map(function ($name) use ($permissionConfig) {
                    return [
                        'name' => $name,
                        'code' => $permissionConfig[$name]['code'] ?? null,
                    ];
                });
            } else {
                $permissions = $user->getAllPermissions()->map(function ($permission) use ($permissionConfig) {
                    return [
                        'name' => $permission->name,
                        'code' => $permissionConfig[$permission->name]['code'] ?? null,
                    ];
                });
            }

            setPermissionsTeamId($originalTeamId);
        } else {
            // Non-team user (e.g. Super Admin check might happen here or in SuperAdmin\UserResource)
            $permissions = $user->getAllPermissions()->map(function ($permission) use ($permissionConfig) {
                return [
                    'name' => $permission->name,
                    'code' => $permissionConfig[$permission->name]['code'] ?? null,
                ];
            });
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar,
            'roles' => $roles,
            'permissions' => $permissions,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
