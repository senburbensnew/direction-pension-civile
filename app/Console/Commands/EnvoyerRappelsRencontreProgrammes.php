<?php

namespace App\Console\Commands;

use App\Services\RencontreReminderService;
use Illuminate\Console\Command;

class EnvoyerRappelsRencontreProgrammes extends Command
{
    protected $signature = 'rdv:rappels-programmes';

    protected $description = 'Envoie les rappels de rendez-vous 24 h et 30 min avant le créneau (agent et demandeur).';

    public function handle(RencontreReminderService $reminders): int
    {
        if (! config('rdv.scheduled_reminders.enabled', true)) {
            $this->info('Rappels programmés RDV désactivés (configuration).');

            return self::SUCCESS;
        }

        $count = $reminders->dispatchScheduledReminders();

        $this->info($count === 0
            ? 'Aucun rappel programmé à envoyer pour ce passage.'
            : "{$count} rendez-vous rappelé(s) (24 h et/ou 30 min).");

        return self::SUCCESS;
    }
}
