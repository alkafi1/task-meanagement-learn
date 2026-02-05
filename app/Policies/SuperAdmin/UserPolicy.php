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
        return true; // Any super admin can view all users
    }

    /**
     * Determine whether the super admin can view the user.
     */
    public function view(SuperAdminUser $superAdmin, User $user)
    {
        return true;
    }

    /**
     * Determine whether the super admin can create users.
     */
    public function create(SuperAdminUser $superAdmin)
    {
        return true;
    }

    /**
     * Determine whether the super admin can update the user.
     */
    public function update(SuperAdminUser $superAdmin, User $user)
    {
        return true;
    }

    /**
     * Determine whether the super admin can delete the user.
     */
    public function delete(SuperAdminUser $superAdmin, User $user)
    {
        return $superAdmin->role === 'super_admin'; // Only super_admin role can delete
    }
}
