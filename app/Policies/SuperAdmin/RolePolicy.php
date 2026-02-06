<?php

namespace App\Policies\SuperAdmin;

use App\Models\SuperAdminUser;
use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the super admin can view any roles.
     */
    public function viewAny(SuperAdminUser $superAdmin)
    {
        return $superAdmin->hasPermissionTo('list roles', 'super_admin');
    }

    /**
     * Determine whether the super admin can view the role.
     */
    public function view(SuperAdminUser $superAdmin, Role $role)
    {
        return $superAdmin->hasPermissionTo('show role', 'super_admin');
    }

    /**
     * Determine whether the super admin can create roles.
     */
    public function create(SuperAdminUser $superAdmin)
    {
        return $superAdmin->hasPermissionTo('create role', 'super_admin');
    }

    /**
     * Determine whether the super admin can update the role.
     */
    public function update(SuperAdminUser $superAdmin, Role $role)
    {
        return $superAdmin->hasPermissionTo('update role', 'super_admin');
    }

    /**
     * Determine whether the super admin can delete the role.
     */
    public function delete(SuperAdminUser $superAdmin, Role $role)
    {
        return $superAdmin->hasPermissionTo('delete role', 'super_admin');
    }
}
