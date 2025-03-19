<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Create permissions
        // $permission = Permission::create(['name' => 'edit articles']);
        // $permission2 = Permission::create(['name' => 'delete articles']);
        // $permission3 = Permission::create(['name' => 'publish articles']);

        // Create roles and assign permissions
        // $role = Role::create(['name' => 'editor']);
        // $role->givePermissionTo($permission);
        // $role->givePermissionTo($permission2);

        // Ensure the 'admin' role exists (create it if necessary)
        $adminRole = Role::firstOrCreate(['name' => 'admin']);

        // Find the user by email or create it if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'], 
            ['name' => 'Rubens', 'password' => bcrypt('password123')] // Customize fields as needed
        );

        // Assign the 'admin' role to the user
        $user->assignRole($adminRole);
        
        // $adminRole->givePermissionTo($permission);
        // $adminRole->givePermissionTo($permission2);
        // $adminRole->givePermissionTo($permission3);

        // You can also assign permissions to multiple roles at once
        // $role->syncPermissions([$permission, $permission2, $permission3]);


    }
}