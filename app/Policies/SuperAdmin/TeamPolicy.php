<?php

namespace App\Policies\SuperAdmin;

use App\Models\SuperAdminUser;
use App\Models\Team;
use Illuminate\Auth\Access\HandlesAuthorization;

class TeamPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the super admin can view any teams.
     */
    public function viewAny(SuperAdminUser $superAdmin)
    {
        return $superAdmin->hasPermissionTo('list teams', 'super_admin');
    }

    /**
     * Determine whether the super admin can view the team.
     */
    public function view(SuperAdminUser $superAdmin, Team $team)
    {
        return $superAdmin->hasPermissionTo('show team', 'super_admin');
    }

    /**
     * Determine whether the super admin can create teams.
     */
    public function create(SuperAdminUser $superAdmin)
    {
        return $superAdmin->hasPermissionTo('create team', 'super_admin');
    }

    /**
     * Determine whether the super admin can update the team.
     */
    public function update(SuperAdminUser $superAdmin, Team $team)
    {
        return $superAdmin->hasPermissionTo('update team', 'super_admin');
    }

    /**
     * Determine whether the super admin can delete the team.
     */
    public function delete(SuperAdminUser $superAdmin, Team $team)
    {
        return $superAdmin->hasPermissionTo('delete team', 'super_admin');
    }
}
