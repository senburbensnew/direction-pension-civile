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
                'code'        => 'direction',
                'nom'         => 'Direction',
                'description' => "Direction generale de l'institution",
                'icon'        => 'fa-landmark',
                'color'       => 'blue',
            ],
            [
                'code'        => 'secretariat',
                'nom'         => 'Secretariat',
                'description' => 'Gestion administrative et correspondances',
                'icon'        => 'fa-building',
                'color'       => 'blue',
            ],
            [
                'code'        => 'reception',
                'nom'         => 'Reception',
                'description' => 'Accueil et orientation des usagers',
                'icon'        => 'fa-concierge-bell',
                'color'       => 'green',
            ],
            [
                'code'        => 'service_controle_placement',
                'nom'         => 'Service Controle et Placements',
                'description' => 'Controle et placement des dossiers',
                'icon'        => 'fa-search',
                'color'       => 'purple',
            ],
            [
                'code'        => 'cellule_administration',
                'nom'         => 'Cellule Administration',
                'description' => 'Administration interne et ressources humaines',
                'icon'        => 'fa-user-cog',
                'color'       => 'yellow',
            ],
            [
                'code'        => 'service_liquidation',
                'nom'         => 'Service Liquidation de Pension',
                'description' => 'Traitement des dossiers de liquidation',
                'icon'        => 'fa-file-invoice-dollar',
                'color'       => 'red',
            ],
            [
                'code'        => 'service_comptabilite',
                'nom'         => 'Service Comptabilite',
                'description' => 'Gestion comptable et financiere',
                'icon'        => 'fa-calculator',
                'color'       => 'indigo',
            ],
            [
                'code'        => 'service_formalite',
                'nom'         => 'Service des Formalites',
                'description' => 'Gestion des formalites administratives',
                'icon'        => 'fa-file-signature',
                'color'       => 'teal',
            ],
            [
                'code'        => 'service_assurance',
                'nom'         => 'Service Assurance',
                'description' => 'Gestion des assurances et prestations',
                'icon'        => 'fa-shield-alt',
                'color'       => 'pink',
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(
                ['code' => $service['code']],
                $service
            );
        }
    }
}
