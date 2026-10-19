<?php

namespace App\Services;

use App\Models\User;

class PortalNavService
{
    /**
     * Admins pass every Gate check but must not see pension / agent portal menu entries.
     */
    private function adminUsesSimplifiedAccountNav(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function userCanSeeMesDemandes(User $user): bool
    {
        if (! $user->can('nav.mes_demandes')) {
            return false;
        }

        if ($this->adminUsesSimplifiedAccountNav($user)
            || $user->isResponsableServiceFormalites()
            || $user->isFormalitesFieldAgent()) {
            return false;
        }

        return true;
    }

    public function userCanSeeCorbeille(User $user): bool
    {
        if ($this->adminUsesSimplifiedAccountNav($user)) {
            return false;
        }

        if (! $user->can('nav.corbeille')) {
            return false;
        }

        return $user->hasAnyRole(config('portal_nav.internal_roles_for_corbeille', []));
    }

    public function userCanSeeBeneficiairesMandats(User $user): bool
    {
        if ($this->adminUsesSimplifiedAccountNav($user)) {
            return false;
        }

        if (! $user->can('nav.beneficiaires_mandats') || ! $user->hasPensionPortalAccess()) {
            return false;
        }

        if (! config('compte.show_pensionne_beneficiaires_mandats_nav', false)) {
            if ($user->hasCategory('pensionne') || $user->hasRole('pensionne')) {
                return false;
            }
        }

        return true;
    }

    public function userCanSeeFormalitesComptes(User $user): bool
    {
        if ($this->adminUsesSimplifiedAccountNav($user)) {
            return false;
        }

        return $user->can('nav.formalites_comptes');
    }

    public function userCanSeeFormalitesSuivi(User $user): bool
    {
        if ($this->adminUsesSimplifiedAccountNav($user)) {
            return false;
        }

        return $user->can('nav.formalites_suivi');
    }

    /**
     * @return list<string>
     */
    public static function permissionNames(): array
    {
        return array_keys(config('portal_nav.permissions', []));
    }

    /**
     * @return array<string, list<string>>
     */
    public static function defaultNavPermissionsByRole(): array
    {
        return config('portal_nav.defaults_by_role', []);
    }
}
