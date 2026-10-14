<?php

namespace App\Policies;

use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Beneficiaire;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use App\Services\Pension\BeneficiaireAccessService;
use Illuminate\Auth\Access\HandlesAuthorization;

class BeneficiairePolicy
{
    use HandlesAuthorization;

    public function __construct(
        private readonly BeneficiaireAccessService $access,
    ) {}

    public function view(User $user, Beneficiaire $beneficiaire): bool
    {
        if (! $this->access->userCanAccessBeneficiaire($user, $beneficiaire)) {
            return false;
        }

        $link = $this->activeLink($user, $beneficiaire);

        if ($link && $link->relation->isRepresentative()) {
            return $link->autorise(BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value);
        }

        return true;
    }

    public function deposerDocuments(User $user, Beneficiaire $beneficiaire): bool
    {
        if (! $this->access->userCanAccessBeneficiaire($user, $beneficiaire)) {
            return false;
        }

        $link = $this->activeLink($user, $beneficiaire);

        if ($link && $link->relation->isRepresentative()) {
            return $link->autorise(BeneficiaireLinkPermissionEnum::DEPOSER_DOCUMENTS->value);
        }

        return true;
    }

    public function createDemande(User $user, Beneficiaire $beneficiaire, string $typeDemande): bool
    {
        if (! $this->access->userCanAccessBeneficiaire($user, $beneficiaire)) {
            return false;
        }

        $link = $this->activeLink($user, $beneficiaire);

        if ($link && $link->relation->isRepresentative()) {
            if (! $link->autorise(BeneficiaireLinkPermissionEnum::SOUMETTRE_DEMANDE->value)) {
                return false;
            }
        }

        return $this->access->userCanActOnDemandeType($user, $beneficiaire, $typeDemande);
    }

    public function createDemandeType(User $user, TypeDemandeEnum|string $type): bool
    {
        $typeValue = $type instanceof TypeDemandeEnum ? $type->value : $type;
        $beneficiaire = $user->pensionContext()->resolveBeneficiaire();

        if (! $beneficiaire) {
            return $user->canAccessFullServices();
        }

        return $this->createDemande($user, $beneficiaire, $typeValue);
    }

    public function usePensionCodeForDemande(
        User $user,
        Beneficiaire $beneficiaire,
        string $code,
        string $typeDemande,
    ): bool {
        if (! $this->createDemande($user, $beneficiaire, $typeDemande)) {
            return false;
        }

        return $this->access->userCanUsePensionCodeForDemande($user, $beneficiaire, $code, $typeDemande);
    }

    private function activeLink(User $user, Beneficiaire $beneficiaire): ?UserBeneficiaireLink
    {
        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        if ($link && $this->access->linkGrantsAccess($link)) {
            return $link;
        }

        return null;
    }
}
