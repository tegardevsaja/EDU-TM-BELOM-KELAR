<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class FixPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create missing permissions
        $permissions = [
            'nilai.import',
            'nilai.create', 
            'nilai.view',
            'nilai.update',
            'nilai.delete',
            'menu.nilai'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
            $this->command->info("Permission created/found: {$permission}");
        }

        // Get master_admin role
        $masterRole = Role::where('name', 'master_admin')->first();
        
        if ($masterRole) {
            // Give all permissions to master_admin
            $allPermissions = Permission::all();
            $masterRole->syncPermissions($allPermissions);
            $this->command->info("All permissions assigned to master_admin role");
            
            // Assign role to all master_admin users
            $masterUsers = User::where('role', 'master_admin')->get();
            foreach ($masterUsers as $user) {
                $user->assignRole($masterRole);
                $this->command->info("Role assigned to user: {$user->email}");
            }
        }

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $this->command->info("Permission cache cleared");
    }
}
