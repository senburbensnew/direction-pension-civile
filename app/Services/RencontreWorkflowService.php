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
    ) {
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

        $demande = $this->apply(
            $demande,
            RencontreStatutEnum::DEMANDE,
            $user,
            'CREATED',
            'Demande de rendez-vous enregistrée (réf. '
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

        $service = $this->resolveResponsibleService(
            $demande
        );

        return $this->apply(
            $demande,
            RencontreStatutEnum::EN_COURS,
            $user,
            'EXAMINED',
            'Examen de la demande : motif, informations et pièces jointes vérifiés par le service responsable.',
            [
                'examine_par' => $user->id,
                'examine_at' => now()->toIso8601String(),
                'service_examen' => $service->nom,
            ],
            [
                'action' => 'examen',
                'service_id' => $service->id,
            ]
        );
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

        $data = $demande->data ?? [];

        if (
            empty($data['date_souhaitee'])
            || empty($data['heure_souhaitee'])
        ) {
            throw ValidationException::withMessages([
                'date_souhaitee' =>
                    'Aucun créneau à attribuer. Proposez une date et une heure.',
            ]);
        }

        return $this->apply(
            $demande,
            RencontreStatutEnum::ATTRIBUE,
            $user,
            'ATTRIBUTED',
            $commentaire
                ?: 'Créneau attribué automatiquement par le portail : '
                . $data['date_souhaitee']
                . ' à '
                . $data['heure_souhaitee']
                . '.',
            [
                'attribue_par' => $user->id,
                'attribue_at' => now()->toIso8601String(),
            ],
            [
                'action' => 'attribution',
            ]
        );
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
            /*
             * Lorsque l'agent a été explicitement sélectionné,
             * les vérifications ci-dessus suffisent.
             *
             * Sinon, le service doit posséder au moins un agent
             * disponible.
             */
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
        | Statut
        |--------------------------------------------------------------------------
        */

        $statut =
            $report
            || $this->statut($demande)
                === RencontreStatutEnum::VALIDE
                ? RencontreStatutEnum::REPORTE
                : RencontreStatutEnum::ATTRIBUE;

        return $this->apply(
            $demande,
            $statut,
            $user,
            $report
                || $statut === RencontreStatutEnum::REPORTE
                ? 'REPORTED'
                : 'MODIFIED',
            'Nouveau créneau proposé : '
                . $date
                . ' à '
                . $payload['heure_souhaitee']
                . ' avec '
                . $agent->displayName()
                . ' ('
                . $service->nom
                . ').',
            $payload,
            [
                'action' =>
                    $statut === RencontreStatutEnum::REPORTE
                        ? 'report'
                        : 'modification',

                'creneau' => $payload,

                'service_id' => $service->id,

                'agent_id' => $agent->id,
            ]
        );
    }

    /**
     * Validation définitive.
     */
    public function valider(
        Demande $demande,
        User $user
    ): Demande {
        $this->assertOpen($demande);

        $service = $this->resolveResponsibleService(
            $demande
        );

        $statut = $this->statut($demande);

        if (
            in_array(
                $statut,
                [
                    RencontreStatutEnum::DEMANDE,
                    RencontreStatutEnum::EN_COURS,
                ],
                true
            )
        ) {
            if (
                $statut === RencontreStatutEnum::DEMANDE
            ) {
                $demande = $this->examiner(
                    $demande,
                    $user
                );
            }

            $demande = $this->attribuer(
                $demande,
                $user
            );
        }

        $demande = $this->visio->attachTo(
            $demande->fresh()
        );

        $confirmation = $this->confirmation(
            $demande
        );

        return $this->apply(
            $demande,
            RencontreStatutEnum::VALIDE,
            $user,
            'VALIDATED',
            'Rendez-vous validé définitivement par le service responsable : '
                . $service->nom
                . '.',
            [
                'valide_par' => $user->id,

                'valide_at' =>
                    now()->toIso8601String(),

                'confirmation' => $confirmation,

                'service_responsable' =>
                    $service->nom,

                'service_id' =>
                    $service->id,
            ],
            [
                'action' => 'validation',

                'service_id' =>
                    $service->id,
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
        return $this->apply(
            $demande,
            RencontreStatutEnum::ANNULE,
            $user,
            'CANCELED',
            'Rendez-vous annulé.'
                . (
                    $motif
                        ? ' Motif : ' . $motif
                        : ''
                ),
            [
                'annule_par' =>
                    $user->id,

                'annule_at' =>
                    now()->toIso8601String(),
            ],
            [
                'action' => 'annulation',
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

            'VALIDATED',
            'APPROVED' => 'Validation',

            'MODIFIED' => 'Modification',

            'REPORTED' => 'Report',

            'CANCELED',
            'REJECTED' => 'Annulation',

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
     * 2. motif
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
        |
        | Une réorientation administrative explicite doit être conservée.
        |
        */

        if ($demande->service) {
            return $demande->service;
        }

        /*
        |--------------------------------------------------------------------------
        | Détermination par motif
        |--------------------------------------------------------------------------
        */

        $motif = $demande->data['motif'] ?? null;

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