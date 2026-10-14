<?php

namespace App\Console\Commands;

use App\Enums\PensionMandatStatusEnum;
use App\Models\BeneficiaireRelationEvent;
use App\Models\PensionMandat;
use App\Services\Pension\BeneficiaireRelationService;
use Illuminate\Console\Command;

class ExpireBeneficiaireRelationsCommand extends Command
{
    protected $signature = 'beneficiaire-relations:expire';

    protected $description = 'Marque comme expirés les liens et mandats dont la date de fin est dépassée';

    public function handle(BeneficiaireRelationService $relations): int
    {
        $links = $relations->expireDueLinks();

        $mandats = 0;
        $today = now()->toDateString();

        PensionMandat::query()
            ->where('status', PensionMandatStatusEnum::ACTIF->value)
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '<', $today)
            ->each(function (PensionMandat $mandat) use (&$mandats) {
                $before = $mandat->only(['status']);
                $mandat->update(['status' => PensionMandatStatusEnum::EXPIRE]);
                BeneficiaireRelationEvent::record(
                    $mandat,
                    'mandat_expire',
                    null,
                    $before,
                    $mandat->only(['status']),
                );
                $mandats++;
            });

        $this->info("Liens expirés : {$links}, mandats expirés : {$mandats}.");

        return self::SUCCESS;
    }
}
