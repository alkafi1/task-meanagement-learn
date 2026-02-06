<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SuperAdminUser;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! ($request->user() instanceof SuperAdminUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Super Admin access only.',
            ], 401);
        }

        return $next($request);
    }
}
