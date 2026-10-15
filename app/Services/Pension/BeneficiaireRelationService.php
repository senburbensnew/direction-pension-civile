<?php

namespace App\Services\Pension;

use App\Enums\BeneficiaireLinkPermissionEnum;
use App\Enums\BeneficiaireLinkStatusEnum;
use App\Enums\BeneficiaireRelationDemandeStatusEnum;
use App\Enums\BeneficiaireRelationEnum;
use App\Models\Beneficiaire;
use App\Models\DemandeCreationCompte;
use App\Models\BeneficiaireRelationDemande;
use App\Models\BeneficiaireRelationEvent;
use App\Models\User;
use App\Models\UserBeneficiaireLink;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BeneficiaireRelationService
{
    public function __construct(
        private readonly BeneficiaireProvisioningService $provisioning,
        private readonly BeneficiaireRelationNotifier $notifier,
    ) {}

    /**
     * @param  list<string>|null  $permissions
     */
    public function submitDemande(
        User $user,
        string $codePension,
        BeneficiaireRelationEnum $typeRelation,
        ?string $motif = null,
        ?UploadedFile $justificatif = null,
        ?array $permissions = null,
    ): BeneficiaireRelationDemande {
        if (in_array($typeRelation, [BeneficiaireRelationEnum::SELF, BeneficiaireRelationEnum::MANDATAIRE], true)) {
            throw new \InvalidArgumentException('Type de relation non autorisé pour cette demande.');
        }

        $beneficiaire = $this->provisioning->beneficiaireForPensionCode($codePension);

        $this->assertCanSubmitDemande($user, $beneficiaire, $typeRelation, $codePension);

        return DB::transaction(function () use ($user, $beneficiaire, $typeRelation, $motif, $justificatif, $permissions) {
            $demande = BeneficiaireRelationDemande::create([
                'user_id' => $user->id,
                'beneficiaire_id' => $beneficiaire->id,
                'type_relation' => $typeRelation,
                'status' => BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE,
                'motif' => $motif,
                'permissions' => $permissions,
            ]);

            if ($justificatif) {
                $demande->addMedia($justificatif)->toMediaCollection('justificatif');
            }

            BeneficiaireRelationEvent::record(
                $demande,
                'demande_creee',
                $user,
                null,
                $demande->only(['status', 'type_relation', 'beneficiaire_id']),
                $motif,
            );

            $this->notifier->notifySubmitted($demande);

            return $demande;
        });
    }

    public function registerFromDemandeCreationCompte(
        DemandeCreationCompte $demandeCompte,
        User $user,
        Beneficiaire $beneficiaire,
        BeneficiaireRelationEnum $typeRelation,
        UserBeneficiaireLink $link,
    ): BeneficiaireRelationDemande {
        $existing = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($demandeCompte, $user, $beneficiaire, $typeRelation, $link) {
            $relationDemande = BeneficiaireRelationDemande::create([
                'user_id' => $user->id,
                'beneficiaire_id' => $beneficiaire->id,
                'type_relation' => $typeRelation,
                'status' => BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE,
                'motif' => 'Demande de création de compte '.$demandeCompte->code,
                'demande_creation_compte_id' => $demandeCompte->id,
            ]);

            BeneficiaireRelationEvent::record(
                $relationDemande,
                'relation_dcc_enregistree',
                $user,
                null,
                $relationDemande->only(['status', 'type_relation', 'beneficiaire_id', 'demande_creation_compte_id']),
                'En attente de rendez-vous et décision formalités',
            );

            $this->notifier->notifyRegisteredViaDemandeCompte($relationDemande, $demandeCompte);

            return $relationDemande;
        });
    }

    public function finalizeFromDemandeCreationCompte(
        DemandeCreationCompte $demandeCompte,
        User $reviewer,
    ): ?UserBeneficiaireLink {
        $relationDemande = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->first();

        if (! $relationDemande) {
            return null;
        }

        if ($relationDemande->status?->isPending()) {
            return $this->approve($relationDemande, $reviewer);
        }

        return $relationDemande->link;
    }

    public function refuseFromDemandeCreationCompte(
        DemandeCreationCompte $demandeCompte,
        User $reviewer,
        string $reason,
    ): void {
        $relationDemande = BeneficiaireRelationDemande::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->where('status', BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE->value)
            ->first();

        if ($relationDemande) {
            $this->refuse($relationDemande, $reviewer, $reason);

            return;
        }

        UserBeneficiaireLink::query()
            ->where('user_id', $demandeCompte->user_id)
            ->whereIn('relation', [
                BeneficiaireRelationEnum::PERE->value,
                BeneficiaireRelationEnum::MERE->value,
                BeneficiaireRelationEnum::TUTEUR->value,
            ])
            ->where('status', BeneficiaireLinkStatusEnum::EN_ATTENTE->value)
            ->each(function (UserBeneficiaireLink $link) use ($reviewer, $reason) {
                $link->update(['status' => BeneficiaireLinkStatusEnum::REFUSE]);
                BeneficiaireRelationEvent::record(
                    $link,
                    'relation_dcc_refusee',
                    $reviewer,
                    null,
                    $link->only(['status']),
                    $reason,
                );
            });
    }

    /**
     * @param  list<string>|null  $permissions
     */
    public function approve(BeneficiaireRelationDemande $demande, User $reviewer, ?array $permissions = null): UserBeneficiaireLink
    {
        $this->assertDemandeIsPending($demande);

        return DB::transaction(function () use ($demande, $reviewer, $permissions) {
            $resolvedPermissions = filled($permissions)
                ? $permissions
                : (filled($demande->permissions)
                    ? $demande->permissions
                    : BeneficiaireLinkPermissionEnum::defaultGrant());

            $link = UserBeneficiaireLink::query()->updateOrCreate(
                [
                    'user_id' => $demande->user_id,
                    'beneficiaire_id' => $demande->beneficiaire_id,
                    'relation' => $demande->type_relation->value,
                ],
                [
                    'status' => BeneficiaireLinkStatusEnum::ACTIVE,
                    'permissions' => $resolvedPermissions,
                    'motif' => $demande->motif,
                    'demande_par' => $demande->user_id,
                    'verifie_par' => $reviewer->id,
                    'verifie_le' => now(),
                    'valid_from' => $demande->valid_from,
                    'valid_until' => $demande->valid_until,
                ]
            );

            $demande->update([
                'status' => BeneficiaireRelationDemandeStatusEnum::ACCEPTEE,
                'validated_by' => $reviewer->id,
                'validated_at' => now(),
                'refusal_reason' => null,
                'user_beneficiaire_link_id' => $link->id,
            ]);

            BeneficiaireRelationEvent::record(
                $link,
                'relation_approuvee',
                $reviewer,
                null,
                $link->fresh()->toArray(),
                'Demande #'.$demande->id,
            );

            $demande->refresh();
            $this->notifier->notifyApproved($demande);

            return $link;
        });
    }

    public function refuse(BeneficiaireRelationDemande $demande, User $reviewer, string $reason): void
    {
        $this->assertDemandeIsPending($demande);

        DB::transaction(function () use ($demande, $reviewer, $reason) {
            $before = $demande->only(['status']);
            $demande->update([
                'status' => BeneficiaireRelationDemandeStatusEnum::REFUSEE,
                'refusal_reason' => $reason,
                'validated_by' => $reviewer->id,
                'validated_at' => now(),
            ]);

            BeneficiaireRelationEvent::record(
                $demande,
                'relation_refusee',
                $reviewer,
                $before,
                $demande->only(['status', 'refusal_reason']),
                $reason,
            );

            $demande->refresh();
            $this->notifier->notifyRefused($demande);
        });
    }

    public function revokeLink(UserBeneficiaireLink $link, User $acteur, ?string $commentaire = null): void
    {
        DB::transaction(function () use ($link, $acteur, $commentaire) {
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
                $commentaire,
            );

            $link->refresh();
            $this->notifier->notifyRevoked($link);
        });
    }

    private function assertCanSubmitDemande(
        User $user,
        Beneficiaire $beneficiaire,
        BeneficiaireRelationEnum $typeRelation,
        string $codePension,
    ): void {
        $normalizedCode = \App\Models\AffiliationPension::normalizeCode($codePension);
        $existingLink = UserBeneficiaireLink::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('relation', $typeRelation->value)
            ->first();

        if ($existingLink && $existingLink->resolvedStatus()->grantsAccess()) {
            throw ValidationException::withMessages([
                'code_pension' => 'Vous avez déjà un accès actif pour ce bénéficiaire avec cette relation.',
            ]);
        }

        $pendingExists = BeneficiaireRelationDemande::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('type_relation', $typeRelation->value)
            ->where('status', BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE->value)
            ->exists();

        if ($pendingExists) {
            throw ValidationException::withMessages([
                'code_pension' => 'Une demande identique est déjà en attente de validation.',
            ]);
        }

        $pendingViaCompte = BeneficiaireRelationDemande::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('status', BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE->value)
            ->whereNotNull('demande_creation_compte_id')
            ->exists();

        if ($pendingViaCompte) {
            throw ValidationException::withMessages([
                'code_pension' => 'Une demande de création de compte représentant est déjà en cours pour ce dossier. Consultez « Mes bénéficiaires » ou « Demande de compte ».',
            ]);
        }

        if ($normalizedCode !== '') {
            $pendingForCode = BeneficiaireRelationDemande::query()
                ->where('user_id', $user->id)
                ->where('status', BeneficiaireRelationDemandeStatusEnum::EN_ATTENTE->value)
                ->whereHas('beneficiaire.affiliations', function ($query) use ($normalizedCode) {
                    $query->whereRaw("UPPER(REPLACE(COALESCE(code_pension, ''), ' ', '')) = ?", [$normalizedCode]);
                })
                ->exists();

            if ($pendingForCode) {
                throw ValidationException::withMessages([
                    'code_pension' => 'Une demande identique est déjà en attente de validation.',
                ]);
            }
        }
    }

    private function assertDemandeIsPending(BeneficiaireRelationDemande $demande): void
    {
        if (! $demande->status?->isPending()) {
            throw ValidationException::withMessages([
                'demande' => 'Cette demande a déjà été traitée.',
            ]);
        }
    }

    public function expireDueLinks(): int
    {
        $count = 0;
        $today = now()->toDateString();

        UserBeneficiaireLink::query()
            ->where('status', BeneficiaireLinkStatusEnum::ACTIVE->value)
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', $today)
            ->each(function (UserBeneficiaireLink $link) use (&$count) {
                $before = $link->only(['status']);
                $link->update(['status' => BeneficiaireLinkStatusEnum::EXPIRE]);
                BeneficiaireRelationEvent::record(
                    $link,
                    'relation_expiree',
                    null,
                    $before,
                    $link->only(['status']),
                );
                $count++;
            });

        return $count;
    }
}
