<?php

namespace App\Services\Team;

use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MemberService
{
    public function getTeamMembers(int $teamId)
    {
        return User::where('team_id', $teamId)->get();
    }

    public function addMember(Team $team, array $data): User
    {
        return DB::transaction(function () use ($team, $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'team_id' => $team->id,
            ]);

            // Set team context for Spatie
            setPermissionsTeamId($team->id);
            $user->assignRole($data['role']);

            return $user;
        });
    }

    public function removeMember(Team $team, User $user): void
    {
        if ($team->owner_id === $user->id) {
            throw new \Exception('Cannot remove the team owner.');
        }

        if ($user->team_id !== $team->id) {
            throw new \Exception('User does not belong to this team.');
        }

        // We could either delete the user or just unset the team_id
        // For this task, let's just remove them from the team
        $user->update(['team_id' => null]);
        $user->syncRoles([]);
        $user->tokens()->delete();
    }
}
