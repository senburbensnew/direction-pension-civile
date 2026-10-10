<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireRelationEnum;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use InvalidArgumentException;

class UserBeneficiaireLinkService
{
    public function attach(
        User $user,
        Beneficiaire $beneficiaire,
        BeneficiaireRelationEnum $relation,
        ?\DateTimeInterface $validFrom = null,
        ?\DateTimeInterface $validUntil = null,
    ): UserBeneficiaireLink {
        if ($relation === BeneficiaireRelationEnum::SELF && $beneficiaire->is_mineur) {
            throw new InvalidArgumentException('Un mineur ne peut pas avoir de lien titulaire (self) sur son dossier.');
        }

        return UserBeneficiaireLink::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'beneficiaire_id' => $beneficiaire->id,
                'relation' => $relation->value,
            ],
            [
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
            ]
        );
    }

    public function detach(UserBeneficiaireLink $link): void
    {
        $link->delete();
    }
}
