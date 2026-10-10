<?php

namespace App\Services;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\RdvAgentAvailability;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class RencontreAvailabilityService
{
    public function __construct(
        private HaitiHolidayService $holidays,
    ) {
    }

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
            'rdv.start',
            self::DEFAULT_START_TIME
        );
    }

    public function endTime(): string
    {
        return (string) config(
            'rdv.end',
            self::DEFAULT_END_TIME
        );
    }

    public function dailyCapacity(): int
    {
        return max(
            1,
            (int) config(
                'rdv.slots_per_agent_per_day',
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
    public function isBookableDate(string $date): bool
    {
        try {
            $day = Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return false;
        }

        if ($day->isWeekend()) {
            return false;
        }

        if ($this->holidays->isHoliday($day)) {
            return false;
        }

        return !$day->isPast()
            || $day->isToday();
    }

    /**
     * Dates fériées haïtiennes (Y-m-d) sur une fenêtre glissante.
     *
     * @return array<int, array{date: string, name: string}>
     */
    public function holidayEvents(
        ?Carbon $from = null,
        ?Carbon $to = null
    ): array {
        $from ??= now()->startOfDay();
        $to ??= now()->copy()->addMonths(4)->endOfDay();

        return \App\Models\Holiday::query()
            ->haiti()
            ->whereBetween('date', [
                $from->toDateString(),
                $to->toDateString(),
            ])
            ->orderBy('date')
            ->get()
            ->map(fn ($h) => [
                'date' => $h->date->format('Y-m-d'),
                'name' => $h->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Créneaux unitaires (début → fin) alignés sur slot_minutes — pour l’UI agent.
     *
     * @return array<int, array{start: string, end: string, label: string}>
     */
    public function slotPresets(): array
    {
        $times = $this->allowedTimes();
        $minutes = $this->slotMinutes();
        $presets = [];

        foreach ($times as $start) {
            $end = Carbon::createFromFormat('H:i', $start)
                ->addMinutes($minutes)
                ->format('H:i');

            $presets[] = [
                'start' => $start,
                'end' => $end,
                'label' => $start.' – '.$end,
            ];
        }

        return $presets;
    }

    /**
     * L'agent a-t-il déclaré être disponible à cette date/heure ?
     *
     * Priorité : indisponibilités → dates précises → récurrence hebdomadaire.
     * Sans aucune règle : disponible sur la plage globale (config rdv).
     */
    public function agentIsAvailableAt(
        User $agent,
        string $date,
        string $time
    ): bool {
        if (!$this->isAllowedTime($time)) {
            return false;
        }

        try {
            $day = Carbon::parse($date);
        } catch (\Throwable) {
            return false;
        }

        $weekday = (int) $day->dayOfWeekIso;

        if ($weekday < 1 || $weekday > 5) {
            return false;
        }

        $normalized = Demande::normalizeRencontreTime($time);

        if (!$normalized) {
            return false;
        }

        $slotStart = Carbon::createFromFormat('H:i', $normalized);
        $slotEnd = $slotStart->copy()->addMinutes($this->slotMinutes());

        $rules = RdvAgentAvailability::query()
            ->where('user_id', $agent->id)
            ->get();

        if ($rules->isEmpty()) {
            return true;
        }

        foreach ($this->blockingRulesForDate($rules, $date, $weekday) as $window) {
            if ($this->slotOverlapsWindow($slotStart, $slotEnd, $window)) {
                return false;
            }
        }

        $dateRules = $this->availableRulesForExactDate($rules, $date);

        if ($dateRules->isNotEmpty()) {
            foreach ($dateRules as $window) {
                if ($this->slotFitsInWindow($slotStart, $slotEnd, $window)) {
                    return true;
                }
            }

            return false;
        }

        $recurring = $this->availableRecurringRulesForWeekday($rules, $weekday);

        if ($recurring->isEmpty()) {
            return true;
        }

        foreach ($recurring as $window) {
            if ($this->slotFitsInWindow($slotStart, $slotEnd, $window)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  Collection<int, RdvAgentAvailability>  $rules
     * @return Collection<int, RdvAgentAvailability>
     */
    private function blockingRulesForDate(
        Collection $rules,
        string $date,
        int $weekday
    ): Collection {
        return $rules->filter(function (RdvAgentAvailability $row) use ($date, $weekday) {
            if (!$row->is_blocked) {
                return false;
            }

            if ($row->available_on) {
                return $row->available_on->toDateString() === $date;
            }

            return (int) $row->weekday === $weekday;
        });
    }

    /**
     * @param  Collection<int, RdvAgentAvailability>  $rules
     * @return Collection<int, RdvAgentAvailability>
     */
    private function availableRulesForExactDate(
        Collection $rules,
        string $date
    ): Collection {
        return $rules->filter(
            fn (RdvAgentAvailability $row) => !$row->is_blocked
                && $row->available_on
                && $row->available_on->toDateString() === $date
        );
    }

    /**
     * @param  Collection<int, RdvAgentAvailability>  $rules
     * @return Collection<int, RdvAgentAvailability>
     */
    private function availableRecurringRulesForWeekday(
        Collection $rules,
        int $weekday
    ): Collection {
        return $rules->filter(
            fn (RdvAgentAvailability $row) => !$row->is_blocked
                && $row->available_on === null
                && (int) $row->weekday === $weekday
        );
    }

    private function slotFitsInWindow(
        Carbon $slotStart,
        Carbon $slotEnd,
        RdvAgentAvailability $window
    ): bool {
        [$start, $end] = $this->windowBounds($window);

        return $slotStart->gte($start) && $slotEnd->lte($end);
    }

    private function slotOverlapsWindow(
        Carbon $slotStart,
        Carbon $slotEnd,
        RdvAgentAvailability $window
    ): bool {
        [$start, $end] = $this->windowBounds($window);

        return $slotStart->lt($end) && $slotEnd->gt($start);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function windowBounds(RdvAgentAvailability $window): array
    {
        $startRaw = (string) $window->start_time;
        $endRaw = (string) $window->end_time;

        $start = Carbon::createFromFormat(
            'H:i:s',
            strlen($startRaw) === 5 ? $startRaw.':00' : $startRaw
        );

        $end = Carbon::createFromFormat(
            'H:i:s',
            strlen($endRaw) === 5 ? $endRaw.':00' : $endRaw
        );

        return [$start, $end];
    }

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
            RencontreStatutEnum::ACTIF->value,
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
        string $time,
        ?Demande $except = null
    ): bool {
        $normalized = Demande::normalizeRencontreTime($time);

        if (!$normalized) {
            return false;
        }

        return Demande::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->where(
                'data->date_souhaitee',
                $date
            )
            ->where(
                'data->heure_souhaitee',
                $normalized
            )
            ->where(
                'data->agent_id',
                $agent->id
            )
            ->when(
                $except,
                fn ($query) => $query->whereKeyNot($except->id)
            )
            ->get()
            ->contains(
                fn (Demande $demande) =>
                    $demande->rencontreStatut()->reservesSlot()
            );
    }

    /**
     * Compte les rendez-vous actifs d'un agent pour une journée.
     */
    public function dailyAppointmentCount(
        User $agent,
        string $date,
        ?Demande $except = null
    ): int {
        return Demande::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->where(
                'data->date_souhaitee',
                $date
            )
            ->where(
                'data->agent_id',
                $agent->id
            )
            ->when(
                $except,
                fn ($query) => $query->whereKeyNot($except->id)
            )
            ->get()
            ->filter(
                fn (Demande $demande) =>
                    $demande->rencontreStatut()->reservesSlot()
            )
            ->count();
    }

    /**
     * Rendez-vous actifs d’un agent sur la semaine ISO contenant $date.
     */
    public function weeklyAppointmentCount(
        User $agent,
        string $date,
        ?Demande $except = null
    ): int {
        try {
            $day = Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return 0;
        }

        $weekStart = $day->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $day->copy()->endOfWeek(Carbon::FRIDAY);

        return Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->whereBetween('data->date_souhaitee', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->where('data->agent_id', $agent->id)
            ->when(
                $except,
                fn ($query) => $query->whereKeyNot($except->id)
            )
            ->get()
            ->filter(
                fn (Demande $demande) =>
                    $demande->rencontreStatut()->reservesSlot()
            )
            ->count();
    }

    public function rotationEnabled(): bool
    {
        return (bool) config('rdv.rotation.enabled', true);
    }

    public function rotationUsesWeeklyLoad(): bool
    {
        return $this->rotationEnabled()
            && (bool) config('rdv.rotation.use_weekly_load', true);
    }

    /**
     * L’agent fait-il partie du pool de rotation pour ce service et cette date ?
     */
    public function agentInRotationPool(
        User $agent,
        Service $service,
        string $date
    ): bool {
        if (!$this->rotationEnabled()) {
            return true;
        }

        try {
            $day = Carbon::parse($date);
        } catch (\Throwable) {
            return false;
        }

        $weekday = (int) $day->dayOfWeekIso;

        if ($weekday < 1 || $weekday > 5) {
            return false;
        }

        $pools = config(
            'rdv.rotation.weekday_agent_ids.'.$service->code
        );

        if (!is_array($pools)) {
            return true;
        }

        $allowed = $pools[$weekday]
            ?? $pools[(string) $weekday]
            ?? null;

        if ($allowed === null || $allowed === []) {
            return true;
        }

        $ids = array_map('intval', (array) $allowed);

        return in_array((int) $agent->id, $ids, true);
    }

    /**
     * Agents du service éligibles à l’assignation automatique ou manuelle ce jour-là.
     *
     * @return Collection<int, User>
     */
    public function agentsForAssignment(
        Service $service,
        string $date
    ): Collection {
        return $this->bookingAgents($service)
            ->filter(
                fn (User $agent) => $this->agentInRotationPool(
                    $agent,
                    $service,
                    $date
                )
            )
            ->values();
    }

    /**
     * Agents pouvant recevoir le RDV (créneau actuel + rotation + dispos + charge).
     *
     * @return Collection<int, User>
     */
    public function agentsAssignableForDemande(
        Demande $demande,
        Service $service
    ): Collection {
        $data = $demande->data ?? [];
        $date = $data['date_souhaitee'] ?? null;
        $heure = $data['heure_souhaitee'] ?? null;

        if (!$date || !$heure) {
            return $this->bookingAgents($service);
        }

        return $this->agentsForAssignment($service, $date)
            ->filter(
                fn (User $agent) => $this->agentCanTakeSlot(
                    $agent,
                    $date,
                    $heure,
                    $demande,
                    $service
                )
            )
            ->values();
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
        if (!$this->isBookableDate($date)) {
            return false;
        }

        if (!$this->isAllowedTime($time)) {
            return false;
        }

        foreach ($this->agentsForAssignment($service, $date) as $agent) {
            if ($this->agentCanTakeSlot($agent, $date, $time, null, $service)) {
                return true;
            }
        }

        return false;
    }

    public function agentCanTakeSlot(
        User $agent,
        string $date,
        string $time,
        ?Demande $ignoreDemande = null,
        ?Service $service = null
    ): bool {
        $service ??= $agent->service_id
            ? Service::query()->find($agent->service_id)
            : null;

        if (
            !$service
            || !$this->isBookingAgent($agent, $service)
        ) {
            return false;
        }

        if (!$this->isBookableDate($date)) {
            return false;
        }

        if (!$this->agentInRotationPool($agent, $service, $date)) {
            return false;
        }

        if (!$this->agentIsAvailableAt($agent, $date, $time)) {
            return false;
        }

        if ($this->agentHasAppointmentAt($agent, $date, $time)) {
            if (
                !$ignoreDemande
                || !$this->isSameSlotForDemande(
                    $ignoreDemande,
                    $agent,
                    $date,
                    $time
                )
            ) {
                return false;
            }
        }

        $dailyCount = $this->dailyAppointmentCount(
            $agent,
            $date,
            $ignoreDemande
        );

        if ($dailyCount >= $this->dailyCapacity()) {
            if (
                !$ignoreDemande
                || !$this->isSameSlotForDemande(
                    $ignoreDemande,
                    $agent,
                    $date,
                    $time
                )
            ) {
                return false;
            }
        }

        return true;
    }

    private function isSameSlotForDemande(
        ?Demande $demande,
        User $agent,
        string $date,
        string $time
    ): bool {
        if (!$demande) {
            return false;
        }

        $data = $demande->data ?? [];

        return (int) ($data['agent_id'] ?? 0) === (int) $agent->id
            && ($data['date_souhaitee'] ?? null) === $date
            && Demande::normalizeRencontreTime(
                $data['heure_souhaitee'] ?? null
            ) === Demande::normalizeRencontreTime($time);
    }

    public function findAvailableAgent(
        string $date,
        string $time,
        Service $service,
        ?Demande $ignoreDemande = null
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
        |   2. Charge de la semaine ISO (rotation)
        |   3. ID croissant (départage déterministe)
        |
        | Pool : agents du service + rotation (config rdv.rotation)
        |
        | Puis on retourne le premier qui satisfait :
        |   - créneau exact libre
        |   - capacité journalière non atteinte
        |
        */

        $candidates = $this->agentsForAssignment($service, $date)
            ->map(function (User $agent) use ($date, $ignoreDemande) {
                return [
                    'agent' => $agent,
                    'load' => $this->dailyAppointmentCount(
                        $agent,
                        $date,
                        $ignoreDemande
                    ),
                    'weekly_load' => $this->rotationUsesWeeklyLoad()
                        ? $this->weeklyAppointmentCount(
                            $agent,
                            $date,
                            $ignoreDemande
                        )
                        : 0,
                ];
            })
            ->sortBy([
                ['load', 'asc'],
                ['weekly_load', 'asc'],
                ['agent.id', 'asc'],
            ]);

        foreach ($candidates as $candidate) {
            $agent = $candidate['agent'];

            if (!$this->agentCanTakeSlot(
                $agent,
                $date,
                $time,
                $ignoreDemande,
                $service
            )) {
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

    /**
     * Créneaux indisponibles pour un service (aucun agent libre).
     *
     * @return array<int, string> ex. ["2026-10-15 14:00", ...]
     */
    public function unavailableSlotsForService(
        Service $service,
        ?Carbon $from = null,
        ?Carbon $to = null
    ): array {
        $from ??= now()->startOfDay();
        $to ??= now()->copy()->addWeeks(8)->endOfDay();

        if ($this->bookingAgents($service)->isEmpty()) {
            return [];
        }

        $unavailable = [];
        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $dateStr = $cursor->toDateString();

            if (!$this->isBookableDate($dateStr)) {
                $cursor->addDay();

                continue;
            }

            $agents = $this->agentsForAssignment($service, $dateStr);

            foreach ($this->allowedTimes() as $time) {
                $hasAgent = false;

                foreach ($agents as $agent) {
                    if ($this->agentCanTakeSlot(
                        $agent,
                        $dateStr,
                        $time,
                        null,
                        $service
                    )) {
                        $hasAgent = true;
                        break;
                    }
                }

                if (!$hasAgent) {
                    $unavailable[] = $dateStr.' '.$time;
                }
            }

            $cursor->addDay();
        }

        return $unavailable;
    }

    /**
     * Message utilisateur lorsqu’un agent ne peut pas recevoir le créneau.
     */
    public function agentAssignmentBlockReason(
        User $agent,
        Service $service,
        string $date,
        string $time,
        ?Demande $ignoreDemande = null
    ): ?string {
        if (!$this->isBookableDate($date)) {
            return 'Cette date n’est pas ouvrée (week-end ou jour férié en Haïti).';
        }

        if (!$this->isAllowedTime($time)) {
            return 'L’heure choisie est en dehors des créneaux autorisés.';
        }

        if (!$this->isBookingAgent($agent, $service)) {
            return 'L’agent sélectionné n’appartient pas au service responsable.';
        }

        if (!$this->agentInRotationPool($agent, $service, $date)) {
            return 'Cet agent n’est pas dans la rotation prévue pour ce jour.';
        }

        if (!$this->agentIsAvailableAt($agent, $date, $time)) {
            return 'L’agent n’a pas déclaré de disponibilité sur ce créneau.';
        }

        if (
            $this->agentHasAppointmentAt(
                $agent,
                $date,
                $time,
                $ignoreDemande
            )
        ) {
            return 'Cet agent est déjà occupé sur ce créneau.';
        }

        if (
            $this->dailyAppointmentCount(
                $agent,
                $date,
                $ignoreDemande
            ) >= $this->dailyCapacity()
            && !$this->isSameSlotForDemande(
                $ignoreDemande,
                $agent,
                $date,
                $time
            )
        ) {
            return 'La capacité journalière de cet agent est atteinte.';
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function unavailableSlotsByServiceCode(): array
    {
        $byCode = [];

        foreach (Service::query()->orderBy('id')->get() as $service) {
            $byCode[$service->code] =
                $this->unavailableSlotsForService($service);
        }

        return $byCode;
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