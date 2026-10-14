<?php

namespace App\Http\Requests\Concerns;

use App\Enums\TypeDemandeEnum;

trait AuthorizesBeneficiaireDemande
{
    protected function authorizeBeneficiaireDemande(TypeDemandeEnum $type): bool
    {
        $user = $this->user();

        if (! $user || ! $user->canAccessFullServices()) {
            return false;
        }

        if ($this->input('action') === 'draft') {
            return true;
        }

        $beneficiaire = $user->pensionContext()->resolveBeneficiaire();

        if (! $beneficiaire) {
            return true;
        }

        return $user->can('createDemande', [$beneficiaire, $type->value]);
    }
}
