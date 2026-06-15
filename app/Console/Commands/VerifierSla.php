<?php

namespace App\Console\Commands;

use App\Models\Demande;
use App\Models\ServiceSla;
use App\Notifications\DossierInactifNotification;
use Illuminate\Console\Command;

class VerifierSla extends Command
{
    protected $signature   = 'dossiers:verifier-sla';
    protected $description = 'Alerte les agents pour les dossiers ayant dépassé le SLA de leur service.';

    public function handle(): int
    {
        $demandes = Demande::active()
            ->whereNotNull('current_service_id')
            ->with('service.users')
            ->get();

        $count = 0;

        foreach ($demandes as $demande) {
            $sla = ServiceSla::forService($demande->current_service_id, $demande->type);

            if (!$sla) continue;

            $joursInactif = (int) $demande->updated_at->diffInDays(now());

            if ($joursInactif >= $sla->delai_jours) {
                foreach ($demande->service->users as $user) {
                    try {
                        $user->notify(new DossierInactifNotification($demande, $joursInactif));
                    } catch (\Throwable $e) {
                        $this->warn("Notification failed for user {$user->id}: {$e->getMessage()}");
                    }
                }
                $count++;
            }
        }

        $this->info("{$count} dossier(s) dépassant leur SLA — alertes envoyées.");

        return self::SUCCESS;
    }
}
