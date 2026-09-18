<?php

namespace Database\Seeders;

use App\Models\DirectionDepartementale;
use Illuminate\Database\Seeder;

class DirectionDepartementaleSeeder extends Seeder
{
    public function run(): void
    {
        $directions = [
            ['abbr' => 'DDO',   'nom' => "Direction Départementale de l'Ouest",      'ville' => 'Port-au-Prince', 'color' => 'blue',   'order' => 1, 'description' => "Représentation régionale de la Direction de la Pension Civile pour le département de l'Ouest. Elle accueille les usagers, reçoit les dossiers et oriente les pensionnaires, fonctionnaires et institutions vers les services compétents du siège."],
            ['abbr' => 'DDA',   'nom' => "Direction Départementale de l'Artibonite", 'ville' => 'Gonaïves',       'color' => 'green',  'order' => 2, 'description' => "Représentation régionale de la DPC dans le département de l'Artibonite. Elle assure la proximité du service public de la pension civile à Gonaïves et dans les communes rattachées."],
            ['abbr' => 'DDS',   'nom' => 'Direction Départementale du Sud',           'ville' => 'Les Cayes',      'color' => 'purple', 'order' => 3, 'description' => "Représentation régionale de la DPC dans le département du Sud. Elle facilite le dépôt des demandes, le suivi des dossiers et l'information des usagers à Les Cayes."],
            ['abbr' => 'DDN',   'nom' => 'Direction Départementale du Nord',          'ville' => 'Cap-Haïtien',    'color' => 'red',    'order' => 4, 'description' => "Représentation régionale de la DPC dans le département du Nord. Elle accompagne les usagers du Cap-Haïtien et des communes voisines dans leurs démarches liées à la pension civile."],
            ['abbr' => 'DDSE',  'nom' => 'Direction Départementale du Sud-Est',       'ville' => 'Jacmel',         'color' => 'yellow', 'order' => 5, 'description' => "Représentation régionale de la DPC dans le département du Sud-Est. Elle constitue le point de contact local pour les pensionnaires et les agents publics à Jacmel."],
            ['abbr' => 'DDNO',  'nom' => 'Direction Départementale du Nord-Ouest',    'ville' => 'Port-de-Paix',   'color' => 'indigo', 'order' => 6, 'description' => "Représentation régionale de la DPC dans le département du Nord-Ouest. Elle rapproche les services de la pension civile des usagers de Port-de-Paix et des communes du département."],
            ['abbr' => 'DDNip', 'nom' => 'Direction Départementale des Nippes',       'ville' => 'Miragoâne',      'color' => 'orange', 'order' => 7, 'description' => "Représentation régionale de la DPC dans le département des Nippes. Elle reçoit et oriente les demandes des usagers à Miragoâne, en liaison avec les services du siège."],
            ['abbr' => 'DDC',   'nom' => 'Direction Départementale du Centre',        'ville' => 'Hinche',         'color' => 'cyan',   'order' => 8, 'description' => "Représentation régionale de la DPC dans le département du Centre. Elle assure l'accueil et l'orientation des usagers à Hinche pour les formalités de pension civile."],
            ['abbr' => 'DDGA',  'nom' => "Direction Départementale de la Grand'Anse", 'ville' => 'Jérémie',        'color' => 'pink',   'order' => 9, 'description' => "Représentation régionale de la DPC dans le département de la Grand'Anse. Elle accompagne les usagers de Jérémie et du département dans leurs démarches auprès de la pension civile."],
        ];

        foreach ($directions as $data) {
            DirectionDepartementale::updateOrCreate(['abbr' => $data['abbr']], $data);
        }
    }
}
