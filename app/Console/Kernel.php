<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Relancer les dossiers inactifs depuis 7 jours, tous les jours à 8h
        $schedule->command('dossiers:relancer --jours=7')->dailyAt('08:00');

        // Vérifier les dépassements de délai légal (30 jours), tous les jours à 8h
        $schedule->command('dossiers:verifier-delai-legal')->dailyAt('08:00');

        // Vérifier les SLA par service, tous les jours à 8h30
        $schedule->command('dossiers:verifier-sla')->dailyAt('08:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
