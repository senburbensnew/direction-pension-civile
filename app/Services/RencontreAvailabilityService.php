<?php

namespace App\Services;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class RencontreAvailabilityService
{
    /*
    |--------------------------------------------------------------------------
    | Configuration par défaut
    |--------------------------------------------------------------------------
    */

    public const DEFAULT_START_TIME = '14:00';

    public const DEFAULT_END_TIME = '16:00';

    public const DEFAULT_SLOT_MINUTES = 15;

    public const DEFAULT_DAILY_CAPACITY = 8;

    /**
     * Rôle des agents pouvant gérer les rendez-vous.
     */
    public const DEFAULT_AGENT_ROLE = 'agent_rdv';

    /*
    |--------------------------------------------------------------------------
    | Configuration
    |--------------------------------------------------------------------------
    */

    public function startTime(): string
    {
        return (string) config(
            'rdv.start',            // ← avant : 'rdv.start_time'
            self::DEFAULT_START_TIME
        );
    }

    public function endTime(): string
    {
        return (string) config(
            'rdv.end',              // ← avant : 'rdv.end_time'
            self::DEFAULT_END_TIME
        );
    }

    public function dailyCapacity(): int
    {
        return max(
            1,
            (int) config(
                'rdv.slots_per_agent_per_day',   // ← avant : 'rdv.daily_capacity'
                self::DEFAULT_DAILY_CAPACITY
            )
        );
    }

    public function slotMinutes(): int
    {
        return max(
            1,
            (int) config(
                'rdv.slot_minutes',
                self::DEFAULT_SLOT_MINUTES
            )
        );
    }

    /**
     * Alias conservé pour compatibilité avec d'autres services.
     */
    public function maxPerAgentPerDay(): int
    {
        return $this->dailyCapacity();
    }

    public function agentRole(): string
    {
        return (string) config(
            'rdv.agent_role',
            self::DEFAULT_AGENT_ROLE
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Créneaux
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne tous les horaires autorisés.
     *
     * Exemple :
     * 14:00
     * 14:15
     * 14:30
     * ...
     * 15:45
     */
    public function allowedTimes(): array
    {
        $start = Carbon::createFromFormat(
            'H:i',
            $this->startTime()
        );

        $end = Carbon::createFromFormat(
            'H:i',
            $this->endTime()
        );

        $times = [];

        while ($start->lt($end)) {
            $times[] = $start->format('H:i');

            $start->addMinutes(
                $this->slotMinutes()
            );
        }

        return $times;
    }

    /**
     * Vérifie si une heure fait partie des créneaux autorisés.
     */
    public function isAllowedTime(?string $time): bool
    {
        if (!$time) {
            return false;
        }

        try {
            $normalized = Carbon::createFromFormat(
                'H:i',
                $time
            )->format('H:i');
        } catch (\Throwable) {
            return false;
        }

        return in_array(
            $normalized,
            $this->allowedTimes(),
            true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Agents
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne uniquement les agents de rendez-vous
     * appartenant au service responsable.
     *
     * Règle :
     *
     * Motif
     *   ↓
     * Service responsable
     *   ↓
     * Agents de ce service
     */
    public function bookingAgents(Service $service): Collection
    {
        return User::query()
            ->role($this->agentRole())
            ->where('service_id', $service->id)
            ->orderBy('id')
            ->get();
    }

    /**
     * Retourne tous les agents de rendez-vous.
     *
     * Utilisé uniquement pour le pilotage / administration.
     */
    public function allBookingAgents(): Collection
    {
        return User::query()
            ->role($this->agentRole())
            ->with('service')
            ->orderBy('service_id')
            ->orderBy('id')
            ->get();
    }

    /**
     * Vérifie qu'un utilisateur est bien un agent de rendez-vous
     * appartenant au service indiqué.
     */
    public function isBookingAgent(
        ?User $user,
        Service $service
    ): bool {
        if (!$user) {
            return false;
        }

        return $user->hasRole($this->agentRole())
            && (int) $user->service_id === (int) $service->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Rendez-vous actifs
    |--------------------------------------------------------------------------
    */

    /**
     * Retourne les statuts qui occupent réellement un créneau.
     */
    private function activeAppointmentStatuses(): array
    {
        return [
            RencontreStatutEnum::DEMANDE->value,
            RencontreStatutEnum::EN_COURS->value,
            RencontreStatutEnum::ATTRIBUE->value,
            RencontreStatutEnum::VALIDE->value,
            RencontreStatutEnum::REPORTE->value,
        ];
    }

    /**
     * Vérifie si un agent possède déjà un rendez-vous
     * sur un créneau donné.
     */
    public function agentHasAppointmentAt(
        User $agent,
        string $date,
        string $time
    ): bool {
        return Demande::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->whereIn(
                'data->rdv_statut',
                $this->activeAppointmentStatuses()
            )
            ->where(
                'data->date_souhaitee',
                $date
            )
            ->where(
                'data->heure_souhaitee',
                Demande::normalizeRencontreTime($time)
            )
            ->where(
                'data->agent_id',
                $agent->id
            )
            ->exists();
    }

    /**
     * Compte les rendez-vous actifs d'un agent pour une journée.
     */
    public function dailyAppointmentCount(
        User $agent,
        string $date
    ): int {
        return Demande::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->whereIn(
                'data->rdv_statut',
                $this->activeAppointmentStatuses()
            )
            ->where(
                'data->date_souhaitee',
                $date
            )
            ->where(
                'data->agent_id',
                $agent->id
            )
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Disponibilité
    |--------------------------------------------------------------------------
    */

    /**
     * Vérifie si le service possède au moins un agent disponible
     * pour le créneau demandé.
     */
    public function isSlotAvailable(
        string $date,
        string $time,
        Service $service
    ): bool {
        if (!$this->isAllowedTime($time)) {
            return false;
        }

        foreach ($this->bookingAgents($service) as $agent) {
            if (
                !$this->agentHasAppointmentAt(
                    $agent,
                    $date,
                    $time
                )
                && $this->dailyAppointmentCount(
                    $agent,
                    $date
                ) < $this->dailyCapacity()
            ) {
                return true;
            }
        }

        return false;
    }

    public function findAvailableAgent(
        string $date,
        string $time,
        Service $service
    ): ?User {
        if (!$this->isAllowedTime($time)) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Équilibrage de charge journalier (least-loaded)
        |--------------------------------------------------------------------------
        |
        | On trie les agents candidats par :
        |
        |   1. Charge du jour croissante
        |   2. ID croissant (départage déterministe)
        |
        | Puis on retourne le premier qui satisfait :
        |   - créneau exact libre
        |   - capacité journalière non atteinte
        |
        */

        $candidates = $this->bookingAgents($service)
            ->map(function (User $agent) use ($date) {
                return [
                    'agent' => $agent,
                    'load'  => $this->dailyAppointmentCount(
                        $agent,
                        $date
                    ),
                ];
            })
            ->sortBy([
                ['load', 'asc'],
                ['agent.id', 'asc'],
            ]);

        foreach ($candidates as $candidate) {
            $agent = $candidate['agent'];

            if ($this->agentHasAppointmentAt($agent, $date, $time)) {
                continue;
            }

            if ($candidate['load'] >= $this->dailyCapacity()) {
                continue;
            }

            return $agent;
        }

        return null;
    }

    /**
     * Vérifie complètement si un créneau peut être réservé
     * pour un service donné.
     */
    public function isBookableSlot(
        string $date,
        string $time,
        Service $service
    ): bool {
        return $this->isSlotAvailable(
            $date,
            $time,
            $service
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Routage
    |--------------------------------------------------------------------------
    */

    /**
     * Affecte une demande à son service responsable et à son agent.
     *
     * Aucun service n'est codé en dur ici.
     */
    public function routeToResponsibleService(
        Demande $demande,
        Service $service,
        User $agent,
        User $by,
        DemandeWorkflowService $workflow
    ): void {
        if (!$this->isBookingAgent($agent, $service)) {
            throw new InvalidArgumentException(
                'L’agent sélectionné n’appartient pas au service responsable.'
            );
        }

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

        $workflow->assignerAgent(
            $demande,
            $agent,
            $by
        );
    }
}