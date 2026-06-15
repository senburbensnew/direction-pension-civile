<?php

namespace App\Services;

use App\Models\Assignment;
use App\Models\Demande;
use App\Models\DemandeCircuitSnapshot;
use App\Models\DemandeHistory;
use App\Models\DemandeInteraction;
use App\Models\RequiredCircuitService;
use App\Models\Service;
use App\Models\StepRequiredDocument;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Models\WorkflowStepTransition;
use Illuminate\Support\Facades\DB;

class DemandeWorkflowService
{
    public function submit(Demande $demande, User $user): void
    {
        $directionId = Service::where('code', Service::DIRECTION)->value('id');
        $soumiseStep = WorkflowStep::forCode('SOUMISE', $demande->type)
            ?? WorkflowStep::forService($directionId, $demande->type);

        // Snapshot du circuit au moment de la soumission
        $snapshot = [
            'step_transitions' => WorkflowStepTransition::whereHas('toStep', fn ($q) => $q->where(
                fn ($q2) => $q2->whereNull('type_demande')->orWhere('type_demande', $demande->type)
            ))->get(['from_step_id', 'to_step_id', 'action', 'is_urgent_only'])->toArray(),
            'required_services' => RequiredCircuitService::where(function ($q) use ($demande) {
                $q->whereNull('type_demande')->orWhere('type_demande', $demande->type);
            })->pluck('service_id')->all(),
            'captured_at' => now()->toIso8601String(),
        ];

        $demande->update([
            'current_service_id' => $directionId,
            'current_step_id'    => $soumiseStep?->id,
        ]);

        DemandeCircuitSnapshot::updateOrCreate(
            ['demande_id' => $demande->id],
            ['snapshot' => $snapshot]
        );

        $demande->interactions()->create([
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => null,
            'to_service_id'   => $directionId,
            'initiated_by'    => $user->id,
            'statut'          => DemandeInteraction::STATUT_ACCEPTE,
        ]);
    }

    /**
     * Valide une transition service-à-service via le graphe d'étapes.
     * Retombe sur allowAll si aucun circuit d'étapes n'est configuré.
     */
    public function validateTransition(
        int $fromServiceId,
        int $toServiceId,
        ?string $type = null,
        bool $isUrgent = false,
        ?Demande $demande = null,
        ?User $user = null,
    ): bool {
        $fromStep = WorkflowStep::forService($fromServiceId, $type);
        $toStep   = WorkflowStep::forService($toServiceId, $type);

        if ($fromStep && $toStep) {
            $snapshot = $demande?->circuitSnapshot?->snapshot;

            if (!empty($snapshot['step_transitions'])) {
                $inSnapshot = collect($snapshot['step_transitions'])
                    ->contains(fn ($t) =>
                        $t['from_step_id'] === $fromStep->id
                        && $t['to_step_id'] === $toStep->id
                        && (!$t['is_urgent_only'] || $isUrgent)
                    );

                if (!$inSnapshot) {
                    return false;
                }

                if ($user) {
                    $live = WorkflowStepTransition::where('from_step_id', $fromStep->id)
                        ->where('to_step_id', $toStep->id)
                        ->first();

                    if ($live) {
                        return $live->canExecute($demande, $user);
                    }
                }

                return true;
            }

            return WorkflowStepTransition::allowed($fromStep->id, $toStep->id, $isUrgent, $demande, $user);
        }

        return true;
    }

    /**
     * Crée des affectations parallèles (type=AVIS) pour un ou plusieurs services.
     *
     * @param  int[]  $serviceIds
     */
    public function affecterServices(Demande $demande, array $serviceIds, User $user): void
    {
        DB::transaction(function () use ($demande, $serviceIds, $user) {
            foreach ($serviceIds as $serviceId) {
                $existing = $demande->interactions()
                    ->where('type', DemandeInteraction::TYPE_AVIS)
                    ->where('to_service_id', $serviceId)
                    ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                    ->first();

                if (!$existing) {
                    $demande->interactions()->create([
                        'type'         => DemandeInteraction::TYPE_AVIS,
                        'to_service_id' => $serviceId,
                        'initiated_by'  => $user->id,
                        'statut'        => DemandeInteraction::STATUT_EN_ATTENTE,
                    ]);
                } else {
                    $existing->update([
                        'initiated_by' => $user->id,
                        'statut'       => DemandeInteraction::STATUT_EN_ATTENTE,
                        'reponse'      => null,
                        'repondu_at'   => null,
                        'repondu_by'   => null,
                    ]);
                }
            }

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'AFFECTATION_CREATED',
                'statut'      => $demande->currentStep?->code,
                'commentaire' => 'Dossier affecté à ' . count($serviceIds) . ' service(s) pour avis.',
                'changed_by'  => $user->id,
            ]);
        });
    }

    public function repondreAffectation(DemandeInteraction $affectation, User $user, ?string $avis, string $statut): void
    {
        $affectation->update([
            'statut'     => $statut,
            'reponse'    => $avis,
            'repondu_at' => now(),
            'repondu_by' => $user->id,
        ]);

        DemandeHistory::create([
            'demande_id'  => $affectation->demande_id,
            'event'       => 'AVIS_SUBMITTED',
            'statut'      => $affectation->demande->currentStep?->code,
            'commentaire' => 'Avis du service ' . $affectation->toService->nom . ' : ' . $statut . ($avis ? ' — ' . $avis : ''),
            'changed_by'  => $user->id,
        ]);
    }

    /**
     * Initie un transfert (type=TRANSFERT) vers un service. Statut : EN_ATTENTE de réception.
     */
    public function transfer(
        Demande $demande,
        Service $toService,
        User $user,
        ?string $commentaire = null
    ): DemandeInteraction {
        abort_unless(
            $this->validateTransition($demande->current_service_id, $toService->id, $demande->type, (bool) $demande->is_urgent, $demande, $user),
            403,
            'Transfert non autorisé selon le circuit défini.'
        );

        $missingDocs = $this->missingStepDocuments($demande);
        abort_if(
            count($missingDocs) > 0,
            422,
            'Documents manquants avant de pouvoir transférer : ' . implode(', ', $missingDocs) . '.'
        );

        DB::transaction(function () use ($demande, $toService, $user, $commentaire) {
            $locked        = Demande::lockForUpdate()->findOrFail($demande->id);
            $fromServiceId = $locked->current_service_id;

            abort_unless(
                $this->validateTransition($fromServiceId, $toService->id, $locked->type, (bool) $locked->is_urgent, $locked, $user),
                403,
                'Transfert non autorisé : le dossier a été modifié entre-temps.'
            );

            $toStep        = WorkflowStep::forService($toService->id, $locked->type);
            $transientStep = WorkflowStep::forCode('TRANSFERT_EN_ATTENTE');

            $locked->update([
                'current_service_id' => $toService->id,
                'current_step_id'    => $transientStep?->id ?? $toStep?->id,
            ]);

            $this->lastInteraction = $locked->interactions()->create([
                'type'            => DemandeInteraction::TYPE_TRANSFERT,
                'from_service_id' => $fromServiceId,
                'to_service_id'   => $toService->id,
                'initiated_by'    => $user->id,
                'commentaire'     => $commentaire,
                'statut'          => DemandeInteraction::STATUT_EN_ATTENTE,
            ]);

            DemandeHistory::create([
                'demande_id'  => $locked->id,
                'event'       => 'TRANSFERT_INITIATED',
                'statut'      => 'TRANSFERT_EN_ATTENTE',
                'commentaire' => 'Transfert initié vers : ' . ($toStep?->nom ?? $toService->nom) . ($commentaire ? ' — ' . $commentaire : ''),
                'changed_by'  => $user->id,
            ]);
        });

        return $this->lastInteraction;
    }

    public function accepterReception(DemandeInteraction $interaction, User $user): void
    {
        abort_unless($interaction->isPending(), 422, 'Ce transfert a déjà été traité.');

        $demande = $interaction->demande;

        DB::transaction(function () use ($interaction, $demande, $user) {
            $interaction->update([
                'statut'     => DemandeInteraction::STATUT_ACCEPTE,
                'repondu_at' => now(),
                'repondu_by' => $user->id,
            ]);

            $serviceId  = $demande->current_service_id ?? $interaction->to_service_id;
            $actualStep = $serviceId ? WorkflowStep::forService((int) $serviceId, $demande->type) : null;
            $actualStep ??= WorkflowStep::forCode('EN_COURS');

            if ($actualStep) {
                $demande->update(['current_step_id' => $actualStep->id]);
            }

            $stepName = $actualStep?->nom ?? $interaction->toService?->nom;

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'TRANSFERT_ACCEPTED',
                'statut'      => $actualStep?->code ?? 'EN_COURS',
                'commentaire' => 'Réception confirmée — étape : ' . $stepName,
                'changed_by'  => $user->id,
            ]);
        });
    }

    public function refuserReception(DemandeInteraction $interaction, User $user, ?string $motif = null): void
    {
        abort_unless($interaction->isPending(), 422, 'Ce transfert a déjà été traité.');

        $demande     = $interaction->demande;
        $fromService = $interaction->from_service_id;
        $refuseStep  = WorkflowStep::forCode('TRANSFERT_REFUSE');

        DB::transaction(function () use ($interaction, $demande, $user, $motif, $fromService, $refuseStep) {
            $interaction->update([
                'statut'     => DemandeInteraction::STATUT_REJETE,
                'reponse'    => $motif,
                'repondu_at' => now(),
                'repondu_by' => $user->id,
            ]);

            $totalRefusals = $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_TRANSFERT)
                ->where('statut', DemandeInteraction::STATUT_REJETE)
                ->count();

            if ($totalRefusals >= 3) {
                $directionId = Service::where('code', Service::DIRECTION)->value('id');
                $demande->update([
                    'current_service_id' => $directionId,
                    'current_step_id'    => $refuseStep?->id ?? WorkflowStep::forService($directionId, $demande->type)?->id,
                ]);
                DemandeHistory::create([
                    'demande_id'  => $demande->id,
                    'event'       => 'TRANSFERT_REFUSED',
                    'statut'      => 'TRANSFERT_REFUSE',
                    'commentaire' => "Transfert refusé par : {$interaction->toService->nom}" . ($motif ? " — {$motif}" : '') . " (escalade automatique à la Direction après {$totalRefusals} refus).",
                    'changed_by'  => $user->id,
                ]);
            } else {
                $demande->update([
                    'current_service_id' => $fromService,
                    'current_step_id'    => $refuseStep?->id ?? WorkflowStep::forService($fromService, $demande->type)?->id,
                ]);
                DemandeHistory::create([
                    'demande_id'  => $demande->id,
                    'event'       => 'TRANSFERT_REFUSED',
                    'statut'      => 'TRANSFERT_REFUSE',
                    'commentaire' => "Transfert refusé par : {$interaction->toService->nom}" . ($motif ? " — {$motif}" : '') . " (refus n°{$totalRefusals}).",
                    'changed_by'  => $user->id,
                ]);
            }
        });
    }

    /**
     * Destinations accessibles depuis l'étape courante de la demande.
     */
    public function availableDestinations(Demande $demande, ?User $user = null): \Illuminate\Support\Collection
    {
        $currentStep = $demande->currentStep;
        $snapshot    = $demande->circuitSnapshot?->snapshot;

        if (!empty($snapshot['step_transitions'])) {
            $toStepIds = collect($snapshot['step_transitions'])
                ->filter(fn ($t) =>
                    $t['from_step_id'] === $currentStep?->id
                    && (!$t['is_urgent_only'] || $demande->is_urgent)
                )
                ->pluck('to_step_id')
                ->unique();

            $steps = WorkflowStep::with('service')->whereIn('id', $toStepIds)->get();

            if ($user) {
                $steps = $steps->filter(function ($step) use ($demande, $user, $currentStep) {
                    $live = WorkflowStepTransition::where('from_step_id', $currentStep?->id)
                        ->where('to_step_id', $step->id)
                        ->first();
                    return !$live || $live->canExecute($demande, $user);
                });
            }

            return $steps->values();
        }

        return WorkflowStepTransition::destinationsFor($currentStep?->id, $demande->is_urgent, $demande, $user);
    }

    public function assignerAgent(Demande $demande, User $agent, User $par, ?string $note = null): Assignment
    {
        $demande->assignments()->whereNull('ended_at')->update(['ended_at' => now()]);

        return $demande->assignments()->create([
            'user_id'     => $agent->id,
            'assigned_by' => $par->id,
            'note'        => $note,
        ]);
    }

    private function missingStepDocuments(Demande $demande): array
    {
        if (!$demande->current_step_id) return [];

        $required = StepRequiredDocument::forStep($demande->current_step_id, $demande->type);
        if ($required->isEmpty()) return [];

        $existingCollections = $demande->getMedia()->pluck('collection_name')->unique();

        return $required
            ->filter(fn ($req) => !$existingCollections->contains($req->document_type))
            ->pluck('label')
            ->all();
    }

    private DemandeInteraction $lastInteraction;
}
