<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use App\Models\UserType;

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

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $pensionnaireRole = Role::firstOrCreate(['name' => 'pensionnaire']);
        $fonctionnaireRole = Role::firstOrCreate(['name' => 'fonctionnaire']);
        $institutionRole = Role::firstOrCreate(['name' => 'institution']);

        // Check if the user type exists
        $userType = UserType::firstOrCreate(
            ['name' => 'fonctionnaire'], // Look for the 'fonctionnaire' user type
            ['name' => 'fonctionnaire']  // If it doesn't exist, create it with this value
        );

        // Find the user by email or create it if it doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'], 
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'),
                'nif' => '1234567890',
                'user_type_id' => $userType->id, // Use the user_type_id from the found or created user type
            ]
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