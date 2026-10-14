<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\BeneficiaireRelationEvent;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use Illuminate\Support\Collection;

class BeneficiaireAccessService
{
    /**
     * @return Collection<int, UserBeneficiaireLink>
     */
    public function grantingLinksFor(User $user): Collection
    {
        return UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->with('beneficiaire.affiliations')
            ->get()
            ->filter(fn (UserBeneficiaireLink $link) => $this->linkGrantsAccess($link));
    }

    /**
     * @return Collection<int, PensionMandat>
     */
    public function activeMandatsFor(User $user): Collection
    {
        return PensionMandat::query()
            ->where('user_id', $user->id)
            ->where('status', PensionMandatStatusEnum::ACTIF->value)
            ->with('beneficiaire.affiliations')
            ->get()
            ->filter(fn (PensionMandat $mandat) => $mandat->isCurrentlyActive());
    }

    /**
     * @return Collection<int, Beneficiaire>
     */
    public function accessibleBeneficiaires(User $user): Collection
    {
        $fromLinks = $this->grantingLinksFor($user)
            ->reject(fn (UserBeneficiaireLink $link) => $link->relation === BeneficiaireRelationEnum::MANDATAIRE)
            ->map(fn (UserBeneficiaireLink $link) => $link->beneficiaire)
            ->filter();

        $fromMandats = $this->activeMandatsFor($user)
            ->map(fn (PensionMandat $mandat) => $mandat->beneficiaire)
            ->filter();

        return $fromLinks->merge($fromMandats)->unique('id')->values();
    }

    public function userCanAccessBeneficiaire(User $user, Beneficiaire $beneficiaire): bool
    {
        return $this->accessibleBeneficiaires($user)->contains('id', $beneficiaire->id);
    }

    public function linkGrantsAccess(UserBeneficiaireLink $link): bool
    {
        if (! $link->isWithinValidityPeriod()) {
            return false;
        }

        $status = $link->resolvedStatus();

        if (! $status->grantsAccess()) {
            return false;
        }

        if ($link->relation === BeneficiaireRelationEnum::MANDATAIRE) {
            return $this->hasActiveMandatFor($link->user_id, $link->beneficiaire_id);
        }

        if ($link->relation->isRepresentative()) {
            return $link->autorise(BeneficiaireLinkPermissionEnum::CONSULTER_DOSSIER->value);
        }

        return true;
    }

    public function hasActiveMandatFor(int $userId, int $beneficiaireId): bool
    {
        return PensionMandat::query()
            ->where('user_id', $userId)
            ->where('beneficiaire_id', $beneficiaireId)
            ->where('status', PensionMandatStatusEnum::ACTIF->value)
            ->get()
            ->contains(fn (PensionMandat $m) => $m->isCurrentlyActive());
    }

    public function userCanActOnDemandeType(User $user, ?Beneficiaire $beneficiaire, string $typeDemande): bool
    {
        if (! $beneficiaire) {
            return true;
        }

        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        if ($link && $this->linkGrantsAccess($link)) {
            if ($link->relation === BeneficiaireRelationEnum::SELF) {
                return true;
            }

            if ($link->relation->isRepresentative()) {
                return $link->autorise(BeneficiaireLinkPermissionEnum::SOUMETTRE_DEMANDE->value);
            }
        }

        $mandat = PensionMandat::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('status', PensionMandatStatusEnum::ACTIF->value)
            ->get()
            ->first(fn (PensionMandat $m) => $m->isCurrentlyActive() && $m->allowsDemandeType($typeDemande));

        return $mandat !== null;
    }

    public function userCanUsePensionCodeForDemande(
        User $user,
        ?Beneficiaire $beneficiaire,
        string $code,
        string $typeDemande,
    ): bool {
        if (! $this->userCanActOnDemandeType($user, $beneficiaire, $typeDemande)) {
            return false;
        }

        if (! $beneficiaire) {
            return true;
        }

        $normalized = AffiliationPension::normalizeCode($code);

        $affiliations = $beneficiaire->relationLoaded('affiliations')
            ? $beneficiaire->affiliations
            : $beneficiaire->affiliations()->get();

        $ownsCode = $affiliations->contains(
            fn (AffiliationPension $affiliation) => AffiliationPension::normalizeCode($affiliation->code_pension) === $normalized
        );

        if (! $ownsCode) {
            return false;
        }

        $link = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        if ($link?->relation === BeneficiaireRelationEnum::MANDATAIRE) {
            $mandat = PensionMandat::query()
                ->where('user_id', $user->id)
                ->where('beneficiaire_id', $beneficiaire->id)
                ->where('status', PensionMandatStatusEnum::ACTIF->value)
                ->get()
                ->first(fn (PensionMandat $m) => $m->isCurrentlyActive()
                    && $m->allowsDemandeType($typeDemande)
                    && $m->allowsPensionCode($normalized));

            return $mandat !== null;
        }

        return true;
    }

    public function userCanViewDemande(User $user, string $typeDemande, ?int $beneficiaireId, ?string $codePension): bool
    {
        $beneficiaire = $beneficiaireId
            ? Beneficiaire::query()->find($beneficiaireId)
            : null;

        if (! $beneficiaire && $codePension) {
            $beneficiaire = AffiliationPension::findByCode($codePension)?->beneficiaire;
        }

        if (! $beneficiaire) {
            return false;
        }

        if (! $user->can('view', $beneficiaire)) {
            return false;
        }

        if ($codePension) {
            if (! $this->codeBelongsToBeneficiaire($beneficiaire, $codePension)) {
                return false;
            }

            $link = UserBeneficiaireLink::query()
                ->where('user_id', $user->id)
                ->where('beneficiaire_id', $beneficiaire->id)
                ->first();

            if ($link?->relation === BeneficiaireRelationEnum::MANDATAIRE) {
                $normalized = AffiliationPension::normalizeCode($codePension);

                return PensionMandat::query()
                    ->where('user_id', $user->id)
                    ->where('beneficiaire_id', $beneficiaire->id)
                    ->where('status', PensionMandatStatusEnum::ACTIF->value)
                    ->get()
                    ->contains(fn (PensionMandat $m) => $m->isCurrentlyActive()
                        && $m->allowsPensionCode($normalized));
            }

            return true;
        }

        return true;
    }

    public function codeBelongsToBeneficiaire(Beneficiaire $beneficiaire, string $code): bool
    {
        $normalized = AffiliationPension::normalizeCode($code);
        $affiliations = $beneficiaire->relationLoaded('affiliations')
            ? $beneficiaire->affiliations
            : $beneficiaire->affiliations()->get();

        return $affiliations->contains(
            fn (AffiliationPension $affiliation) => AffiliationPension::normalizeCode($affiliation->code_pension) === $normalized
        );
    }

    public function revokeMandataireLink(int $userId, int $beneficiaireId, ?User $acteur = null): void
    {
        UserBeneficiaireLink::query()
            ->where('user_id', $userId)
            ->where('beneficiaire_id', $beneficiaireId)
            ->where('relation', BeneficiaireRelationEnum::MANDATAIRE->value)
            ->get()
            ->each(function (UserBeneficiaireLink $link) use ($acteur) {
                $before = $link->only(['status', 'valid_until']);
                $link->update([
                    'status' => BeneficiaireLinkStatusEnum::REVOQUE,
                    'valid_until' => now()->toDateString(),
                ]);
                BeneficiaireRelationEvent::record(
                    $link,
                    'relation_revoquee',
                    $acteur,
                    $before,
                    $link->only(['status', 'valid_until']),
                );
            });
    }
}
