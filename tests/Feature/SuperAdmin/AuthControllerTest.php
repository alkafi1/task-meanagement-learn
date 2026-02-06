<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\SuperAdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Reset Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'manage-super_admins', 'guard_name' => 'super_admin']);
    }

    public function test_super_admin_can_login()
    {
        $superAdmin = SuperAdminUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);

        // Ensure it's hashed manually in service if using service,
        // but here we are just testing the login endpoint.
        // SuperAdminAuthService uses Hash::make.

        $response = $this->postJson('/api/v1/super-admin/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'code',
                'message',
                'data' => [
                    'token',
                    'super_admin',
                ],
            ]);
    }

    public function test_super_admin_roles_remain_global_via_gate()
    {
        $superAdmin = SuperAdminUser::create([
            'name' => 'Global Admin',
            'email' => 'global@test.com',
            'password' => Hash::make('password'),
        ]);

        $role = Role::create(['name' => 'super-admin', 'guard_name' => 'super_admin', 'team_id' => null]);
        $role->givePermissionTo('manage-super_admins');

        $superAdmin->assignRole('super-admin');

        // Super Admin should have permission regardless of team ID because of Gate::before in AppServiceProvider
        setPermissionsTeamId(1); // Some team
        $this->assertTrue($superAdmin->can('manage-super_admins'));

        setPermissionsTeamId(null);
        $this->assertTrue($superAdmin->can('manage-super_admins'));
    }
}
