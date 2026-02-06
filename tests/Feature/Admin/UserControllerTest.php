<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: Authenticated user can get their current profile
     */
    public function test_authenticated_user_can_get_current_profile()
    {
        // Create a user
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Act as authenticated user and get profile
        $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/admin/user');

        // Assert response structure and data
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'avatar',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'code' => 200,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => 'John Doe',
                        'email' => 'john@example.com',
                    ],
                ],
            ]);
    }

    /**
     * Test: Unauthenticated user cannot get profile
     */
    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/v1/admin/user');

        $response->assertStatus(401);
    }

    /**
     * Test: User can update profile with name and email
     */
    public function test_user_can_update_profile_with_name_and_email()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => 200,
                'data' => [
                    'user' => [
                        'name' => 'New Name',
                        'email' => 'new@example.com',
                    ],
                ],
            ]);

        // Verify database was updated
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test: User can update profile with avatar
     */
    public function test_user_can_update_profile_with_avatar()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $avatar = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => ['avatar'],
                ],
            ]);

        // Verify avatar was stored
        $avatarPath = $response->json('data.user.avatar');
        Storage::disk('public')->assertExists($avatarPath);
    }

    /**
     * Test: User can update avatar and old avatar is deleted
     */
    public function test_updating_avatar_deletes_old_avatar()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar' => 'avatars/old-avatar.jpg',
        ]);

        // Create the old avatar file
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'old content');

        $newAvatar = UploadedFile::fake()->image('new-avatar.jpg');

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $newAvatar,
        ]);

        $response->assertStatus(200);

        // Old avatar should be deleted
        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');

        // New avatar should exist
        $newAvatarPath = $response->json('data.user.avatar');
        Storage::disk('public')->assertExists($newAvatarPath);
    }

    /**
     * Test: Profile update requires name
     */
    public function test_profile_update_requires_name()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /**
     * Test: Profile update requires email
     */
    public function test_profile_update_requires_email()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => 'Test Name',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test: Email must be unique except for current user
     */
    public function test_email_must_be_unique_except_current_user()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // User2 trying to use User1's email should fail
        $response = $this->actingAs($user2, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => 'User 2',
            'email' => 'user1@example.com',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // User2 can keep their own email
        $response = $this->actingAs($user2, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => 'User 2 Updated',
            'email' => 'user2@example.com',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test: Avatar must be an image
     */
    public function test_avatar_must_be_image()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $file,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Test: Avatar size limit is enforced
     */
    public function test_avatar_size_limit_enforced()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        // Create file larger than 2MB (2048 KB)
        $largeFile = UploadedFile::fake()->image('large.jpg')->size(3000);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $largeFile,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['avatar']);
    }

    /**
     * Test: User can change password with valid current password
     */
    public function test_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => 200,
            ]);

        // Verify password was changed
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /**
     * Test: Password change fails with incorrect current password
     */
    public function test_password_change_fails_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('correctpassword'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);

        // Password should not be changed
        $this->assertTrue(Hash::check('correctpassword', $user->fresh()->password));
    }

    /**
     * Test: Password change requires confirmation
     */
    public function test_password_change_requires_confirmation()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /**
     * Test: New password must meet minimum length requirement
     */
    public function test_new_password_must_meet_minimum_length()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/v1/admin/user/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /**
     * Test: Unauthenticated user cannot change password
     */
    public function test_unauthenticated_user_cannot_change_password()
    {
        $response = $this->putJson('/api/v1/admin/user/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test: Multi-tenancy - User can only access their own profile
     */
    public function test_user_can_only_access_own_profile()
    {
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        // User1 should only see their own data
        $response = $this->actingAs($user1, 'sanctum')->getJson('/api/v1/admin/user');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'user' => [
                        'id' => $user1->id,
                        'name' => 'User 1',
                    ],
                ],
            ])
            ->assertJsonMissing([
                'name' => 'User 2',
            ]);
    }

    /**
     * Test: Profile update only affects authenticated user
     */
    public function test_profile_update_only_affects_authenticated_user()
    {
        $user1 = User::factory()->create(['name' => 'User 1']);
        $user2 = User::factory()->create(['name' => 'User 2']);

        // User1 updates their profile
        $response = $this->actingAs($user1, 'sanctum')->putJson('/api/v1/admin/user', [
            'name' => 'Updated User 1',
            'email' => $user1->email,
        ]);

        $response->assertStatus(200);

        // Verify User1 was updated
        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'name' => 'Updated User 1',
        ]);

        // Verify User2 was NOT affected
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'name' => 'User 2',
        ]);
    }
}
