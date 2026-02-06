<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class SetPermissionsTeamId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user instanceof User && $user->team_id) {
                setPermissionsTeamId($user->team_id);
            } else {
                // For SuperAdminUser or users without a team, unset the team ID
                setPermissionsTeamId(null);
            }
        }

        return $next($request);
    }
}
