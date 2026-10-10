<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireRelationEnum;
use App\Enums\PensionMandatStatusEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Models\PensionMandat;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use Illuminate\Support\Collection;

class ActivePensionContext
{
    public const SESSION_BENEFICIAIRE = 'pension_context.beneficiaire_id';

    public const SESSION_AFFILIATION = 'pension_context.affiliation_id';

    public function __construct(
        private readonly ?User $user,
    ) {
    }

    public static function for(?User $user): self
    {
        return new self($user);
    }

    /**
     * @return Collection<int, Beneficiaire>
     */
    public function accessibleBeneficiaires(): Collection
    {
        if (! $this->user) {
            return collect();
        }

        $fromLinks = UserBeneficiaireLink::query()
            ->where('user_id', $this->user->id)
            ->with('beneficiaire.affiliations')
            ->get()
            ->filter(fn (UserBeneficiaireLink $link) => $link->isActive())
            ->map(fn (UserBeneficiaireLink $link) => $link->beneficiaire)
            ->filter();

        $fromMandats = PensionMandat::query()
            ->where('user_id', $this->user->id)
            ->where('status', PensionMandatStatusEnum::ACTIF->value)
            ->with('beneficiaire.affiliations')
            ->get()
            ->filter(fn (PensionMandat $mandat) => $mandat->isCurrentlyActive())
            ->map(fn (PensionMandat $mandat) => $mandat->beneficiaire)
            ->filter();

        return $fromLinks->merge($fromMandats)->unique('id')->values();
    }

    public function resolveBeneficiaire(): ?Beneficiaire
    {
        $beneficiaires = $this->accessibleBeneficiaires();

        if ($beneficiaires->isEmpty()) {
            return null;
        }

        $sessionId = session(self::SESSION_BENEFICIAIRE);

        if ($sessionId) {
            $match = $beneficiaires->firstWhere('id', (int) $sessionId);

            if ($match) {
                return $match;
            }
        }

        if ($beneficiaires->count() === 1) {
            return $beneficiaires->first();
        }

        $selfLink = UserBeneficiaireLink::query()
            ->where('user_id', $this->user?->id)
            ->where('relation', BeneficiaireRelationEnum::SELF->value)
            ->first();

        return $selfLink?->beneficiaire ?? $beneficiaires->first();
    }

    public function resolveAffiliation(): ?AffiliationPension
    {
        $beneficiaire = $this->resolveBeneficiaire();

        if (! $beneficiaire) {
            return $this->legacyAffiliationFromUser();
        }

        $affiliations = $beneficiaire->affiliations;

        if ($affiliations->isEmpty()) {
            return $this->legacyAffiliationFromUser();
        }

        $sessionAffId = session(self::SESSION_AFFILIATION);

        if ($sessionAffId) {
            $match = $affiliations->firstWhere('id', (int) $sessionAffId);

            if ($match) {
                return $match;
            }
        }

        return $beneficiaire->primaryAffiliation();
    }

    public function displayPensionCode(): ?string
    {
        if ($this->user && ! $this->user->exposesPensionIdentity()) {
            return null;
        }

        return $this->resolveAffiliation()?->code_pension;
    }

    public function needsContextSwitcher(): bool
    {
        $beneficiaires = $this->accessibleBeneficiaires();

        if ($beneficiaires->count() > 1) {
            return true;
        }

        $beneficiaire = $beneficiaires->first();

        return $beneficiaire && $beneficiaire->affiliations()->count() > 1;
    }

    /**
     * @return Collection<int, AffiliationPension>
     */
    public function accessibleAffiliations(?Beneficiaire $beneficiaire = null): Collection
    {
        $beneficiaire ??= $this->resolveBeneficiaire();

        if (! $beneficiaire) {
            return collect();
        }

        if (! $this->userCanAccessBeneficiaire($beneficiaire)) {
            return collect();
        }

        return $beneficiaire->affiliations;
    }

    public function setContext(int $beneficiaireId, ?int $affiliationId = null): void
    {
        $beneficiaire = $this->accessibleBeneficiaires()->firstWhere('id', $beneficiaireId);

        if (! $beneficiaire) {
            return;
        }

        session([
            self::SESSION_BENEFICIAIRE => $beneficiaire->id,
        ]);

        if ($affiliationId) {
            $affiliation = $beneficiaire->affiliations->firstWhere('id', $affiliationId);

            if ($affiliation) {
                session([self::SESSION_AFFILIATION => $affiliation->id]);
            }
        } else {
            session()->forget(self::SESSION_AFFILIATION);
        }
    }

    public function userCanAccessBeneficiaire(Beneficiaire $beneficiaire): bool
    {
        return $this->accessibleBeneficiaires()->contains('id', $beneficiaire->id);
    }

    public function userCanActOnDemandeType(string $typeDemande): bool
    {
        if (! $this->user) {
            return false;
        }

        $beneficiaire = $this->resolveBeneficiaire();

        if (! $beneficiaire) {
            return true;
        }

        $link = UserBeneficiaireLink::query()
            ->where('user_id', $this->user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        if ($link?->relation === BeneficiaireRelationEnum::SELF) {
            return true;
        }

        if ($link && in_array($link->relation, [
            BeneficiaireRelationEnum::PERE,
            BeneficiaireRelationEnum::MERE,
            BeneficiaireRelationEnum::TUTEUR,
        ], true)) {
            return true;
        }

        $mandat = PensionMandat::query()
            ->where('user_id', $this->user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('status', PensionMandatStatusEnum::ACTIF->value)
            ->get()
            ->first(fn (PensionMandat $m) => $m->isCurrentlyActive() && $m->allowsDemandeType($typeDemande));

        return $mandat !== null;
    }

    public function userCanUsePensionCodeForDemande(string $code, string $typeDemande): bool
    {
        if (! $this->userCanActOnDemandeType($typeDemande)) {
            return false;
        }

        $normalized = AffiliationPension::normalizeCode($code);
        $beneficiaire = $this->resolveBeneficiaire();

        if (! $beneficiaire) {
            return true;
        }

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
            ->where('user_id', $this->user?->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->first();

        if ($link?->relation === BeneficiaireRelationEnum::MANDATAIRE) {
            $mandat = PensionMandat::query()
                ->where('user_id', $this->user?->id)
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

    public function ensureDefaults(): void
    {
        if (! $this->user) {
            return;
        }

        $beneficiaires = $this->accessibleBeneficiaires();

        if ($beneficiaires->isEmpty()) {
            return;
        }

        $sessionBenefId = session(self::SESSION_BENEFICIAIRE);
        $validBenef = $sessionBenefId
            && $beneficiaires->contains('id', (int) $sessionBenefId);

        if (! $validBenef) {
            $default = $beneficiaires->count() === 1
                ? $beneficiaires->first()
                : ($this->resolveBeneficiaire() ?? $beneficiaires->first());

            if ($default) {
                session([self::SESSION_BENEFICIAIRE => $default->id]);
            }
        }

        $beneficiaire = $this->resolveBeneficiaire();

        if (! $beneficiaire) {
            return;
        }

        $affiliations = $beneficiaire->relationLoaded('affiliations')
            ? $beneficiaire->affiliations
            : $beneficiaire->affiliations()->get();

        if ($affiliations->isEmpty()) {
            return;
        }

        $sessionAffId = session(self::SESSION_AFFILIATION);
        $validAff = $sessionAffId && $affiliations->contains('id', (int) $sessionAffId);

        if (! $validAff) {
            $primary = $affiliations->firstWhere('is_primary', true) ?? $affiliations->first();

            if ($primary) {
                session([self::SESSION_AFFILIATION => $primary->id]);
            }
        }
    }

    private function legacyAffiliationFromUser(): ?AffiliationPension
    {
        return null;
    }
}
