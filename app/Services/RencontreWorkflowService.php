<?php

namespace App\Services;

use App\Enums\RencontreStatutEnum;
use App\Http\Controllers\DemandeRencontreController;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\DemandeHistory;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\RencontreAgentAssigneNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RencontreWorkflowService
{
    public function __construct(
        private RencontreAvailabilityService $availability,
        private RencontreVisioService $visio,
        private RencontreMotifService $motifsService,
    ) {}

    /**
     * Indique si le rendez-vous est actif.
     */
    public function estActif(Demande $demande): bool
    {
        return $this->statut($demande) === RencontreStatutEnum::ACTIF;
    }

    /**
     * Retourne le statut métier actuel du rendez-vous.
     */
    public function statut(
        Demande $demande
    ): RencontreStatutEnum {
        $stored = RencontreStatutEnum::tryFrom(
            (string) ($demande->data['rdv_statut'] ?? '')
        );

        return $stored
            ?? RencontreStatutEnum::fromWorkflow(
                $demande->currentStep?->code
            );
    }

    /**
     * Applique un changement de statut.
     *
     * @param array<string, mixed> $data
     * @param array<string, mixed> $champs
     */
    public function apply(
        Demande $demande,
        RencontreStatutEnum $statut,
        User $user,
        string $event,
        string $commentaire,
        array $data = [],
        array $champs = [],
    ): Demande {
        $step = WorkflowStep::forCode(
            $statut->workflowCode(),
            $demande->type
        );

        $currentCode = $demande->relationLoaded('currentStep')
            ? $demande->currentStep?->code
            : WorkflowStep::find(
                $demande->current_step_id
            )?->code;

        $payload = [
            'data' => array_merge(
                $demande->data ?? [],
                $data,
                [
                    'rdv_statut' => $statut->value,
                    'rdv_statut_at' => now()->toIso8601String(),
                ]
            ),
        ];

        if (
            $step
            && $currentCode !== $statut->workflowCode()
        ) {
            $payload['current_step_id'] = $step->id;
        }

        if ($statut === RencontreStatutEnum::DEMANDE) {
            $payload['submitted_at']
                = $demande->submitted_at ?? now();
        }

        $demande->update($payload);

        DemandeHistory::create([
            'demande_id' => $demande->id,
            'event' => $event,
            'statut' => $statut->value,
            'commentaire' => $commentaire,
            'changed_by' => $user->id,
            'champs' => $champs ?: null,
        ]);

        return $demande->fresh();
    }

    /**
     * Soumet une demande de rendez-vous.
     *
     * Le service responsable est déterminé à partir du motif.
     * L'agent est automatiquement sélectionné parmi les agents
     * du service responsable.
     *
     * Le lien de visioconférence est généré immédiatement si la
     * modalité est « visio » (Option A).
     */
    public function enregistrerSoumission(
        Demande $demande,
        User $user
    ): Demande {
        $service = $this->resolveResponsibleService($demande);

        $agent = $this->resolveBookingAgent(
            $demande,
            $service
        );

        $data = $demande->data ?? [];

        $data['service_id'] = $service->id;
        $data['service_code'] = $service->code;
        $data['service_responsable'] = $service->nom;
        $data['unite'] = $service->nom;

        $data['agent_id'] = $agent->id;
        $data['agent_nom'] = $agent->displayName();

        $demande->update([
            'current_service_id' => $service->id,
            'data' => $data,
        ]);

        /*
        |--------------------------------------------------------------------------
        | OPTION A — Génération du token visio dès la création
        |--------------------------------------------------------------------------
        |
        | Le token est créé immédiatement pour que :
        |   - le lien soit disponible dès la soumission,
        |   - le bloc « Suivi du rendez-vous » affiche un vrai lien cliquable,
        |   - la méthode confirmation() retourne l'URL réelle et non le repli.
        |
        | attachTo() est idempotent : il ne régénère pas un token existant.
        |
        */

        $demande = $demande->fresh();

        if (($data['modalite'] ?? '') === 'visio') {
            $demande = $this->visio->attachTo($demande);
        }

        $demande = $this->apply(
            $demande,
            RencontreStatutEnum::ACTIF,
            $user,
            'CREATED',
            'Rendez-vous enregistré et activé automatiquement (réf. '
                . $demande->code
                . '). Service responsable : '
                . $service->nom
                . '.',
            [
                'service_id' => $service->id,
                'service_code' => $service->code,
                'service_responsable' => $service->nom,
                'unite' => $service->nom,
                'agent_id' => $agent->id,
                'agent_nom' => $agent->displayName(),
            ],
            [
                'action' => 'creation',
                'service_id' => $service->id,
                'service_code' => $service->code,
                'agent_id' => $agent->id,
            ]
        );

        $demande = $demande->fresh();

        $this->notifyAssignedAgent(
            $demande,
            $user
        );

        return $demande;
    }

    /**
     * Examen de la demande.
     */
    public function examiner(
        Demande $demande,
        User $user
    ): Demande {
        $this->assertOpen($demande);

        return $this->estActif($demande)
            ? $demande->fresh()
            : $this->valider($demande, $user);
    }

    /**
     * Attribution du créneau.
     */
    public function attribuer(
        Demande $demande,
        User $user,
        ?string $commentaire = null
    ): Demande {
        $this->assertOpen($demande);

        return $this->estActif($demande)
            ? $demande->fresh()
            : $this->valider($demande, $user);
    }

    /**
     * Propose ou modifie un créneau.
     *
     * @param array{
     *     date_souhaitee: string,
     *     heure_souhaitee: string,
     *     agent_id?: int,
     *     lieu_rdv?: string|null
     * } $creneau
     */
    public function proposerCreneau(
        Demande $demande,
        User $user,
        array $creneau,
        bool $report = false
    ): Demande {
        $this->assertOpen($demande);

        $date = $creneau['date_souhaitee'];
        $heure = $creneau['heure_souhaitee'];

        $service = $this->resolveResponsibleService(
            $demande
        );

        if (!$this->availability->isAllowedTime($heure)) {
            throw ValidationException::withMessages([
                'heure_souhaitee' =>
                    'Choisissez un créneau de 15 minutes entre 14h00 et 16h00.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Agent
        |--------------------------------------------------------------------------
        */

        $agentId = (int) (
            $creneau['agent_id']
            ?? $demande->data['agent_id']
            ?? 0
        );

        $agent = $agentId
            ? User::find($agentId)
            : null;

        if ($agentId && !$agent) {
            throw ValidationException::withMessages([
                'agent_id' =>
                    'L’agent sélectionné est introuvable.',
            ]);
        }

        if (
            $agent
            && !$this->availability->isBookingAgent(
                $agent,
                $service
            )
        ) {
            throw ValidationException::withMessages([
                'agent_id' =>
                    'L’agent sélectionné n’appartient pas au service responsable de cette demande.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Si aucun agent n'est imposé, sélection automatique
        |--------------------------------------------------------------------------
        */

        if (!$agent) {
            $agent = $this->availability->findAvailableAgent(
                $date,
                $heure,
                $service
            );

            if (!$agent) {
                throw ValidationException::withMessages([
                    'heure_souhaitee' =>
                        'Aucun agent disponible dans le service responsable pour ce créneau.',
                ]);
            }

            $agentId = $agent->id;
        }

        /*
        |--------------------------------------------------------------------------
        | Vérification de disponibilité de l'agent
        |--------------------------------------------------------------------------
        */

        $ancienneDate = $demande->data['date_souhaitee'] ?? null;

        $ancienneHeure = !empty(
            $demande->data['heure_souhaitee'] ?? null
        )
            ? Demande::normalizeRencontreTime(
                $demande->data['heure_souhaitee']
            )
            : null;

        $nouvelleHeure = Demande::normalizeRencontreTime(
            $heure
        );

        $memeCreneau = (
            $ancienneDate === $date
            && $ancienneHeure === $nouvelleHeure
            && (int) ($demande->data['agent_id'] ?? 0)
                === (int) $agent->id
        );

        if (
            !$memeCreneau
            && $this->availability->agentHasAppointmentAt(
                $agent,
                $date,
                $heure
            )
        ) {
            throw ValidationException::withMessages([
                'heure_souhaitee' =>
                    'Cet agent est déjà occupé sur ce créneau.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Capacité journalière de l'agent
        |--------------------------------------------------------------------------
        */

        if (!$memeCreneau) {
            $dailyCount = $this->availability->dailyAppointmentCount(
                $agent,
                $date
            );

            if (
                $dailyCount
                >= $this->availability->dailyCapacity()
            ) {
                throw ValidationException::withMessages([
                    'date_souhaitee' =>
                        'La capacité journalière de cet agent est atteinte.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Disponibilité générale du service
        |--------------------------------------------------------------------------
        */

        if (!$memeCreneau) {
            if (
                !$this->availability->isBookableSlot(
                    $date,
                    $heure,
                    $service
                )
            ) {
                throw ValidationException::withMessages([
                    'heure_souhaitee' =>
                        'Ce créneau n’est plus disponible pour le service responsable de cette demande.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Données sauvegardées
        |--------------------------------------------------------------------------
        */

        $payload = [
            'date_souhaitee' => $date,

            'heure_souhaitee' => $nouvelleHeure,

            'agent_id' => $agent->id,

            'agent_nom' => $agent->displayName(),

            'service_id' => $service->id,

            'service_code' => $service->code,

            'service_responsable' => $service->nom,

            'unite' => $service->nom,
        ];

        if (
            array_key_exists(
                'lieu_rdv',
                $creneau
            )
            && filled($creneau['lieu_rdv'])
        ) {
            $payload['lieu_rdv'] = $creneau['lieu_rdv'];
        }

        /*
        |--------------------------------------------------------------------------
        | Statut et événement
        |--------------------------------------------------------------------------
        */

        $statut = $report
            ? RencontreStatutEnum::REPORTE
            : RencontreStatutEnum::ACTIF;

        $event = $report
            ? 'REPORTED'
            : 'MODIFIED';

        $commentaire = $report
            ? 'Rendez-vous reporté au '
                . $date
                . ' à '
                . $payload['heure_souhaitee']
                . ' avec '
                . $agent->displayName()
                . ' ('
                . $service->nom
                . ').'
            : 'Nouveau créneau proposé : '
                . $date
                . ' à '
                . $payload['heure_souhaitee']
                . ' avec '
                . $agent->displayName()
                . ' ('
                . $service->nom
                . ').';

        $updated = $this->apply(
            $demande,
            $statut,
            $user,
            $event,
            $commentaire,
            $payload,
            [
                'action' => $report
                    ? 'report'
                    : 'modification',

                'creneau' => $payload,

                'service_id' => $service->id,

                'agent_id' => $agent->id,
            ]
        );

        // Garantir la présence du token visio après changement de créneau
        if (($updated->data['modalite'] ?? '') === 'visio') {
            $updated = $this->visio->attachTo($updated);
        }

        return $updated;
    }

    /**
     * Activation automatique du rendez-vous.
     */
    public function valider(
        Demande $demande,
        User $user
    ): Demande {
        $this->assertOpen($demande);

        if ($this->estActif($demande)) {
            // S'assurer que le token visio existe même si le RDV
            // a été activé par un autre chemin (ex : création directe).
            if (($demande->data['modalite'] ?? '') === 'visio'
                && blank($demande->visio_token)
            ) {
                $demande = $this->visio->attachTo($demande->fresh());
            }

            return $demande->fresh();
        }

        $service = $this->resolveResponsibleService($demande);
        $demande = $this->visio->attachTo($demande->fresh());
        $confirmation = $this->confirmation($demande);

        return $this->apply(
            $demande,
            RencontreStatutEnum::ACTIF,
            $user,
            'ACTIVATED',
            'Rendez-vous activé automatiquement.',
            [
                'active_par' => $user->id,
                'active_at' => now()->toIso8601String(),
                'confirmation' => $confirmation,
                'service_responsable' => $service->nom,
                'service_id' => $service->id,
            ],
            [
                'action' => 'activation',
                'service_id' => $service->id,
            ]
        );
    }

    /**
     * Clôture du rendez-vous.
     */
    public function clore(
        Demande $demande,
        User $user,
        RencontreStatutEnum $statut,
        ?string $commentaire = null
    ): Demande {
        $this->assertOpen($demande);

        if (
            !in_array(
                $statut,
                [
                    RencontreStatutEnum::REALISE,
                    RencontreStatutEnum::NON_HONORE,
                ],
                true
            )
        ) {
            abort(
                422,
                'Clôture invalide.'
            );
        }

        $updated = $this->apply(
            $demande,
            $statut,
            $user,
            'CLOSED',
            $commentaire
                ?: 'Clôture du rendez-vous : '
                . $statut->label()
                . '.',
            [
                'cloture_par' =>
                    $user->id,

                'cloture_at' =>
                    now()->toIso8601String(),
            ],
            [
                'action' => 'cloture',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Création de compte
        |--------------------------------------------------------------------------
        */

        if (
            $statut === RencontreStatutEnum::REALISE
        ) {
            $owner =
                $updated->relationLoaded('user')
                    ? $updated->user
                    : User::find(
                        $updated->created_by
                    );

            if (
                $owner?->isProvisionnel()
            ) {
                $compte =
                    DemandeCreationCompte::query()
                        ->where(
                            'user_id',
                            $owner->id
                        )
                        ->where(
                            'status',
                            DemandeCreationCompte::STATUS_EN_ATTENTE
                        )
                        ->latest()
                        ->first();

                if ($compte) {
                    $service =
                        $this->resolveResponsibleService(
                            $updated
                        );

                    $compte->recordHistory(
                        DemandeCreationCompteHistory::EVENT_RDV_REALISE,
                        'Rendez-vous réalisé ('
                            . $updated->code
                            . '). En attente de décision du service responsable : '
                            . $service->nom
                            . '.',
                        $user,
                        DemandeCreationCompte::STATUS_EN_ATTENTE,
                        [
                            'rdv_id' =>
                                $updated->id,

                            'rdv_code' =>
                                $updated->code,

                            'service_id' =>
                                $service->id,

                            'service_code' =>
                                $service->code,
                        ]
                    );
                }
            }
        }

        return $updated;
    }

    /**
     * Annulation.
     */
    public function annuler(
        Demande $demande,
        User $user,
        ?string $motif = null
    ): Demande {
        $this->assertOpen($demande);

        $data = $demande->data ?? [];
        $demandeurId = (int) $demande->created_by;
        $agentId = (int) ($data['agent_id'] ?? 0);

        $isDemandeur = (int) $user->id === $demandeurId;
        $isAgentRdv = $agentId > 0 && (int) $user->id === $agentId;
        $isAdmin = $user->hasAnyRole(['admin', 'direction']);

        abort_unless(
            $isDemandeur || $isAgentRdv || $isAdmin,
            403,
            'Seul le demandeur, l’agent RDV affecté ou un administrateur peut annuler ce rendez-vous.'
        );

        // Motif obligatoire pour un agent RDV ou un administrateur
        if (($isAgentRdv || $isAdmin) && blank($motif)) {
            throw ValidationException::withMessages([
                'motif' => 'Le motif d’annulation est obligatoire.',
            ]);
        }

        $acteur = match (true) {
            $isAgentRdv => 'agent_rdv',
            $isAdmin => 'administrateur',
            default => 'demandeur',
        };

        $motif = filled($motif) ? trim((string) $motif) : null;

        return $this->apply(
            $demande,
            RencontreStatutEnum::ANNULE,
            $user,
            'CANCELED',
            match ($acteur) {
                'agent_rdv' => 'Rendez-vous annulé par l’agent RDV. Motif : ' . $motif,
                'administrateur' => 'Rendez-vous annulé par un administrateur. Motif : ' . $motif,
                default => 'Rendez-vous annulé par le demandeur.'
                    . ($motif ? ' Motif : ' . $motif : ''),
            },
            [
                'annule_par' => $user->id,
                'annule_par_type' => $acteur,
                'annule_at' => now()->toIso8601String(),
                'motif_annulation' => $motif,
            ],
            [
                'action' => 'annulation',
                'acteur' => $acteur,
                'motif_obligatoire' => $isAgentRdv || $isAdmin,
            ]
        );
    }

    /**
     * Génère les données de confirmation.
     *
     * @return array<string, mixed>
     */
    public function confirmation(
        Demande $demande
    ): array {
        $data = $demande->data ?? [];

        $modalite =
            ($data['modalite'] ?? 'visio')
                === 'physique'
                ? 'physique'
                : 'visio';

        $existing =
            is_array(
                $data['confirmation'] ?? null
            )
                ? $data['confirmation']
                : [];

        $service = $demande->service;

        return array_merge(
            $existing,
            [
                'numero' =>
                    $demande->code,

                'date' =>
                    $data['date_souhaitee']
                    ?? null,

                'heure' =>
                    Demande::normalizeRencontreTime(
                        $data['heure_souhaitee']
                        ?? null
                    ),

                'service' =>
                    $data['service_responsable']
                    ?? $service?->nom
                    ?? 'Service responsable non défini',

                'mode' =>
                    $modalite === 'physique'
                        ? 'Présentiel'
                        : 'Visioconférence',

                'lieu' =>
                    $modalite === 'physique'
                        ? ($data['lieu_rdv'] ?? '—')
                        : null,

                'lien' =>
                    $modalite === 'visio'
                    && $demande->visio_token
                        ? route(
                            'demandes.rencontre.visio',
                            $demande->visio_token
                        )
                        : (
                            $modalite === 'visio'
                                ? 'Lien sécurisé généré à la validation'
                                : null
                        ),

                'pieces' =>
                    $data['documents_a_preparer']
                    ?? DemandeRencontreController::DOCUMENTS_A_PREPARER[
                        $modalite
                    ],
            ]
        );
    }

    /**
     * Libellé d'un événement.
     */
    public function eventLabel(
        string $event
    ): string {
        return match ($event) {
            'CREATED',
            'SUBMITTED' => 'Création',

            'EXAMINED' => 'Examen',

            'ATTRIBUTED' => 'Attribution',

            'ACTIVATED',
            'APPROVED' => 'Validation',

            'MODIFIED' => 'Modification',

            'REPORTED' => 'Report',

            'CANCELED' => 'Annulation',

            'REJECTED' => 'Refus',

            'CLOSED' => 'Clôture',

            'REORIENTED' => 'Réorientation',

            'SUITE' => 'Suite donnée',

            'IDENTITY_CONFIRMED' =>
                'Identité confirmée',

            default => $event,
        };
    }

    /**
     * Vérifie que le rendez-vous est encore ouvert.
     */
    private function assertOpen(
        Demande $demande
    ): void {
        abort_unless(
            $demande->isRencontre(),
            404
        );

        abort_if(
            $this->statut($demande)->isTerminal(),
            422,
            'Ce rendez-vous est déjà clos.'
        );
    }

    /**
     * Détermine le service responsable.
     *
     * Priorité :
     *
     * 1. current_service_id
     * 2. motif (clé technique motif_key, puis libellé motif)
     *
     * Aucun service n'est imposé en dur.
     */
    private function resolveResponsibleService(
        Demande $demande
    ): Service {
        $demande->loadMissing([
            'service',
            'user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Service déjà affecté
        |--------------------------------------------------------------------------
        */

        if ($demande->service) {
            return $demande->service;
        }

        /*
        |--------------------------------------------------------------------------
        | Détermination par motif
        |--------------------------------------------------------------------------
        */

        $motif =
            $demande->data['motif_key']
            ?? $demande->data['motif']
            ?? null;

        if (!$motif) {
            throw ValidationException::withMessages([
                'motif' =>
                    'Le motif de la demande est obligatoire pour déterminer le service responsable.',
            ]);
        }

        $service = $this->motifsService->resolve(
            $motif,
            $demande->user
        );

        if (!$service) {
            throw ValidationException::withMessages([
                'motif' =>
                    'Aucun service responsable n’a été défini pour le motif sélectionné.',
            ]);
        }

        return $service;
    }

    /**
     * Détermine l'agent du service responsable.
     *
     * Si un agent est déjà enregistré et appartient au service,
     * il est conservé.
     *
     * Sinon, un agent disponible est recherché pour le créneau
     * enregistré dans la demande.
     */
    private function resolveBookingAgent(
        Demande $demande,
        Service $service
    ): User {
        $data = $demande->data ?? [];

        $agentId = (int) (
            $data['agent_id'] ?? 0
        );

        /*
        |--------------------------------------------------------------------------
        | Agent déjà affecté
        |--------------------------------------------------------------------------
        */

        if ($agentId) {
            $agent = User::find($agentId);

            if (
                $agent
                && $this->availability->isBookingAgent(
                    $agent,
                    $service
                )
            ) {
                return $agent;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Recherche selon le créneau demandé
        |--------------------------------------------------------------------------
        */

        $date = $data['date_souhaitee'] ?? null;
        $heure = $data['heure_souhaitee'] ?? null;

        if ($date && $heure) {
            $agent = $this->availability->findAvailableAgent(
                $date,
                $heure,
                $service
            );

            if ($agent) {
                return $agent;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Aucun agent disponible
        |--------------------------------------------------------------------------
        */

        throw ValidationException::withMessages([
            'agent_id' =>
                'Aucun agent de rendez-vous disponible pour le service responsable « '
                . $service->nom
                . ' » sur le créneau demandé.',
        ]);
    }

    /**
     * Notification de l'agent.
     */
    private function notifyAssignedAgent(
        Demande $demande,
        User $usager
    ): void {
        $agent = User::find(
            $demande->data['agent_id'] ?? 0
        );

        if (
            !$agent
            || (int) $agent->id === (int) $usager->id
        ) {
            return;
        }

        try {
            $agent->notify(
                new RencontreAgentAssigneNotification(
                    $demande
                )
            );
        } catch (\Throwable $e) {
            Log::error(
                'RencontreWorkflowService: could not notify assigned agent',
                [
                    'agent_id' =>
                        $agent->id,

                    'demande_id' =>
                        $demande->id,

                    'error' =>
                        $e->getMessage(),
                ]
            );
        }
    }
}