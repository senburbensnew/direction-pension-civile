<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'direction',
                'nom' => 'Direction',
                'description' => 'Direction générale de l’institution',
            ],
            [
                'code' => 'secretariat',
                'nom' => 'Secrétariat',
                'description' => 'Gestion administrative et correspondances',
            ],
            [
                'code' => 'service_liquidation',
                'nom' => 'Service Liquidation',
                'description' => 'Traitement des dossiers de liquidation',
            ],
            [
                'code' => 'service_controle_placement',
                'nom' => 'Service Contrôle et Placement',
                'description' => 'Contrôle et placement des dossiers',
            ],
            [
                'code' => 'service_comptabilite',
                'nom' => 'Service Comptabilité',
                'description' => 'Gestion comptable et financière',
            ],
            [
                'code' => 'service_formalite',
                'nom' => 'Service Formalité',
                'description' => 'Gestion des formalités administratives',
            ],
            [
                'code' => 'service_assurance',
                'nom' => 'Service Assurance',
                'description' => 'Gestion des assurances et prestations',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['code' => $service['code']], // clé unique
                $service
            );
        }
    }
}
