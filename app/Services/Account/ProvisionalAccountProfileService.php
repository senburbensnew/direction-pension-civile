<?php

namespace App\Services\Account;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Détermine catégories et rôles portail à la création d'un compte provisoire.
 *
 * Un représentant légal (mineur) n'est pas nécessairement pensionné titulaire.
 */
class ProvisionalAccountProfileService
{
    public function configure(
        User $user,
        bool $isMinorRepresentativeAccount,
        bool $representativeIsAlsoPensionne = false,
        bool $isMandataireAccount = false,
        bool $mandataireIsAlsoPensionne = false,
    ): void {
        $sync = app(UserCategoryRoleSyncService::class);

        if ($isMandataireAccount) {
            if ($mandataireIsAlsoPensionne) {
                Role::findOrCreate('pensionne', 'web');
                $user->assignRole('pensionne');
                $sync->syncCategories($user, ['externe', 'pensionne'], 'pensionne');
            } else {
                $sync->assignCategory($user, 'externe', primary: true, syncRoles: false);
            }

            return;
        }

        if (! $isMinorRepresentativeAccount) {
            Role::findOrCreate('pensionne', 'web');
            $user->assignRole('pensionne');
            $sync->assignCategory($user, 'pensionne', primary: true, syncRoles: false);

            return;
        }

        if ($representativeIsAlsoPensionne) {
            Role::findOrCreate('pensionne', 'web');
            $user->assignRole('pensionne');
            $sync->syncCategories($user, ['externe', 'pensionne'], 'pensionne');

            return;
        }

        $sync->assignCategory($user, 'externe', primary: true, syncRoles: false);
    }
}
