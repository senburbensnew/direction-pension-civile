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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DemandeWorkflowService
{
    /**
     * Soumission : toujours reçue par la Direction (brouillon→soumise ou soumission directe).
     */
    public function submit(Demande $demande, User $user): void
    {
        $direction = Service::where('code', Service::DIRECTION)->first();
        abort_unless($direction, 500, 'Service Direction introuvable. Impossible de soumettre la demande.');

        $soumiseStep = WorkflowStep::forCode('SOUMISE', $demande->type);
        if (!$soumiseStep) {
            $soumiseStep = WorkflowStep::forService($direction->id, $demande->type);
        }
        abort_unless($soumiseStep, 500, 'Étape SOUMISE introuvable. Configurez le circuit de traitement.');

        // Garantir que l'étape SOUMISE est rattachée à la Direction
        if ((int) $soumiseStep->service_id !== (int) $direction->id) {
            $soumiseStep->update(['service_id' => $direction->id]);
            $soumiseStep->refresh();
        }

        $snapshot = [
            'step_transitions'  => $this->captureCircuitTransitions($demande->type),
            'required_services' => RequiredCircuitService::where(function ($q) use ($demande) {
                $q->whereNull('type_demande')->orWhere('type_demande', $demande->type);
            })->pluck('service_id')->all(),
            'captured_at' => now()->toIso8601String(),
        ];

        $demande->update([
            'current_service_id' => $direction->id,
            'current_step_id'    => $soumiseStep->id,
            'submitted_at'       => $demande->submitted_at ?? now(),
            'expires_at'         => null,
        ]);

        DemandeCircuitSnapshot::updateOrCreate(
            ['demande_id' => $demande->id],
            ['snapshot' => $snapshot]
        );

        $demande->interactions()->create([
            'type'            => DemandeInteraction::TYPE_TRANSFERT,
            'from_service_id' => null,
            'to_service_id'   => $direction->id,
            'to_step_id'      => $soumiseStep->id,
            'initiated_by'    => $user->id,
            'commentaire'     => 'Soumission initiale — réception Direction',
            'statut'          => DemandeInteraction::STATUT_ACCEPTE,
        ]);

        DemandeHistory::create([
            'demande_id'  => $demande->id,
            'event'       => 'SUBMITTED',
            'statut'      => 'SOUMISE',
            'commentaire' => 'Demande soumise — reçue par la Direction.',
            'changed_by'  => $user->id,
            'data'        => $demande->data,
        ]);
    }

    /**
     * Valide une transition service-à-service via le graphe d'étapes.
     * Retombe sur allowAll si aucun circuit d'étapes n'est configuré pour les services.
     */
    public function validateTransition(
        int $fromServiceId,
        int $toServiceId,
        ?string $type = null,
        bool $isUrgent = false,
        ?Demande $demande = null,
        ?User $user = null,
    ): bool {
        $fromStep = $this->resolveFromStep($fromServiceId, $type, $demande);
        $circuitConfigured = $this->serviceHasCircuitSteps($fromServiceId, $type)
            || $this->serviceHasCircuitSteps($toServiceId, $type);

        // Circuit présent mais étape source introuvable → refuser (plus d'allow-all silencieux).
        if (!$fromStep) {
            return !$circuitConfigured;
        }

        $destinations = $this->destinationStepsFrom($fromStep, $isUrgent, $demande, $user);
        $hasOutgoing = $destinations->isNotEmpty()
            || WorkflowStepTransition::where('from_step_id', $fromStep->id)->exists();

        if (!$hasOutgoing && !$circuitConfigured) {
            return true;
        }

        return $destinations->contains(
            fn (WorkflowStep $step) => (int) $step->service_id === $toServiceId
        );
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
                        'type'          => DemandeInteraction::TYPE_AVIS,
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

            $fromStep      = $this->resolveFromStep($fromServiceId, $locked->type, $locked);
            $toStep        = $this->resolveDestinationStep($fromStep, $toService->id, $locked->type, (bool) $locked->is_urgent, $locked, $user);
            $transientStep = WorkflowStep::forCode('TRANSFERT_EN_ATTENTE');

            $locked->update([
                'current_service_id' => $toService->id,
                'current_step_id'    => $transientStep?->id ?? $toStep?->id,
            ]);

            $this->lastInteraction = $locked->interactions()->create([
                'type'            => DemandeInteraction::TYPE_TRANSFERT,
                'from_service_id' => $fromServiceId,
                'to_service_id'   => $toService->id,
                'to_step_id'      => $toStep?->id,
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

            $actualStep = $interaction->to_step_id
                ? WorkflowStep::find($interaction->to_step_id)
                : null;

            if (!$actualStep && $interaction->to_service_id) {
                $actualStep = $this->resolveDestinationStep(
                    $this->resolveFromStep($interaction->from_service_id, $demande->type, $demande),
                    (int) $interaction->to_service_id,
                    $demande->type,
                    (bool) $demande->is_urgent,
                    $demande,
                    null,
                );
            }

            $actualStep ??= WorkflowStep::forCode('EN_COURS');

            if ($actualStep) {
                $demande->update([
                    'current_service_id' => $interaction->to_service_id ?? $demande->current_service_id,
                    'current_step_id'    => $actualStep->id,
                ]);
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
     * Après annotation Direction : transmettre au Secrétariat pour dispatching
     * si le circuit le prévoit (arête SOUMISE → étape secrétariat).
     *
     * @return DemandeInteraction|null interaction créée, ou null si non applicable
     */
    public function dispatchToSecretariatAfterAnnotation(Demande $demande, User $user): ?DemandeInteraction
    {
        $direction = Service::where('code', Service::DIRECTION)->first();
        $secretariat = Service::where('code', Service::SECRETARIAT)->first();

        if (!$direction || !$secretariat) {
            return null;
        }

        if ((int) $demande->current_service_id !== (int) $direction->id) {
            return null;
        }

        // Éviter un double transfert si déjà en cours vers le Secrétariat
        $alreadyPending = $demande->interactions()
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('to_service_id', $secretariat->id)
            ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
            ->exists();
        if ($alreadyPending) {
            return null;
        }

        $options = $this->availableTransferOptions($demande, $user);
        $secretariatOption = $options->first(
            fn ($opt) => (int) ($opt->service_id ?? 0) === (int) $secretariat->id
        );

        if (!$secretariatOption) {
            return null;
        }

        return $this->transfer(
            $demande,
            $secretariat,
            $user,
            'Dispatch après annotation Direction — transmission au Secrétariat'
        );
    }

    /**
     * Destinations accessibles depuis l'étape courante (WorkflowStep avec relation service).
     */
    public function availableDestinations(Demande $demande, ?User $user = null): Collection
    {
        $currentStep = $demande->currentStep;
        if (!$currentStep) {
            return collect();
        }

        $snapshot = $demande->circuitSnapshot?->snapshot;

        if (!empty($snapshot['step_transitions'])) {
            $toStepIds = collect($snapshot['step_transitions'])
                ->filter(fn ($t) =>
                    (int) $t['from_step_id'] === (int) $currentStep->id
                    && (empty($t['is_urgent_only']) || $demande->is_urgent)
                )
                ->pluck('to_step_id')
                ->map(fn ($id) => (int) $id)
                ->unique();

            $steps = WorkflowStep::with('service')->whereIn('id', $toStepIds)->get();

            if ($user) {
                $steps = $steps->filter(function ($step) use ($demande, $user, $currentStep) {
                    $live = WorkflowStepTransition::where('from_step_id', $currentStep->id)
                        ->where('to_step_id', $step->id)
                        ->first();

                    return !$live || $live->canExecute($demande, $user);
                });
            }

            return $steps->values();
        }

        return WorkflowStepTransition::destinationsFor($currentStep->id, (bool) $demande->is_urgent, $demande, $user);
    }

    /**
     * Services de destination uniques (pour les selects UI qui postent service_id).
     */
    public function availableDestinationServices(Demande $demande, ?User $user = null): Collection
    {
        return $this->availableDestinations($demande, $user)
            ->map(fn (WorkflowStep $step) => $step->service)
            ->filter()
            ->unique('id')
            ->values();
    }

    /**
     * Options de transfert pilotées par le circuit (snapshot ou graphe live).
     * Se met à jour automatiquement quand l'admin modifie les flows (dossiers sans snapshot,
     * ou nouvelles soumissions avec nouveau snapshot).
     *
     * @return Collection<int, object{service_id:int,service_nom:string,step_nom:string,step_code:string,action:string}>
     */
    public function availableTransferOptions(Demande $demande, ?User $user = null): Collection
    {
        $currentStep = $demande->currentStep;
        if (!$currentStep || $currentStep->codeIsTransient() || $currentStep->isTerminal()) {
            return collect();
        }

        return $this->availableDestinations($demande, $user)
            ->filter(fn (WorkflowStep $step) => $step->service_id)
            ->map(function (WorkflowStep $step) use ($currentStep, $demande) {
                return (object) [
                    'service_id'  => (int) $step->service_id,
                    'service_nom' => $step->service?->nom ?? 'Service',
                    'step_nom'    => $step->nom,
                    'step_code'   => $step->code,
                    'action'      => $this->resolveTransitionAction((int) $currentStep->id, (int) $step->id, $demande),
                ];
            })
            ->unique('service_id')
            ->values();
    }

    public function usesCircuitSnapshot(Demande $demande): bool
    {
        $snapshot = $demande->circuitSnapshot?->snapshot;

        return is_array($snapshot) && !empty($snapshot['step_transitions']);
    }

    /**
     * Service IDs requis pour approbation — préfère le snapshot de soumission.
     */
    public function requiredServiceIds(Demande $demande): Collection
    {
        $snapshot = $demande->circuitSnapshot?->snapshot;

        if (is_array($snapshot) && array_key_exists('required_services', $snapshot)) {
            return collect($snapshot['required_services'])->map(fn ($id) => (int) $id)->unique()->values();
        }

        return RequiredCircuitService::where(function ($q) use ($demande) {
            $q->where('type_demande', $demande->type)->orWhereNull('type_demande');
        })->pluck('service_id')->map(fn ($id) => (int) $id)->unique()->values();
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

    private function resolveFromStep(?int $fromServiceId, ?string $type, ?Demande $demande): ?WorkflowStep
    {
        if ($demande?->currentStep && !$demande->currentStep->codeIsTransient()) {
            return $demande->currentStep;
        }

        if ($demande?->current_step_id) {
            $step = WorkflowStep::find($demande->current_step_id);
            if ($step && !$step->codeIsTransient()) {
                return $step;
            }
        }

        if (!$fromServiceId) {
            return null;
        }

        return WorkflowStep::forService($fromServiceId, $type);
    }

    private function resolveDestinationStep(
        ?WorkflowStep $fromStep,
        int $toServiceId,
        ?string $type,
        bool $isUrgent,
        ?Demande $demande,
        ?User $user,
    ): ?WorkflowStep {
        if (!$fromStep) {
            return null;
        }

        $candidates = $this->destinationStepsFrom($fromStep, $isUrgent, $demande, $user);

        return $candidates->first(fn (WorkflowStep $step) => (int) $step->service_id === $toServiceId);
    }

    private function destinationStepsFrom(
        WorkflowStep $fromStep,
        bool $isUrgent,
        ?Demande $demande,
        ?User $user,
    ): Collection {
        $snapshot = $demande?->circuitSnapshot?->snapshot;

        if (!empty($snapshot['step_transitions'])) {
            $toStepIds = collect($snapshot['step_transitions'])
                ->filter(fn ($t) =>
                    (int) $t['from_step_id'] === (int) $fromStep->id
                    && (empty($t['is_urgent_only']) || $isUrgent)
                )
                ->pluck('to_step_id')
                ->map(fn ($id) => (int) $id)
                ->unique();

            $steps = WorkflowStep::with('service')->whereIn('id', $toStepIds)->get();

            if ($demande && $user) {
                $steps = $steps->filter(function ($step) use ($demande, $user, $fromStep) {
                    $live = WorkflowStepTransition::where('from_step_id', $fromStep->id)
                        ->where('to_step_id', $step->id)
                        ->first();

                    return !$live || $live->canExecute($demande, $user);
                });
            }

            return $steps->values();
        }

        return WorkflowStepTransition::destinationsFor($fromStep->id, $isUrgent, $demande, $user);
    }

    private function serviceHasCircuitSteps(int $serviceId, ?string $type): bool
    {
        return WorkflowStep::where('service_id', $serviceId)
            ->where(function ($q) use ($type) {
                $q->whereNull('type_demande');
                if ($type) {
                    $q->orWhere('type_demande', $type);
                }
            })
            ->exists();
    }

    /**
     * Capture les transitions du circuit type (prioritaire), sinon le circuit global.
     *
     * @return array<int, array<string, mixed>>
     */
    private function resolveTransitionAction(int $fromStepId, int $toStepId, Demande $demande): string
    {
        $snapshot = $demande->circuitSnapshot?->snapshot;
        if (!empty($snapshot['step_transitions'])) {
            $row = collect($snapshot['step_transitions'])->first(fn ($t) =>
                (int) $t['from_step_id'] === $fromStepId && (int) $t['to_step_id'] === $toStepId
            );
            if (!empty($row['action'])) {
                return (string) $row['action'];
            }
        }

        $live = WorkflowStepTransition::where('from_step_id', $fromStepId)
            ->where('to_step_id', $toStepId)
            ->value('action');

        return $live ?: 'Transférer';
    }

    private function captureCircuitTransitions(?string $type): array
    {
        $columns = ['from_step_id', 'to_step_id', 'action', 'is_urgent_only'];

        if ($type) {
            $typeIds = WorkflowStep::where('type_demande', $type)->pluck('id');
            if ($typeIds->isNotEmpty()) {
                $rows = WorkflowStepTransition::query()
                    ->whereIn('to_step_id', $typeIds)
                    ->where(function ($q) use ($typeIds) {
                        $q->whereIn('from_step_id', $typeIds)->orWhereNull('from_step_id');
                    })
                    ->get($columns)
                    ->toArray();

                if (!empty($rows)) {
                    return $rows;
                }
            }
        }

        $globalIds = WorkflowStep::whereNull('type_demande')->pluck('id');
        if ($globalIds->isEmpty()) {
            return [];
        }

        return WorkflowStepTransition::query()
            ->whereIn('to_step_id', $globalIds)
            ->where(function ($q) use ($globalIds) {
                $q->whereIn('from_step_id', $globalIds)->orWhereNull('from_step_id');
            })
            ->get($columns)
            ->toArray();
    }

    private function missingStepDocuments(Demande $demande): array
    {
        if (!$demande->current_step_id) {
            return [];
        }

        $required = StepRequiredDocument::forStep($demande->current_step_id, $demande->type);
        if ($required->isEmpty()) {
            return [];
        }

        $existingCollections = $demande->getMedia()->pluck('collection_name')->unique();

        return $required
            ->filter(fn ($req) => !$existingCollections->contains($req->document_type))
            ->pluck('label')
            ->all();
    }

    private DemandeInteraction $lastInteraction;
}
