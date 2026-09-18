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
                'description' => "La Réception accueille les usagers, oriente les démarches et enregistre les dépôts de dossiers, en lien avec le Service Accueil et Formalités.",
                'icon'        => 'fa-concierge-bell',
                'color'       => 'green',
            ],
            [
                'code'        => 'service_controle_placement',
                'nom'         => 'Service de Contrôle et Placement',
                'description' => 'Le Service de Contrôle et Placement veille à l’adéquation et à la sûreté des opérations financières, étudie les placements et rend compte des opérations affectant le compte pension civile.',
                'icon'        => 'fa-search',
                'color'       => 'purple',
            ],
            [
                'code'        => 'cellule_administration',
                'nom'         => 'Service Administratif',
                'description' => 'Le Service Administratif pourvoit en matériel et équipement, assure le transport et la logistique, veille à l’entretien du bâtiment et assiste la Direction dans la gestion des ressources humaines.',
                'icon'        => 'fa-user-cog',
                'color'       => 'yellow',
            ],
            [
                'code'        => 'service_liquidation',
                'nom'         => 'Service de Liquidation',
                'description' => 'Le Service de Liquidation traite, classe et enregistre les dossiers, met à jour les fiches électroniques et établit le droit à la pension conformément au décret en vigueur.',
                'icon'        => 'fa-file-invoice-dollar',
                'color'       => 'red',
            ],
            [
                'code'        => 'service_comptabilite',
                'nom'         => 'Service de la Comptabilité',
                'description' => 'Le Service de la Comptabilité tient le payroll, traite les réversibilités, prépare le payroll mensuel et gère la distribution des chèques.',
                'icon'        => 'fa-calculator',
                'color'       => 'indigo',
            ],
            [
                'code'        => 'service_formalite',
                'nom'         => 'Service Accueil et Formalités',
                'description' => 'Le Service Accueil et Formalités accueille les nouveaux pensionnés, prépare les cartes de pension, valide les mandats et assure les virements ainsi que le service de proximité.',
                'icon'        => 'fa-file-signature',
                'color'       => 'teal',
            ],
            [
                'code'        => 'service_assurance',
                'nom'         => 'Service Assurance',
                'description' => 'Le Service Assurance gère la couverture d’assurance des pensionnés et l’octroi de la contribution funéraire en cas de décès.',
                'icon'        => 'fa-shield-alt',
                'color'       => 'pink',
            ],
            [
                'code'        => 'service_archives',
                'nom'         => 'Service des Archives',
                'description' => 'Le Service des Archives archive les correspondances, les fichiers de demande de pension et les dossiers de chaque pensionné.',
                'icon'        => 'fa-box-archive',
                'color'       => 'slate',
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
