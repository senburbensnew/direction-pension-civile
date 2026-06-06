<?php

namespace App\Console\Commands;

use App\Models\Demande;
use App\Models\Status;
use App\Notifications\DelaiLegalDepasseNotification;
use Illuminate\Console\Command;

class VerifierDelaiLegal extends Command
{
    protected $signature   = 'dossiers:verifier-delai-legal {--delai=30 : Délai légal en jours}';
    protected $description = 'Signale les dossiers ayant dépassé le délai légal de traitement.';

    public function handle(): int
    {
        $delai = (int) $this->option('delai');

        $terminaux = Status::whereIn('code', [
            'BROUILLON', 'APPROUVEE', 'REJETEE', 'CLOTUREE', 'ANNULEE',
        ])->pluck('id');

        $demandes = Demande::whereNotIn('status_id', $terminaux)
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<=', now()->subDays($delai))
            ->with('service.users')
            ->get();

        $count = 0;

        foreach ($demandes as $demande) {
            $joursDepasse = (int) $demande->submitted_at->diffInDays(now()) - $delai;

            if ($demande->service) {
                foreach ($demande->service->users as $user) {
                    try {
                        $user->notify(new DelaiLegalDepasseNotification($demande, $joursDepasse));
                    } catch (\Throwable $e) {
                        $this->warn("Notification failed for user {$user->id}: {$e->getMessage()}");
                    }
                }
            }

            $count++;
        }

        $this->info("{$count} dossier(s) dépassant le délai légal de {$delai} jours — alertes envoyées.");

        return self::SUCCESS;
    }
}
