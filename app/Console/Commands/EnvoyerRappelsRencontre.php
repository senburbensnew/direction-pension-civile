<?php

namespace App\Console\Commands;

use App\Services\RencontreReminderService;
use Illuminate\Console\Command;

class EnvoyerRappelsRencontre extends Command
{
    protected $signature = 'rdv:rappels-veille';

    protected $description = 'Notifie le service des Formalités pour appeler les usagers la veille de leur rendez-vous.';

    public function handle(RencontreReminderService $reminders): int
    {
        $count = $reminders->dispatch();

        $this->info($count === 0
            ? 'Aucun appel de rappel à planifier aujourd’hui.'
            : "{$count} rappel(s) d’appel envoyé(s) au service des Formalités.");

        return self::SUCCESS;
    }
}
