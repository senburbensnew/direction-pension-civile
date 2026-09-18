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
                'description' => "La Direction générale pilote l'institution, fixe les orientations stratégiques et statue en dernier ressort sur les dossiers de pension civile.",
                'icon'        => 'fa-landmark',
                'color'       => 'blue',
            ],
            [
                'code'        => 'secretariat',
                'nom'         => 'Secretariat',
                'description' => "Le Secrétariat assure la gestion administrative, le traitement du courrier et la coordination des correspondances officielles de la Direction de la Pension Civile.",
                'icon'        => 'fa-building',
                'color'       => 'blue',
            ],
            [
                'code'        => 'reception',
                'nom'         => 'Reception',
                'description' => "La Réception accueille les usagers, oriente les démarches et enregistre les dépôts de dossiers au siège de la Direction de la Pension Civile.",
                'icon'        => 'fa-concierge-bell',
                'color'       => 'green',
            ],
            [
                'code'        => 'service_controle_placement',
                'nom'         => 'Service Controle et Placements',
                'description' => "Le Service Contrôle et Placements vérifie la conformité des dossiers, suit leur circulation interne et assure le placement des pièces au bon stade du circuit.",
                'icon'        => 'fa-search',
                'color'       => 'purple',
            ],
            [
                'code'        => 'cellule_administration',
                'nom'         => 'Cellule Administration',
                'description' => "La Cellule Administration gère les affaires internes de l'institution, notamment le soutien administratif et les ressources humaines.",
                'icon'        => 'fa-user-cog',
                'color'       => 'yellow',
            ],
            [
                'code'        => 'service_liquidation',
                'nom'         => 'Service Liquidation de Pension',
                'description' => "Le Service Liquidation de Pension instruit et liquide les droits à pension des fonctionnaires civils, conformément aux textes en vigueur.",
                'icon'        => 'fa-file-invoice-dollar',
                'color'       => 'red',
            ],
            [
                'code'        => 'service_comptabilite',
                'nom'         => 'Service Comptabilite',
                'description' => "Le Service Comptabilité assure le suivi comptable et financier des opérations liées aux pensions et au fonctionnement de la Direction.",
                'icon'        => 'fa-calculator',
                'color'       => 'indigo',
            ],
            [
                'code'        => 'service_formalite',
                'nom'         => 'Service des Formalites',
                'description' => "Le Service des Formalités instruit les actes administratifs nécessaires à la constitution, à la régularisation et à la mise à jour des dossiers de pension.",
                'icon'        => 'fa-file-signature',
                'color'       => 'teal',
            ],
            [
                'code'        => 'service_assurance',
                'nom'         => 'Service Assurance',
                'description' => "Le Service Assurance traite les questions d'assurance et de prestations complémentaires liées aux droits des pensionnaires et de leurs ayants droit.",
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
