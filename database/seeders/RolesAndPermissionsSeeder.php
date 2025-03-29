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
        // Create permissions using firstOrCreate to avoid duplicates
        $permissions = [
            'viewPensionnaireSection',
            'viewFonctionnaireSection',
            'viewInstitutionSection',
            'viewPensionnaireMenu',
            'viewFonctionnaireMenu',
            'viewInstitutionMenu',
            'viewDashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Fetch permissions
        $viewPensionnaireSection = Permission::where('name', 'viewPensionnaireSection')->first();
        $viewFonctionnaireSection = Permission::where('name', 'viewFonctionnaireSection')->first();
        $viewInstitutionSection = Permission::where('name', 'viewInstitutionSection')->first();
        $viewPensionnaireMenu = Permission::where('name', 'viewPensionnaireMenu')->first();
        $viewFonctionnaireMenu = Permission::where('name', 'viewFonctionnaireMenu')->first();
        $viewInstitutionMenu = Permission::where('name', 'viewInstitutionMenu')->first();
        $viewDashboard = Permission::where('name', 'viewDashboard')->first();

        // Create roles
        $roles = [
            'admin',
            'pensionnaire',
            'fonctionnaire',
            'institution',
            'liquidation',
            'service_formalite',
            'assurance',
            'comptabilite',
            'secretariat',
            'administration',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Assign permissions to roles
        Role::where('name', 'pensionnaire')->first()->givePermissionTo([$viewPensionnaireSection, $viewPensionnaireMenu, $viewDashboard]);
        Role::where('name', 'fonctionnaire')->first()->givePermissionTo([$viewFonctionnaireSection, $viewFonctionnaireMenu, $viewDashboard]);
        Role::where('name', 'institution')->first()->givePermissionTo([$viewInstitutionSection, $viewInstitutionMenu, $viewDashboard]);

        // Check if the user type exists
        $userType = UserType::firstOrCreate(['name' => 'fonctionnaire']);

        // Create or update the admin user
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
                'nif' => '1234567890',
                'user_type_id' => $userType->id,
            ]
        );

        // Assign roles to the admin user
        $adminUser->assignRole(['admin', 'fonctionnaire']);
    }
}