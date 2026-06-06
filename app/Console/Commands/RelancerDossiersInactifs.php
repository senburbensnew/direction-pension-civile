<?php

namespace App\Console\Commands;

use App\Models\Demande;
use App\Models\Status;
use App\Notifications\DossierInactifNotification;
use Illuminate\Console\Command;

class RelancerDossiersInactifs extends Command
{
    protected $signature   = 'dossiers:relancer {--jours=7 : Nombre de jours d\'inactivité avant relance}';
    protected $description = 'Envoie une relance aux agents pour les dossiers inactifs depuis N jours.';

    public function handle(): int
    {
        $jours = (int) $this->option('jours');

        $terminaux = Status::whereIn('code', [
            'BROUILLON', 'APPROUVEE', 'REJETEE', 'CLOTUREE', 'ANNULEE',
        ])->pluck('id');

        $demandes = Demande::whereNotIn('status_id', $terminaux)
            ->whereNotNull('current_service_id')
            ->where('updated_at', '<=', now()->subDays($jours))
            ->with('service.users')
            ->get();

        $count = 0;

        foreach ($demandes as $demande) {
            $joursInactif = (int) $demande->updated_at->diffInDays(now());

            foreach ($demande->service->users as $user) {
                try {
                    $user->notify(new DossierInactifNotification($demande, $joursInactif));
                } catch (\Throwable $e) {
                    $this->warn("Notification failed for user {$user->id}: {$e->getMessage()}");
                }
            }

            $count++;
        }

        $this->info("{$count} dossier(s) inactif(s) depuis {$jours} jours — relances envoyées.");

        return self::SUCCESS;
    }
}
