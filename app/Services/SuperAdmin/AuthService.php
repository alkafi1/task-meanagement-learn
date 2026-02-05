<?php

namespace App\Services\SuperAdmin;

use App\Models\SuperAdminUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate a super admin and return a token.
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (Auth::guard('sanctum')->check()) {
            // dd(1);
            throw ValidationException::withMessages([
                'email' => [__('auth.already_authenticated')],
            ]);
        }

        $superAdmin = SuperAdminUser::where('email', $credentials['email'])->first();

        if (! $superAdmin || ! Hash::check($credentials['password'], $superAdmin->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $superAdmin->createToken('super-admin-token')->plainTextToken;

        return [
            'super_admin' => $superAdmin,
            'token' => $token,
        ];
    }

    /**
     * Logout a super admin by revoking all tokens.
     *
     * @param SuperAdminUser $superAdmin
     * @return void
     */
    public function logout(SuperAdminUser $superAdmin): void
    {
        $superAdmin->tokens()->delete();
    }
}
