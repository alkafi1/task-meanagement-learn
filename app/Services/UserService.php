<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * Update user profile information.
     *
     * Updates name, email, and optionally avatar for the authenticated user.
     * Handles file upload for avatar and stores it in the public storage.
     *
     * @param  User  $user  The authenticated user
     * @param  array  $data  Validated data from UpdateProfileRequest
     * @return User  Updated user model
     */
    public function updateProfile(User $user, array $data): User
    {
        // Handle avatar upload if provided
        if (isset($data['avatar']) && $data['avatar']) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar in public/avatars directory
            $avatarPath = $data['avatar']->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        // Update user with validated data
        $user->update($data);

        // Refresh the model to get updated values
        return $user->fresh();
    }

    /**
     * Change user password.
     *
     * Updates the password for the authenticated user.
     * The current password validation is handled by ChangePasswordRequest.
     *
     * @param  User  $user  The authenticated user
     * @param  string  $newPassword  The new password (will be hashed)
     * @return void
     */
    public function changePassword(User $user, string $newPassword): void
    {
        // Hash and update the password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
