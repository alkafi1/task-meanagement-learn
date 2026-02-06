<?php

namespace App\Services\Team;

use App\Models\Role;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function getTeamRoles(): Collection
    {
        return Role::where('guard_name', 'team')
            ->with('permissions')
            ->get();
    }

    public function createTeamRole(array $data): Role
    {
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'team',
        ]);

        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    }

    public function updateTeamRole(Role $role, array $data): Role
    {
        $role->update(['name' => $data['name']]);

        if (isset($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role->fresh('permissions');
    }

    public function deleteTeamRole(Role $role): void
    {
        // Prevent deleting core team roles
        if (in_array($role->name, ['team-admin', 'team-member'])) {
            throw new \Exception('Cannot delete core team roles.');
        }

        $role->delete();
    }
}
