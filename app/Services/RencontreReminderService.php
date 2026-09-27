<?php

namespace App\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\RappelRencontreAppelNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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
}
