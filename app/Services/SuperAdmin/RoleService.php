<?php

namespace App\Services\SuperAdmin;

use Spatie\Permission\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function getAllRoles(?int $teamId = null): Collection
    {
        return Role::where('guard_name', 'super_admin')
            ->where('team_id', $teamId)
            ->with('permissions')
            ->get();
    }

    public function createRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'super_admin',
            'team_id' => $data['team_id'] ?? null,
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function updateRole(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh('permissions');
    }

    public function deleteRole(Role $role): void
    {
        // Prevent deleting core roles if necessary
        if (in_array($role->name, ['super-admin', 'admin'])) {
            throw new \Exception('Cannot delete core administrative roles.');
        }

        $role->delete();
    }
}
