<?php

namespace Database\Seeders;

use App\Models\SuperAdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SuperAdminUser::updateOrCreate(
            ['email' => 'admin@taskflow.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ]
        );

        $this->command->info('Super Admin created: admin@taskflow.com / password');
    }
}
