<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Pension\BeneficiaireProvisioningService;
use Illuminate\Console\Command;

class SyncBeneficiairesFromUsers extends Command
{
    protected $signature = 'pension:sync-beneficiaires {--user= : ID utilisateur cible}';

    protected $description = 'Crée bénéficiaire + affiliation manquants pour les comptes pensionnés existants';

    public function handle(BeneficiaireProvisioningService $provisioning): int
    {
        $query = User::query()->role('pensionne');

        if ($this->option('user')) {
            $query->whereKey($this->option('user'));
        }

        $count = 0;

        $query->each(function (User $user) use ($provisioning, &$count) {
            $before = $user->beneficiaireLinks()->count();
            $provisioning->ensureForUser($user);
            $after = $user->fresh()->beneficiaireLinks()->count();

            if ($after > $before) {
                $count++;
                $this->line("User #{$user->id} synchronisé.");
            }
        });

        $this->info("Terminé. {$count} compte(s) mis à jour.");

        return self::SUCCESS;
    }
}
