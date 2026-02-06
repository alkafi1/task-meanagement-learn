<?php

namespace Tests\Feature\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Team $team;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->owner = User::factory()->create();
        $this->team = Team::create([
            'name' => 'Owner Team',
            'slug' => 'owner-team',
            'owner_id' => $this->owner->id,
        ]);
        $this->owner->update(['team_id' => $this->team->id]);

        // Create team-guarded permissions
        Permission::create(['name' => 'view dashboard', 'guard_name' => 'team']);
        Permission::create(['name' => 'manage team', 'guard_name' => 'team']);
    }

    public function test_team_owner_can_list_permissions()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson('/api/v1/team/roles/permissions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => [
                    '*' => ['name', 'code', 'group']
                ]
            ]);

        $this->assertGreaterThan(0, count($response->json('data')));
    }

    public function test_team_owner_can_create_role()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/team/roles', [
                'name' => 'Developer',
                'permissions' => ['view dashboard']
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', [
            'name' => 'Developer',
            'guard_name' => 'team',
            'team_id' => $this->team->id
        ]);
    }

    public function test_team_member_cannot_create_role()
    {
        $member = User::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($member, 'sanctum')
            ->postJson('/api/v1/team/roles', [
                'name' => 'Hacker'
            ]);

        $response->assertStatus(403);
    }

    public function test_team_owner_can_add_member_with_role()
    {
        $role = Role::create([
            'name' => 'Project Manager',
            'guard_name' => 'team',
            'team_id' => $this->team->id
        ]);

        $data = [
            'name' => 'New Member',
            'email' => 'member@example.com',
            'password' => 'password123',
            'role' => 'Project Manager'
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/team/members', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'name' => 'New Member',
            'email' => 'member@example.com',
            'team_id' => $this->team->id
        ]);

        $member = User::where('email', 'member@example.com')->first();

        setPermissionsTeamId($this->team->id);
        $this->assertTrue($member->hasRole('Project Manager', 'team'));
    }

    public function test_team_owner_can_remove_member()
    {
        $member = User::factory()->create(['team_id' => $this->team->id]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/team/members/{$member->id}");

        $response->assertStatus(200);
        $this->assertNull($member->fresh()->team_id);
    }

    public function test_cannot_remove_team_owner()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/team/members/{$this->owner->id}");

        $response->assertStatus(403);
        $this->assertEquals($this->team->id, $this->owner->fresh()->team_id);
    }
}
