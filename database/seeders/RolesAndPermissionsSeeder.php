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

/*      $permissions = [
            'CAN_VIEW_PENSIONNAIRE_SECTION',
            'CAN_VIEW_FONCTIONNAIRE_SECTION',
            'CAN_VIEW_INSTITUTION_SECTION',
            'CAN_VIEW_PENSIONNAIRE_MENU',
            'CAN_VIEW_FONCTIONNAIRE_MENU',
            'CAN_VIEW_INSTITUTION_MENU',
            'CAN_VIEW_DASHBOARD',
        ]; */

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

/*      $canViewPensionnaireSection   = Permission::where('name', 'CAN_VIEW_PENSIONNAIRE_SECTION')->first();
        $canViewFonctionnaireSection = Permission::where('name', 'CAN_VIEW_FONCTIONNAIRE_SECTION')->first();
        $canViewInstitutionSection   = Permission::where('name', 'CAN_VIEW_INSTITUTION_SECTION')->first();
        $canViewPensionnaireMenu     = Permission::where('name', 'CAN_VIEW_PENSIONNAIRE_MENU')->first();
        $canViewFonctionnaireMenu    = Permission::where('name', 'CAN_VIEW_FONCTIONNAIRE_MENU')->first();
        $canViewInstitutionMenu      = Permission::where('name', 'CAN_VIEW_INSTITUTION_MENU')->first();
        $canViewDashboard            = Permission::where('name', 'CAN_VIEW_DASHBOARD')->first(); */


        // Create roles
        $roles = [
            'admin',
            'pensionnaire',
            'fonctionnaire',
            'institution',
            'direction',
            'secretariat',
            'service_liquidation',
            'service_formalite',
            'service_controle_placement',
            'service_comptabilite',
            'service_assurance',
            'administration',
        ];

/*         $roles = [
            'ROLE_SUPERADMIN',
            'ROLE_PENSIONNAIRE',
            'ROLE_FONCTIONNAIRE',
            'ROLE_INSTITUTION',
            'ROLE_DIRECTION',
            'ROLE_SECRETARIAT',

            'ROLE_SERVICE_LIQUIDATION',
            'ROLE_SERVICE_FORMALITE',
            'ROLE_SERVICE_CONTROLE_PLACEMENT',
            'ROLE_SERVICE_COMPTABILITE',
            'ROLE_SERVICE_ASSURANCE',

            'ROLE_ADMINISTRATION',
        ]; */


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