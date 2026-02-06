<?php

namespace App\Services\SuperAdmin;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
     * Create a new team with an owner.
     *
     * @param array $data
     * @return Team
     */
    public function createTeam(array $data): Team
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the User (Team Owner)
            $owner = User::create([
                'name' => $data['owner_name'],
                'email' => $data['owner_email'],
                'password' => Hash::make($data['owner_password']),
            ]);

            // 2. Create the Team and assign owner
            $team = Team::create([
                'name' => $data['name'],
                'slug' => $data['slug'] ?? null,
                'owner_id' => $owner->id,
            ]);

            // 3. Link user to team
            $owner->update(['team_id' => $team->id]);

            // 4. Assign 'team-admin' role to owner (Spatie-team-scoped)
            // Note: We need to define this role in the seeder or sync command later
            setPermissionsTeamId($team->id);

            // For now, let's just make sure the user is marked as owner
            // We'll handle custom team roles in a separate step if needed

            return $team;
        });
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
