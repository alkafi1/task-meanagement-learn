<?php

namespace App\Services\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeamAuthService
{
    /**
     * Authenticate a user within a team.
     *
     * @param array $data
     * @param string $teamSlug
     * @return array
     * @throws ValidationException
     */
    public function login(array $data, string $teamSlug): array
    {
        $team = Team::where('slug', $teamSlug)->first();

        if (!$team) {
            throw ValidationException::withMessages([
                'team' => ['Invalid team identifier.'],
            ]);
        }

        // Check if user is already authenticated
        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            if ($user instanceof User && $user->team_id) {
                $existingTeam = Team::find($user->team_id);
                if ($existingTeam && $existingTeam->slug === $teamSlug && $user->email === $data['email']) {
                    throw ValidationException::withMessages([
                        'email' => [__('auth.already_authenticated')],
                    ]);
                }
            }
        }

        $user = User::where('email', $data['email'])
            ->where('team_id', $team->id)
            ->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('team-access-token')->plainTextToken;

        return $this->formatResponse($user, $team, $token);
    }

    /**
     * Get permissions for the authenticated user.
     *
     * @param User $user
     * @return array
     */
    public function getPermissions(User $user): array
    {
        $team = Team::find($user->team_id);

        if (!$team) {
            return [];
        }

        return $this->getUserPermissionsWithCodes($user, $team);
    }

    /**
     * Format the login response.
     */
    protected function formatResponse(User $user, Team $team, string $token): array
    {
        return [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $this->getUserPermissionsWithCodes($user, $team),
            ],
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
            ],
        ];
    }

    /**
     * Get user permissions with numeric codes for a specific team.
     */
    protected function getUserPermissionsWithCodes(User $user, Team $team): array
    {
        $permissionConfig = config('permissions.codes', []);

        $originalTeamId = getPermissionsTeamId();
        setPermissionsTeamId($team->id);

        // Grant all team permissions if user is the team owner OR has team-admin role
        if ($user->id === $team->owner_id || $user->hasRole('team-admin', 'team')) {
            $webPermissions = config('permissions.guards.team.permissions', []);
            $permissions = collect($webPermissions)->map(function ($name) use ($permissionConfig) {
                return [
                    'name' => $name,
                    'code' => $permissionConfig[$name]['code'] ?? null,
                ];
            })->toArray();
        } else {
            $permissions = $user->getAllPermissions()->map(function ($permission) use ($permissionConfig) {
                return [
                    'name' => $permission->name,
                    'code' => $permissionConfig[$permission->name]['code'] ?? null,
                ];
            })->toArray();
        }

        setPermissionsTeamId($originalTeamId);

        return $permissions;
    }
}
