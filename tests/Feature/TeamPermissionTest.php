<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use App\Models\SuperAdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TeamPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create basic permissions
        Permission::create(['name' => 'view-tasks', 'guard_name' => 'web']);
        Permission::create(['name' => 'edit-tasks', 'guard_name' => 'web']);

        // Super admin guard permissions
        Permission::create(['name' => 'manage-users', 'guard_name' => 'super_admin']);
    }

    public function test_roles_are_scoped_to_teams()
    {
        $ownerA = User::factory()->create();
        $teamA = Team::create(['name' => 'Team A', 'slug' => 'team-a', 'owner_id' => $ownerA->id]);

        $ownerB = User::factory()->create();
        $teamB = Team::create(['name' => 'Team B', 'slug' => 'team-b', 'owner_id' => $ownerB->id]);
        // Create a role for Team A
        setPermissionsTeamId(null);
        $roleA = Role::create(['name' => 'Editor', 'guard_name' => 'web', 'team_id' => $teamA->id]);
        $roleA->givePermissionTo('view-tasks');

        // Create a role for Team B with same name but different permissions
        setPermissionsTeamId(null);
        $roleB = Role::create(['name' => 'Editor', 'guard_name' => 'web', 'team_id' => $teamB->id]);
        $roleB->givePermissionTo('edit-tasks');

        // Create users for each team
        $userA = User::factory()->create(['team_id' => $teamA->id]);
        $userB = User::factory()->create(['team_id' => $teamB->id]);

        // Assign roles
        setPermissionsTeamId($teamA->id);
        $userA->assignRole('Editor');

        setPermissionsTeamId($teamB->id);
        $userB->assignRole('Editor');

        // Verify scoping in middleware-like context
        setPermissionsTeamId($teamA->id);
        $this->assertTrue($userA->hasPermissionTo('view-tasks', 'web'));
        $this->assertFalse($userA->hasPermissionTo('edit-tasks', 'web'));

        setPermissionsTeamId($teamB->id);
        $this->assertTrue($userB->hasPermissionTo('edit-tasks', 'web'));
        $this->assertFalse($userB->hasPermissionTo('view-tasks', 'web'));
    }

    public function test_super_admin_roles_remain_global_via_gate()
    {
        $superAdmin = SuperAdminUser::create([
            'name' => 'Global Admin',
            'email' => 'admin@test.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $role = Role::create(['name' => 'super-admin', 'guard_name' => 'super_admin', 'team_id' => null]);
        $role->givePermissionTo('manage-users');

        $superAdmin->assignRole('super-admin');

        // Super Admin should have permission regardless of team ID because of Gate::before in AppServiceProvider
        setPermissionsTeamId(1); // Some team
        $this->assertTrue($superAdmin->can('manage-users'));

        setPermissionsTeamId(null);
        $this->assertTrue($superAdmin->can('manage-users'));
    }
}
