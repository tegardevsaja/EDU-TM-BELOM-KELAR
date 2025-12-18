<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create master admin user
        $user = User::create([
            'name' => 'Master Admin',
            'email' => 'master@admin.com',
            'password' => Hash::make('password'),
            'role' => 'master_admin',
            'email_verified_at' => now(),
        ]);

        // Assign role
        $role = Role::where('name', 'master_admin')->first();
        if ($role) {
            $user->assignRole($role);
        }

        $this->command->info('Master admin user created: ' . $user->email);
    }
}
