<?php

namespace Database\Seeders;

use App\Models\AnneeFiscale;
use Illuminate\Database\Seeder;

class AnneeFiscaleSeeder extends Seeder
{
    public function run(): void
    {
        $annees = [
            ['code' => '2026-2027', 'date_debut' => '2026-10-01', 'date_fin' => '2027-09-30', 'active' => true],
            ['code' => '2025-2026', 'date_debut' => '2025-10-01', 'date_fin' => '2026-09-30', 'active' => false],
            ['code' => '2024-2025', 'date_debut' => '2024-10-01', 'date_fin' => '2025-09-30', 'active' => false],
            ['code' => '2023-2024', 'date_debut' => '2023-10-01', 'date_fin' => '2024-09-30', 'active' => false],
            ['code' => '2022-2023', 'date_debut' => '2022-10-01', 'date_fin' => '2023-09-30', 'active' => false],
        ];

        foreach ($annees as $index => $annee) {
            AnneeFiscale::updateOrCreate(
                ['code' => $annee['code']],
                [
                    'libelle'    => 'Exercice ' . $annee['code'],
                    'date_debut' => $annee['date_debut'],
                    'date_fin'   => $annee['date_fin'],
                    'active'     => $annee['active'],
                    'ordre'      => 100 - $index,
                ]
            );
        }
    }
}