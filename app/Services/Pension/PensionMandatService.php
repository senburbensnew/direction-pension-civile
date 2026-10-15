<?php

namespace App\Services\Pension;

use App\Enums\PensionMandatStatusEnum;
use App\Models\BeneficiaireRelationEvent;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;
use App\Models\User;
use App\Notifications\PensionMandatRefuseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PensionMandatService
{
    public function __construct(
        private readonly BeneficiaireProvisioningService $provisioning,
        private readonly PensionMandatNotifier $notifier,
    ) {}

    public function registerFromDemandeCreationCompte(
        User $user,
        DemandeCreationCompte $demandeCompte,
    ): PensionMandat {
        $existing = PensionMandat::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($user, $demandeCompte) {
            $beneficiaireId = $this->resolveBeneficiaireIdFromDemandeCompte($demandeCompte);

            $mandat = PensionMandat::create([
                'user_id' => $user->id,
                'beneficiaire_id' => $beneficiaireId,
                'type' => 'designation_ouverture_compte',
                'status' => PensionMandatStatusEnum::EN_ATTENTE,
                'notes' => 'Créé via demande de compte mandataire '.$demandeCompte->code,
                'demande_creation_compte_id' => $demandeCompte->id,
            ]);

            if ($demandeCompte->hasMedia('mandat_designation')) {
                $demandeCompte->getFirstMedia('mandat_designation')?->copy(
                    $mandat,
                    'mandat_document',
                );
            }

            BeneficiaireRelationEvent::record(
                $mandat,
                'mandat_dcc_enregistre',
                $user,
                null,
                $mandat->only(['status', 'beneficiaire_id', 'demande_creation_compte_id']),
                'En attente de rendez-vous, décision formalités puis approbation mandat',
            );

            $this->notifier->notifyRegisteredViaDemandeCompte($mandat, $demandeCompte);

            return $mandat;
        });
    }

    public function syncBeneficiaireFromDemandeCreationCompte(DemandeCreationCompte $demandeCompte): ?PensionMandat
    {
        if (! $demandeCompte->is_mandataire) {
            return null;
        }

        $user = $demandeCompte->user;
        if (! $user) {
            return null;
        }

        $mandat = PensionMandat::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->first();

        if (! $mandat) {
            $code = trim((string) ($demandeCompte->pension_code ?? ''));
            if ($code === '') {
                return null;
            }

            return $this->registerFromDemandeCreationCompte($user, $demandeCompte);
        }

        if ($mandat->beneficiaire_id !== null) {
            return $mandat;
        }

        $beneficiaireId = $this->resolveBeneficiaireIdFromDemandeCompte($demandeCompte);
        if ($beneficiaireId === null) {
            return $mandat;
        }

        $mandat->update(['beneficiaire_id' => $beneficiaireId]);

        return $mandat->fresh();
    }

    public function onDemandeCreationCompteAccepted(DemandeCreationCompte $demandeCompte): void
    {
        $mandat = PensionMandat::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->first();

        if (! $mandat || ! $mandat->status?->isPending()) {
            return;
        }

        $note = 'Compte accepté ('.$demandeCompte->code.') — mandat en attente d’approbation.';
        $mandat->update([
            'notes' => trim(($mandat->notes ?? '')."\n".$note),
        ]);

        BeneficiaireRelationEvent::record(
            $mandat,
            'mandat_dcc_compte_accepte',
            null,
            null,
            $mandat->only(['status', 'notes']),
            $note,
        );

        $this->notifier->notifyReadyAfterAccountAccepted($mandat->fresh());
    }

    public function refuseFromDemandeCreationCompte(
        DemandeCreationCompte $demandeCompte,
        User $reviewer,
        string $reason,
    ): void {
        $mandat = PensionMandat::query()
            ->where('demande_creation_compte_id', $demandeCompte->id)
            ->where('status', PensionMandatStatusEnum::EN_ATTENTE->value)
            ->first();

        if ($mandat) {
            $this->refuse($mandat, $reviewer, $reason);

            return;
        }
    }

    public function refuse(PensionMandat $mandat, User $reviewer, string $reason): void
    {
        if (! $mandat->status?->isPending()) {
            throw ValidationException::withMessages([
                'mandat' => 'Ce mandat a déjà été traité.',
            ]);
        }

        $reason = trim($reason);
        $before = $mandat->only(['status']);

        $mandat->update([
            'status' => PensionMandatStatusEnum::REFUSE,
            'refusal_reason' => $reason,
            'validated_at' => now(),
            'validated_by' => $reviewer->id,
        ]);

        BeneficiaireRelationEvent::record(
            $mandat,
            'mandat_refuse',
            $reviewer,
            $before,
            $mandat->only(['status', 'refusal_reason']),
            $reason,
        );

        $mandat->mandataire?->notify(new PensionMandatRefuseNotification($mandat->fresh(), $reason));
    }

    private function resolveBeneficiaireIdFromDemandeCompte(DemandeCreationCompte $demandeCompte): ?int
    {
        $code = trim((string) ($demandeCompte->pension_code ?? ''));
        if ($code === '') {
            return null;
        }

        return $this->provisioning->beneficiaireForPensionCode($code)->id;
    }

    public function assertCanSubmitMandat(User $user, string $codePension): void
    {
        $code = \App\Models\AffiliationPension::normalizeCode($codePension);
        if ($code === '') {
            return;
        }

        $beneficiaire = $this->provisioning->beneficiaireForPensionCode($code);

        $pendingViaCompte = PensionMandat::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('status', PensionMandatStatusEnum::EN_ATTENTE->value)
            ->whereNotNull('demande_creation_compte_id')
            ->exists();

        if ($pendingViaCompte) {
            throw ValidationException::withMessages([
                'code_pension' => 'Une demande de création de compte mandataire est déjà en cours pour ce dossier. Consultez « Mes mandats » ou votre demande de compte.',
            ]);
        }

        $pendingDuplicate = PensionMandat::query()
            ->where('user_id', $user->id)
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('status', PensionMandatStatusEnum::EN_ATTENTE->value)
            ->exists();

        if ($pendingDuplicate) {
            throw ValidationException::withMessages([
                'code_pension' => 'Une demande de mandat identique est déjà en attente de validation.',
            ]);
        }
    }

}
