<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\HaitiHolidayService;

class GenerateHaitiHolidays extends Command
{
    protected $signature = 'holidays:generate
                            {year? : Année à générer}
                            {--next : Générer également l’année suivante}';

    protected $description = 'Génère les jours fériés officiels d’Haïti';

    public function handle(HaitiHolidayService $service): int
    {
        $year = $this->argument('year')
            ?? now()->year;

        $service->generate((int) $year);

        $this->info(
            "Les jours fériés haïtiens de {$year} ont été générés."
        );

        if ($this->option('next')) {
            $service->generate((int) $year + 1);

            $this->info(
                "Les jours fériés haïtiens de " .
                ((int) $year + 1) .
                " ont également été générés."
            );
        }

        return self::SUCCESS;
    }
}