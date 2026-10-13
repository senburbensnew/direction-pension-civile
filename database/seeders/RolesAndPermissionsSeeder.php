<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const GUARD = 'web';

    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
        $this->syncRolePermissions();
    }

    /**
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return [
            'CAN_VIEW_PENSIONNE_SECTION',
            'CAN_VIEW_FONCTIONNAIRE_SECTION',
            'CAN_VIEW_INSTITUTION_SECTION',
            'CAN_VIEW_PENSIONNE_MENU',
            'CAN_VIEW_FONCTIONNAIRE_MENU',
            'CAN_VIEW_INSTITUTION_MENU',
            'CAN_VIEW_DASHBOARD',
        ];
    }

    /**
     * @return list<string>
     */
    public static function allPermissionNames(): array
    {
        return self::permissionNames();
    }

    /**
     * @return list<string>
     */
    public static function canonicalRoles(): array
    {
        return [
            'admin',
            'agent_formalites',
            'agent_rdv',
            'assistant_directeur',
            'directeur',
            'fonctionnaire',
            'institution',
            'pensionne',
            'responsable_service_accueil_formalites',
            'validateur_rdv',
        ];
    }

    /**
     * @return list<string>
     */
    public static function rolesRequiringService(): array
    {
        return [
            'agent_rdv',
            'validateur_rdv',
            'agent_formalites',
            'responsable_service_accueil_formalites',
        ];
    }

    /**
     * @return list<string>
     */
    public static function portalRoles(): array
    {
        return [
            'pensionne',
            'fonctionnaire',
            'institution',
        ];
    }

    /**
     * @return list<string>
     */
    public static function internalRoles(): array
    {
        return array_values(array_diff(self::canonicalRoles(), self::portalRoles()));
    }

    /**
     * @return list<string>
     */
    public static function directionRoles(): array
    {
        return [
            User::ROLE_DIRECTEUR,
            User::ROLE_ASSISTANT_DIRECTEUR,
        ];
    }

    /**
     * @return list<string>
     */
    public static function adminOrDirectionRoles(): array
    {
        return array_values(array_unique(array_merge(['admin'], self::directionRoles())));
    }

    /**
     * @return list<string>
     */
    public static function corbeilleAccessRoles(): array
    {
        return self::internalRoles();
    }

    /**
     * @return array<string, string>
     */
    public static function roleToServiceCode(): array
    {
        return [
            User::ROLE_DIRECTEUR => 'direction',
            User::ROLE_ASSISTANT_DIRECTEUR => 'direction',
            'responsable_service_accueil_formalites' => 'service_accueil_formalites',
            User::ROLE_AGENT_FORMALITES => 'service_accueil_formalites',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        $all = self::allPermissionNames();

        return [
            'admin' => $all,
            'pensionne' => [
                'CAN_VIEW_PENSIONNE_SECTION',
                'CAN_VIEW_PENSIONNE_MENU',
                'CAN_VIEW_DASHBOARD',
            ],
            'fonctionnaire' => [
                'CAN_VIEW_FONCTIONNAIRE_SECTION',
                'CAN_VIEW_FONCTIONNAIRE_MENU',
                'CAN_VIEW_DASHBOARD',
            ],
            'institution' => [
                'CAN_VIEW_INSTITUTION_SECTION',
                'CAN_VIEW_INSTITUTION_MENU',
                'CAN_VIEW_DASHBOARD',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function allRoleNamesForSeeding(): array
    {
        return self::canonicalRoles();
    }

    public static function guard(): string
    {
        return self::GUARD;
    }

    private function seedPermissions(): void
    {
        foreach (self::permissionNames() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => self::guard(),
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function seedRoles(): void
    {
        foreach (self::allRoleNamesForSeeding() as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => self::guard(),
            ]);
        }
    }

    private function syncRolePermissions(): void
    {
        foreach (self::rolePermissions() as $roleName => $permissionNames) {
            $role = Role::findByName($roleName, self::guard());
            $role->syncPermissions($permissionNames);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
