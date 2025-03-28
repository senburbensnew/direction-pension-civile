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
        // Create permissions  ex : $permission = Permission::create(['name' => 'edit articles']);
        $viewPensionnaireSection = Permission::create(['name' => 'viewPensionnaireSection']);
        $viewFonctionnaireSection = Permission::create(['name' => 'viewFonctionnaireSection']);
        $viewInstitutionSection = Permission::create(['name' => 'viewInstitutionSection']);
        $viewPensionnaireMenu = Permission::create(['name' => 'viewPensionnaireMenu']);
        $viewFonctionnaireMenu = Permission::create(['name' => 'viewFonctionnaireMenu']);
        $viewInstitutionMenu = Permission::create(['name' => 'viewInstitutionMenu']);

        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $pensionnaireRole = Role::firstOrCreate(['name' => 'pensionnaire']);
        $fonctionnaireRole = Role::firstOrCreate(['name' => 'fonctionnaire']);
        $institutionRole = Role::firstOrCreate(['name' => 'institution']);
        $liquidationRole = Role::firstOrCreate(['name' => 'liquidation']);
        $serviceFormaliteRole = Role::firstOrCreate(['name' => 'service_formalite']);
        $assuranceRole = Role::firstOrCreate(['name' => 'assurance']);
        $comptabiliteRole = Role::firstOrCreate(['name' => 'comptabilite']);
        $secretariatRole = Role::firstOrCreate(['name' => 'secretariat']);
        $administrationRole = Role::firstOrCreate(['name' => 'administration']);

        // Assign permissions to roles
        $pensionnaireRole->givePermissionTo($viewPensionnaireSection);    
        $pensionnaireRole->givePermissionTo($viewPensionnaireMenu);     
        $fonctionnaireRole->givePermissionTo($viewFonctionnaireSection); 
        $fonctionnaireRole->givePermissionTo($viewFonctionnaireMenu); 
        $institutionRole->givePermissionTo($viewInstitutionSection); 
        $institutionRole->givePermissionTo($viewInstitutionMenu); 

        // You can also assign permissions to multiple roles at once
        // $role->syncPermissions([$permission, $permission2, $permission3]);

        // Check if the user type exists
        $userType = UserType::firstOrCreate(
            ['name' => 'fonctionnaire'], // Look for the 'fonctionnaire' user type
            ['name' => 'fonctionnaire']  // If it doesn't exist, create it with this value
        );

        // Find the user by email or create it if it doesn't exist
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'], 
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password123'),
                'nif' => '1234567890',
                'user_type_id' => $userType->id, // Use the user_type_id from the found or created user type
            ]
        );

        // Assign roles role to the user
        $adminUser->assignRole($adminRole);
        $adminUser->assignRole($fonctionnaireRole);
    }
}