<?php

namespace Database\Seeders;

use App\Models\SuperAdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Sync all permissions and roles from config/permissions.php
        $this->command->info('Syncing permissions and roles from configuration...');
        \Illuminate\Support\Facades\Artisan::call('permissions:sync');

        // 2. Create initial Super Admin User
        $superAdmin = SuperAdminUser::updateOrCreate(
            ['email' => 'admin@taskflow.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Assign Super Admin Role (using the guard specified in Spatie)
        if (!$superAdmin->hasRole('super-admin', 'super_admin')) {
            $superAdmin->assignRole('super-admin');
        }

        $this->command->info('Super Admin created and role assigned: admin@taskflow.com / password');
    }
}
