<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\SuperAdminUser;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ManagementTest extends TestCase
{
    use RefreshDatabase;

    protected SuperAdminUser $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->superAdmin = SuperAdminUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        // Create the core 'super-admin' role which has permission to manage things
        $role = Role::create(['name' => 'super-admin', 'guard_name' => 'super_admin']);

        // Let's create some permissions that match our config/permissions.php
        Permission::create(['name' => 'create role', 'guard_name' => 'super_admin']);
        Permission::create(['name' => 'create team', 'guard_name' => 'super_admin']);
        Permission::create(['name' => 'view permissions', 'guard_name' => 'super_admin']);

        $role->givePermissionTo(['create role', 'create team', 'view permissions']);
        $this->superAdmin->assignRole($role);
    }

    public function test_super_admin_can_list_permissions()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->getJson('/api/v1/super-admin/permissions');

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

    public function test_super_admin_can_create_global_role()
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/super-admin/roles', [
                'name' => 'New Global Role',
                'permissions' => ['create role']
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', [
            'name' => 'New Global Role',
            'guard_name' => 'super_admin',
            'team_id' => null
        ]);
    }

    public function test_super_admin_can_create_team_role()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'owner_id' => User::factory()->create()->id
        ]);

        // Create a team-scoped permission first
        Permission::create(['name' => 'view dashboard', 'guard_name' => 'team']);

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/super-admin/roles', [
                'name' => 'Team Editor',
                'team_id' => $team->id,
                'permissions' => ['view dashboard']
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('roles', [
            'name' => 'Team Editor',
            'guard_name' => 'team',
            'team_id' => $team->id
        ]);
    }

    public function test_super_admin_can_create_team_with_owner()
    {
        $data = [
            'name' => 'New SaaS Team',
            'slug' => 'new-saas-team',
            'owner_name' => 'Owner Name',
            'owner_email' => 'owner@saas.com',
            'owner_password' => 'password123'
        ];

        $response = $this->actingAs($this->superAdmin, 'sanctum')
            ->postJson('/api/v1/super-admin/teams', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('teams', [
            'name' => 'New SaaS Team',
            'slug' => 'new-saas-team'
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Owner Name',
            'email' => 'owner@saas.com'
        ]);

        $team = Team::where('slug', 'new-saas-team')->first();
        $user = User::where('email', 'owner@saas.com')->first();

        $this->assertEquals($team->owner_id, $user->id);
        $this->assertEquals($user->team_id, $team->id);
    }
}
