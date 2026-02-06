<?php

namespace App\Policies\SuperAdmin;

use App\Models\SuperAdminUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the super admin can view any users.
     */
    public function viewAny(SuperAdminUser $superAdmin)
    {
        return $superAdmin->hasPermissionTo('list users', 'super_admin');
    }

    /**
     * Determine whether the super admin can view the user.
     */
    public function view(SuperAdminUser $superAdmin, SuperAdminUser $user)
    {
        return $superAdmin->hasPermissionTo('show user', 'super_admin');
    }

    /**
     * Determine whether the super admin can create users.
     */
    public function create(SuperAdminUser $superAdmin)
    {
        return $superAdmin->hasPermissionTo('create user', 'super_admin');
    }

    /**
     * Determine whether the super admin can update the user.
     */
    public function update(SuperAdminUser $superAdmin, SuperAdminUser $user)
    {
        return $superAdmin->hasPermissionTo('update user', 'super_admin');
    }

    /**
     * Determine whether the super admin can delete the user.
     */
    public function delete(SuperAdminUser $superAdmin, SuperAdminUser $user)
    {
        return $superAdmin->hasPermissionTo('delete user', 'super_admin');
    }
}
