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
        $permissions = [
            'CAN_VIEW_PENSIONNE_SECTION' => ['viewPensionneSection', 'viewPensionnaireSection'],
            'CAN_VIEW_FONCTIONNAIRE_SECTION' => ['viewFonctionnaireSection'],
            'CAN_VIEW_INSTITUTION_SECTION' => ['viewInstitutionSection'],
            'CAN_VIEW_PENSIONNE_MENU' => ['viewPensionneMenu', 'viewPensionnaireMenu'],
            'CAN_VIEW_FONCTIONNAIRE_MENU' => ['viewFonctionnaireMenu'],
            'CAN_VIEW_INSTITUTION_MENU' => ['viewInstitutionMenu'],
            'CAN_VIEW_DASHBOARD' => ['viewDashboard'],
        ];

        foreach ($permissions as $name => $legacyNames) {
            $permission = Permission::where('name', $name)->first();

            foreach ($legacyNames as $legacyName) {
                $legacy = Permission::where('name', $legacyName)->first();

                if (! $legacy) {
                    continue;
                }

                if ($permission) {
                    $legacy->delete();
                } else {
                    $legacy->update(['name' => $name]);
                    $permission = $legacy;
                }
            }

            if (! $permission) {
                Permission::create(['name' => $name, 'guard_name' => 'web']);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $canViewPensionneSection = Permission::where('name', 'CAN_VIEW_PENSIONNE_SECTION')->first();
        $canViewFonctionnaireSection = Permission::where('name', 'CAN_VIEW_FONCTIONNAIRE_SECTION')->first();
        $canViewInstitutionSection = Permission::where('name', 'CAN_VIEW_INSTITUTION_SECTION')->first();
        $canViewPensionneMenu = Permission::where('name', 'CAN_VIEW_PENSIONNE_MENU')->first();
        $canViewFonctionnaireMenu = Permission::where('name', 'CAN_VIEW_FONCTIONNAIRE_MENU')->first();
        $canViewInstitutionMenu = Permission::where('name', 'CAN_VIEW_INSTITUTION_MENU')->first();
        $canViewDashboard = Permission::where('name', 'CAN_VIEW_DASHBOARD')->first();

        $roles = [
            'admin',
            'pensionne',
            'fonctionnaire',
            'institution',
            'direction',
            'directeur',
            'assistant_directeur',
            'secretariat',
            'service_liquidation',
            'service_accueil_formalites',
            'service_controle_placement',
            'service_comptabilite',
            'service_assurance',
            'administration',
            'responsable_service_accueil_formalites',
            'responsable_service_controle_placement',
            'responsable_service_comptabilite',
            'responsable_service_assurance',
            'responsable_administration',
            'responsable_direction',
            'responsable_directeur',
            'responsable_assistant_directeur',
            'responsable_secretariat',
            User::ROLE_AGENT_RDV,
            User::ROLE_VALIDATEUR_RDV,
            User::ROLE_AGENT_FORMALITES,
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web',]);
        }

        Role::where('name', 'pensionne')->first()->givePermissionTo([$canViewPensionneSection, $canViewPensionneMenu, $canViewDashboard]);
        Role::where('name', 'fonctionnaire')->first()->givePermissionTo([$canViewFonctionnaireSection, $canViewFonctionnaireMenu, $canViewDashboard]);
        Role::where('name', 'institution')->first()->givePermissionTo([$canViewInstitutionSection, $canViewInstitutionMenu, $canViewDashboard]);
    }
}
