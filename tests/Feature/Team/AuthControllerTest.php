<?php

namespace Tests\Feature\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_user_can_login_to_team()
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
        ]);

        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'owner_id' => $owner->id,
        ]);

        $owner->update(['team_id' => $team->id]);

        $response = $this->withHeaders(['team-slug' => 'test-team'])
            ->postJson('/api/v1/team/login', [
                'email' => 'owner@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'roles', 'permissions'],
                    'team' => ['id', 'name', 'slug'],
                ],
            ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
        ]);

        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'owner_id' => $owner->id,
        ]);

        $owner->update(['team_id' => $team->id]);

        $response = $this->withHeaders(['team-slug' => 'test-team'])
            ->postJson('/api/v1/team/login', [
                'email' => 'owner@example.com',
                'password' => 'wrong-password',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_if_user_not_in_team()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'owner_id' => User::factory()->create()->id,
        ]);

        // User is NOT linked to team

        $response = $this->withHeaders(['team-slug' => 'test-team'])
            ->postJson('/api/v1/team/login', [
                'email' => 'user@example.com',
                'password' => 'password123',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_fails_if_team_slug_header_missing()
    {
        $response = $this->postJson('/api/v1/team/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'The team-slug header is required.']);
    }

    public function test_team_owner_gets_all_permissions()
    {
        $owner = User::factory()->create();
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'owner_id' => $owner->id,
        ]);
        $owner->update(['team_id' => $team->id]);

        // Create some permissions in 'team' guard
        Permission::create(['name' => 'list tasks', 'guard_name' => 'team']);
        Permission::create(['name' => 'delete task', 'guard_name' => 'team']);

        $token = $owner->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeaders(['team-slug' => $team->slug])
            ->getJson('/api/v1/team/permissions');

        $response->assertStatus(200);

        $permissions = $response->json('data.permissions');

        // Should have all web guard permissions defined in config
        $this->assertGreaterThan(0, count($permissions));

        // Check if codes are present
        foreach ($permissions as $p) {
            $this->assertArrayHasKey('name', $p);
            $this->assertArrayHasKey('code', $p);
        }
    }

    public function test_team_member_gets_only_assigned_permissions()
    {
        $owner = User::factory()->create();
        $team = Team::create(['name' => 'T1', 'slug' => 't1', 'owner_id' => $owner->id]);

        $user = User::factory()->create(['team_id' => $team->id]);

        Permission::create(['name' => 'view dashboard', 'guard_name' => 'team']);
        Permission::create(['name' => 'manage team', 'guard_name' => 'team']);

        $role = Role::create(['name' => 'Viewer', 'guard_name' => 'team', 'team_id' => $team->id]);
        $role->givePermissionTo('view dashboard');

        // Assign roles
        setPermissionsTeamId($team->id);
        $user->assignRole($role);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->withHeaders(['team-slug' => $team->slug])
            ->getJson('/api/v1/team/permissions');

        $response->assertStatus(200);

        $permissions = $response->json('data.permissions');

        $this->assertCount(1, $permissions);
        $this->assertEquals('view dashboard', $permissions[0]['name']);
        $this->assertNotNull($permissions[0]['code']);
    }
}
