<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class TeamAuthController extends Controller
{
    /**
     * Team login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'team_slug' => ['required', 'string', 'exists:teams,slug'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $team = Team::where('slug', $request->team_slug)->firstOrFail();

        $user = User::where('email', $request->email)
            ->where('team_id', $team->id)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken('team-access-token')->plainTextToken;

        return ApiResponse::success(200, __('messages.login_success'), [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
            ],
        ]);
    }

    /**
     * Team logout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success(200, __('messages.logout_success'));
    }
}
