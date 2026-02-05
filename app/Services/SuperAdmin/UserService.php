<?php

namespace App\Services\SuperAdmin;

use App\Models\SuperAdminUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Get all users across tenants.
     *
     * @return Collection
     */
    public function getAllUsers(): Collection
    {
        return SuperAdminUser::all();
    }

    /**
     * Create a new user.
     *
     * @param array $data
     * @return User
     */
    public function createUser(array $data): User
    {
        return SuperAdminUser::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * Update an existing user.
     *
     * @param SuperAdminUser $user
     * @param array $data
     * @return SuperAdminUser
     */
    public function updateUser(SuperAdminUser $user, array $data): SuperAdminUser
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user->fresh();
    }

    /**
     * Delete a user.
     *
     * @param User $user
     * @return void
     */
    public function deleteUser(SuperAdminUser $user): void
    {
        $user->delete();
    }
}
