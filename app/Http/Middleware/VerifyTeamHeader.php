<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyTeamHeader
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $teamSlug = team_slug();

        if (!$teamSlug) {
            return response()->json([
                'success' => false,
                'code' => 422,
                'message' => 'The team-slug header is required.',
                'data' => null
            ], 422);
        }

        $team = current_team();

        if (!$team) {
            return response()->json([
                'success' => false,
                'code' => 404,
                'message' => 'Team not found.',
                'data' => null
            ], 404);
        }

        $user = $request->user();

        if ($user && $user->team_id !== $team->id) {
            return response()->json([
                'success' => false,
                'code' => 403,
                'message' => 'You do not have access to this team.',
                'data' => null
            ], 403);
        }

        return $next($request);
    }
}
