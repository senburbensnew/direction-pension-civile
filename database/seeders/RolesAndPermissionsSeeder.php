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

        $roles = array_values(array_unique(array_merge(
            config('dpc_roles.canonical', []),
            array_keys(config('dpc_roles.legacy_aliases', [])),
        )));

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web',]);
        }

        Role::where('name', 'pensionne')->first()->givePermissionTo([$canViewPensionneSection, $canViewPensionneMenu, $canViewDashboard]);
        Role::where('name', 'fonctionnaire')->first()->givePermissionTo([$canViewFonctionnaireSection, $canViewFonctionnaireMenu, $canViewDashboard]);
        Role::where('name', 'institution')->first()->givePermissionTo([$canViewInstitutionSection, $canViewInstitutionMenu, $canViewDashboard]);
    }
}
