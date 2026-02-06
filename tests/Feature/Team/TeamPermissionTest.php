<?php

namespace Tests\Feature\Team;

use App\Models\Team;
use App\Models\User;
use App\Models\SuperAdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
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
        Permission::create(['name' => 'view-tasks', 'guard_name' => 'team']);
        Permission::create(['name' => 'edit-tasks', 'guard_name' => 'team']);

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
        $roleA = Role::create(['name' => 'Editor', 'guard_name' => 'team', 'team_id' => $teamA->id]);
        $roleA->givePermissionTo('view-tasks');

        // Create a role for Team B with same name but different permissions
        setPermissionsTeamId(null);
        $roleB = Role::create(['name' => 'Editor', 'guard_name' => 'team', 'team_id' => $teamB->id]);
        $roleB->givePermissionTo('edit-tasks');

        // Create users for each team
        $userA = User::factory()->create(['team_id' => $teamA->id]);
        $userB = User::factory()->create(['team_id' => $teamB->id]);

        // Assign roles
        setPermissionsTeamId($teamA->id);
        $userA->assignRole($roleA);

        setPermissionsTeamId($teamB->id);
        $userB->assignRole($roleB);

        // Verify scoping in middleware-like context
        setPermissionsTeamId($teamA->id);
        $this->assertTrue($userA->hasPermissionTo('view-tasks', 'team'));
        $this->assertFalse($userA->hasPermissionTo('edit-tasks', 'team'));

        setPermissionsTeamId($teamB->id);
        $this->assertTrue($userB->hasPermissionTo('edit-tasks', 'team'));
        $this->assertFalse($userB->hasPermissionTo('view-tasks', 'team'));
    }

}
