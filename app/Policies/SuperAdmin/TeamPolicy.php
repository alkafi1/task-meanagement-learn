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
        return true;
    }

    /**
     * Determine whether the super admin can view the team.
     */
    public function view(SuperAdminUser $superAdmin, Team $team)
    {
        return true;
    }

    /**
     * Determine whether the super admin can create teams.
     */
    public function create(SuperAdminUser $superAdmin)
    {
        return true;
    }

    /**
     * Determine whether the super admin can update the team.
     */
    public function update(SuperAdminUser $superAdmin, Team $team)
    {
        return true;
    }

    /**
     * Determine whether the super admin can delete the team.
     */
    public function delete(SuperAdminUser $superAdmin, Team $team)
    {
        return $superAdmin->role === 'super_admin';
    }
}
