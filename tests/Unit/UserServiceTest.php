<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    /**
     * Test: updateProfile updates user name and email
     */
    public function test_update_profile_updates_name_and_email()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $updatedUser = $this->userService->updateProfile($user, [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    /**
     * Test: updateProfile handles avatar upload
     */
    public function test_update_profile_handles_avatar_upload()
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $updatedUser = $this->userService->updateProfile($user, [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        $this->assertNotNull($updatedUser->avatar);
        $this->assertStringStartsWith('avatars/', $updatedUser->avatar);
        Storage::disk('public')->assertExists($updatedUser->avatar);
    }

    /**
     * Test: updateProfile deletes old avatar when uploading new one
     */
    public function test_update_profile_deletes_old_avatar()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar' => 'avatars/old-avatar.jpg',
        ]);

        // Create old avatar file
        Storage::disk('public')->put('avatars/old-avatar.jpg', 'old content');
        $this->assertTrue(Storage::disk('public')->exists('avatars/old-avatar.jpg'));

        $newAvatar = UploadedFile::fake()->image('new-avatar.jpg');

        $updatedUser = $this->userService->updateProfile($user, [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $newAvatar,
        ]);

        // Old avatar should be deleted
        Storage::disk('public')->assertMissing('avatars/old-avatar.jpg');

        // New avatar should exist
        Storage::disk('public')->assertExists($updatedUser->avatar);
        $this->assertNotEquals('avatars/old-avatar.jpg', $updatedUser->avatar);
    }

    /**
     * Test: updateProfile without avatar keeps existing avatar
     */
    public function test_update_profile_without_avatar_keeps_existing()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar' => 'avatars/existing-avatar.jpg',
        ]);

        Storage::disk('public')->put('avatars/existing-avatar.jpg', 'content');

        $updatedUser = $this->userService->updateProfile($user, [
            'name' => 'Updated Name',
            'email' => $user->email,
        ]);

        // Avatar should remain unchanged
        $this->assertEquals('avatars/existing-avatar.jpg', $updatedUser->avatar);
        Storage::disk('public')->assertExists('avatars/existing-avatar.jpg');
    }

    /**
     * Test: updateProfile returns fresh user model
     */
    public function test_update_profile_returns_fresh_model()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $updatedUser = $this->userService->updateProfile($user, [
            'name' => 'New Name',
            'email' => $user->email,
        ]);

        // Should be a fresh instance with updated data
        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertNotSame($user, $updatedUser);
    }

    /**
     * Test: changePassword updates password correctly
     */
    public function test_change_password_updates_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $this->userService->changePassword($user, 'newpassword123');

        // Verify new password works
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));

        // Verify old password no longer works
        $this->assertFalse(Hash::check('oldpassword', $user->fresh()->password));
    }

    /**
     * Test: changePassword hashes the password
     */
    public function test_change_password_hashes_password()
    {
        $user = User::factory()->create();

        $plainPassword = 'myplainpassword123';

        $this->userService->changePassword($user, $plainPassword);

        $freshUser = $user->fresh();

        // Password should be hashed, not plain
        $this->assertNotEquals($plainPassword, $freshUser->password);

        // But should verify correctly
        $this->assertTrue(Hash::check($plainPassword, $freshUser->password));
    }

    /**
     * Test: changePassword persists to database
     */
    public function test_change_password_persists_to_database()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $oldPasswordHash = $user->password;

        $this->userService->changePassword($user, 'newpassword123');

        // Get fresh instance from database
        $userFromDb = User::find($user->id);

        // Password hash should be different
        $this->assertNotEquals($oldPasswordHash, $userFromDb->password);

        // New password should work
        $this->assertTrue(Hash::check('newpassword123', $userFromDb->password));
    }

    /**
     * Test: updateProfile handles multiple attributes at once
     */
    public function test_update_profile_handles_multiple_attributes()
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $updatedUser = $this->userService->updateProfile($user, [
            'name' => 'New Name',
            'email' => 'new@example.com',
            'avatar' => $avatar,
        ]);

        $this->assertEquals('New Name', $updatedUser->name);
        $this->assertEquals('new@example.com', $updatedUser->email);
        $this->assertNotNull($updatedUser->avatar);
        Storage::disk('public')->assertExists($updatedUser->avatar);
    }
}
