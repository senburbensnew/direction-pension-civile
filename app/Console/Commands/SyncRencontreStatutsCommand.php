<?php

namespace App\Console\Commands;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Services\RencontreWorkflowService;
use Illuminate\Console\Command;

class SyncRencontreStatutsCommand extends Command
{
    protected $signature = 'rencontre:sync-statuts
                            {--dry-run : Afficher sans modifier}
                            {--user= : ID utilisateur pour l’historique (défaut : premier admin)}';

    protected $description = 'Passe en « réalisé » les RDV encore actifs alors qu’une formalité est déjà enregistrée (créneau passé).';

    public function handle(RencontreWorkflowService $workflow): int
    {
        $actor = $this->resolveActor();
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        Demande::query()
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
            ->orderBy('id')
            ->each(function (Demande $demande) use ($workflow, $actor, $dryRun, &$updated) {
                if ($demande->rencontreStatut()->isTerminal()) {
                    return;
                }

                if (
                    $demande->rencontreStatut() !== RencontreStatutEnum::ACTIF
                    || ! $demande->formaliteRdvEnregistree()
                    || ! $demande->rencontreHeureEstPassee()
                ) {
                    return;
                }

                $this->line(sprintf(
                    '%s %s → réalisé (formalité déjà enregistrée)',
                    $demande->code,
                    $demande->id
                ));

                if (! $dryRun) {
                    $workflow->clore(
                        $demande,
                        $actor,
                        RencontreStatutEnum::REALISE,
                        'Synchronisation : formalité enregistrée, statut RDV aligné sur réalisé.'
                    );
                }

                $updated++;
            });

        $this->info($dryRun
            ? "{$updated} rendez-vous à aligner (dry-run)."
            : "{$updated} rendez-vous aligné(s) sur « réalisé ».");

        return self::SUCCESS;
    }

    private function resolveActor(): User
    {
        $id = $this->option('user');

        if ($id) {
            return User::findOrFail((int) $id);
        }

        $admin = User::role('admin')->first();

        if ($admin) {
            return $admin;
        }

        return User::query()->firstOrFail();
    }
}
