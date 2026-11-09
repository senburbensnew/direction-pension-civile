<?php

namespace App\Services;

use App\Enums\RencontreRappelTimingEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\RappelRencontreAppelNotification;
use App\Notifications\RappelRencontreProgrammeNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class RencontreReminderService
{
    public function reminderDate(?Carbon $from = null): string
    {
        $days = (int) config('rdv.reminder.days_before', 1);

        return ($from ?? now())->addDays($days)->toDateString();
    }

    /**
     * @return Collection<int, Demande>
     */
    public function appointmentsOn(string $date): Collection
    {
        $cancelledIds = WorkflowStep::query()
            ->whereIn('code', ['REJETEE', 'ANNULEE'])
            ->pluck('id');

        return Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->when(
                $cancelledIds->isNotEmpty(),
                fn ($query) => $query->where(function ($query) use ($cancelledIds) {
                    $query->whereNull('current_step_id')
                        ->orWhereNotIn('current_step_id', $cancelledIds);
                })
            )
            ->get()
            ->filter(function (Demande $demande) use ($date) {
                $slot = $demande->data['date_souhaitee'] ?? null;

                return $slot && Carbon::parse($slot)->toDateString() === $date;
            })
            ->sortBy(fn (Demande $demande) => Demande::normalizeRencontreTime(
                $demande->data['heure_souhaitee'] ?? '99:99'
            ))
            ->values();
    }

    /**
     * @return Collection<int, Demande>
     */
    public function dueForPhoneReminder(?Carbon $from = null): Collection
    {
        return $this->appointmentsOn($this->reminderDate($from))
            ->filter(fn (Demande $demande) => empty($demande->data['rappel_veille_envoye_at']))
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    public function recipientsFor(Demande $demande): Collection
    {
        $recipients = User::role((string) config('rdv.agent_role', User::ROLE_AGENT_RDV))->get();

        $assignedId = $demande->assignments()->whereNull('ended_at')->value('user_id');
        if ($assignedId) {
            $assigned = User::find($assignedId);
            if ($assigned) {
                $recipients->push($assigned);
            }
        }

        return $recipients->unique('id')->values();
    }

    public function dispatch(?Carbon $from = null): int
    {
        $count = 0;

        foreach ($this->dueForPhoneReminder($from) as $demande) {
            foreach ($this->recipientsFor($demande) as $user) {
                $user->notify(new RappelRencontreAppelNotification($demande));
            }

            $demande->update([
                'data' => array_merge($demande->data ?? [], [
                    'rappel_veille_envoye_at' => now()->toIso8601String(),
                    'rappel_veille_canal' => 'appel_telephonique',
                    'rappel_veille_service' => config('rdv.reminder.service_label', 'Formalités'),
                ]),
            ]);

            $count++;
        }

        return $count;
    }

    public function appointmentsOnForAgent(string $date, int $agentId): \Illuminate\Support\Collection
    {
        return Demande::query()
            ->where('type', \App\Enums\TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->where('data->date_souhaitee', $date)
            ->where('data->agent_id', $agentId)
            ->whereNotIn('data->rdv_statut', \App\Enums\RencontreStatutEnum::terminalValues())
            ->orderBy('data->heure_souhaitee')
            ->get();
    }

    public function dispatchScheduledReminders(?Carbon $now = null): int
    {
        $now = $now ?? now();
        $count = 0;

        foreach (RencontreRappelTimingEnum::cases() as $timing) {
            foreach ($this->dueForScheduledReminder($timing, $now) as $demande) {
                $notification = new RappelRencontreProgrammeNotification($demande, $timing);

                $this->notifyAssignedAgent($demande, $notification);
                $this->notifyBooker($demande, $notification);

                $sentKey = $timing->sentAtDataKey();
                $demande->update([
                    'data' => array_merge($demande->data ?? [], [
                        $sentKey => $now->toIso8601String(),
                    ]),
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * @return Collection<int, Demande>
     */
    public function dueForScheduledReminder(RencontreRappelTimingEnum $timing, ?Carbon $now = null): Collection
    {
        $now = $now ?? now();
        $tolerance = max(1, (int) config('rdv.scheduled_reminders.tolerance_minutes', 1));
        $target = $now->copy()->addMinutes($timing->leadMinutes());
        $windowStart = $target->copy()->subMinutes($tolerance);
        $windowEnd = $target->copy()->addMinutes($tolerance);
        $sentKey = $timing->sentAtDataKey();

        return $this->eligibleUpcomingRencontres($now)
            ->filter(function (Demande $demande) use ($windowStart, $windowEnd, $sentKey) {
                if (filled($demande->data[$sentKey] ?? null)) {
                    return false;
                }

                $at = $demande->rencontreScheduledAt();

                return $at !== null
                    && $at->gte($windowStart)
                    && $at->lte($windowEnd);
            })
            ->values();
    }

    /**
     * @return Collection<int, Demande>
     */
    private function eligibleUpcomingRencontres(Carbon $now): Collection
    {
        return $this->rencontreBaseQuery()
            ->get()
            ->filter(function (Demande $demande) use ($now) {
                if ($demande->rencontreStatut()->isTerminal()) {
                    return false;
                }

                $at = $demande->rencontreScheduledAt();

                return $at !== null && $at->gt($now);
            })
            ->values();
    }

    private function rencontreBaseQuery(): Builder
    {
        $cancelledIds = WorkflowStep::query()
            ->whereIn('code', ['REJETEE', 'ANNULEE'])
            ->pluck('id');

        return Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->when(
                $cancelledIds->isNotEmpty(),
                fn (Builder $query) => $query->where(function (Builder $query) use ($cancelledIds) {
                    $query->whereNull('current_step_id')
                        ->orWhereNotIn('current_step_id', $cancelledIds);
                })
            );
    }

    private function notifyAssignedAgent(Demande $demande, RappelRencontreProgrammeNotification $notification): void
    {
        $agentId = (int) ($demande->data['agent_id'] ?? 0);
        if ($agentId <= 0) {
            return;
        }

        User::find($agentId)?->notify($notification);
    }

    private function notifyBooker(Demande $demande, RappelRencontreProgrammeNotification $notification): void
    {
        $booker = $demande->user;
        if ($booker instanceof User) {
            $booker->notify($notification);

            return;
        }

        $data = $demande->data ?? [];
        $email = trim((string) ($data['email'] ?? ''));
        $phone = preg_replace('/\s+/', '', trim((string) ($data['telephone'] ?? '')));

        if (! filled($email) && ! filled($phone)) {
            return;
        }

        if (filled($phone) && ! filled($email)) {
            Notification::route('twilio', $phone)->notify($notification);

            return;
        }

        $routes = Notification::route('mail', $email);
        if (filled($phone)) {
            $routes->route('twilio', $phone);
        }

        $routes->notify($notification);
    }
}
