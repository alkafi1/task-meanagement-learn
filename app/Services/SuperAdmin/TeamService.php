<?php

namespace App\Services\SuperAdmin;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class TeamService
{
    /**
     * Get all teams across tenants.
     *
     * @return Collection
     */
    public function getAllTeams(): Collection
    {
        return Team::with('owner')->get();
    }

    /**
     * Create a new team.
     *
     * @param array $data
     * @return Team
     */
    public function createTeam(array $data): Team
    {
        return Team::create($data);
    }

    /**
     * Update an existing team.
     *
     * @param Team $team
     * @param array $data
     * @return Team
     */
    public function updateTeam(Team $team, array $data): Team
    {
        $team->update($data);

        return $team->fresh();
    }

    /**
     * Delete a team.
     *
     * @param Team $team
     * @return void
     */
    public function deleteTeam(Team $team): void
    {
        $team->delete();
    }
}
