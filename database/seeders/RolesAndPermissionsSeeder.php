<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PortalNavService;
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
            'can_view_pensionne_section',
            'can_view_fonctionnaire_section',
            'can_view_institution_section',
            'can_view_pensionne_menu',
            'can_view_fonctionnaire_menu',
            'can_view_institution_menu',
            'can_view_dashboard',
            'pension_mandats.view',
            'pension_mandats.approuver',
            'pension_mandats.revoquer',
            'beneficiaire_relations.view',
            'beneficiaire_relations.approuver',
            'beneficiaire_relations.revoquer',
            'newsletter.view',
            'newsletter.manage_subscribers',
            'newsletter.manage_campaigns',
            'newsletter.send',
            ...PortalNavService::permissionNames(),
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

        $formalites = [
            'pension_mandats.view',
            'pension_mandats.approuver',
            'pension_mandats.revoquer',
            'beneficiaire_relations.view',
            'beneficiaire_relations.approuver',
            'beneficiaire_relations.revoquer',
        ];

        $nav = PortalNavService::defaultNavPermissionsByRole();

        $adminNavHidden = [
            'nav.mes_demandes',
            'nav.corbeille',
            'nav.beneficiaires_mandats',
        ];

        return [
            'admin' => array_values(array_diff($all, $adminNavHidden)),
            'agent_formalites' => array_values(array_unique(array_merge(
                $formalites,
                $nav['agent_formalites'] ?? []
            ))),
            'agent_rdv' => $nav['agent_rdv'] ?? [],
            'responsable_service_accueil_formalites' => array_values(array_unique(array_merge(
                $formalites,
                $nav['responsable_service_accueil_formalites'] ?? []
            ))),
            'validateur_rdv' => $nav['validateur_rdv'] ?? [],
            'directeur' => array_values(array_unique(array_merge(
                $formalites,
                $nav['directeur'] ?? []
            ))),
            'assistant_directeur' => array_values(array_unique(array_merge(
                $formalites,
                $nav['assistant_directeur'] ?? []
            ))),
            'pensionne' => array_values(array_unique(array_merge(
                [
                    'can_view_pensionne_section',
                    'can_view_pensionne_menu',
                    'can_view_dashboard',
                ],
                $nav['pensionne'] ?? []
            ))),
            'fonctionnaire' => array_values(array_unique(array_merge(
                [
                    'can_view_fonctionnaire_section',
                    'can_view_fonctionnaire_menu',
                    'can_view_dashboard',
                ],
                $nav['fonctionnaire'] ?? []
            ))),
            'institution' => array_values(array_unique(array_merge(
                [
                    'can_view_institution_section',
                    'can_view_institution_menu',
                    'can_view_dashboard',
                ],
                $nav['institution'] ?? []
            ))),
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
