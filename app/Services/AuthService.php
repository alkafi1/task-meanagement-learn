<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param  array  $data
     * @return array  ['user' => User, 'token' => string]
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Attempt to log in a user.
     *
     * @param  array  $credentials
     * @return array  ['user' => User, 'token' => string]
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (Auth::guard('sanctum')->check()) {
            throw ValidationException::withMessages([
                'email' => [__('auth.already_authenticated')],
            ]);
        }

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Log out the user (revoke tokens).
     *
     * @param  User  $user
     * @return void
     */
    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * Change user password.
     *
     * @param  User  $user
     * @param  string  $newPassword
     * @return void
     */
    public function changePassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
