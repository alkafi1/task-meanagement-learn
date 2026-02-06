<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync permissions and roles for the Super Admin module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permission sync...');

        // Guard names used in the application
        $guards = array_keys(config('permissions.guards', []));

        // Get config from central file
        $config = config('permissions.guards');

        foreach ($config as $guard => $data) {
            $this->info("Processing guard: {$guard}");

            // Create Permissions
            foreach ($data['permissions'] as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => $guard]);
            }

            // Create Roles and Sync Permissions
            foreach ($data['roles'] as $roleName => $permissions) {
                $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
                $role->syncPermissions($permissions);
                $this->line(" - Synced role: {$roleName} ({$guard})");
            }
        }

        // Clear Spatie Cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Permissions and roles synced successfully!');
    }
}
