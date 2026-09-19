<?php

namespace Database\Seeders;

use App\Models\DirectionDepartementale;
use Illuminate\Database\Seeder;

class DirectionDepartementaleSeeder extends Seeder
{
    public function run(): void
    {
        $directions = [
            [
                'abbr' => 'DDS',
                'nom' => 'Direction Départementale du Sud Cayes',
                'ville' => 'Les Cayes',
                'color' => 'purple',
                'order' => 1,
                'description' => "Représentation régionale de la DPC dans le département du Sud, basée aux Cayes. Elle facilite le dépôt des demandes, le suivi des dossiers et l'information des usagers.",
            ],
            [
                'abbr' => 'DDNip',
                'nom' => 'Direction Départementale des Nippes',
                'ville' => 'Miragoâne',
                'color' => 'orange',
                'order' => 2,
                'description' => "Représentation régionale de la DPC dans le département des Nippes. Elle reçoit et oriente les demandes des usagers, en liaison avec les services du siège.",
            ],
            [
                'abbr' => 'BAQ',
                'nom' => "Bureau d'Aquin",
                'ville' => 'Aquin',
                'color' => 'teal',
                'order' => 3,
                'description' => "Bureau local de la DPC à Aquin. Il accueille les usagers, reçoit les dossiers et oriente vers les services compétents.",
            ],
            [
                'abbr' => 'BPG',
                'nom' => 'Bureau de Petit-Goâve',
                'ville' => 'Petit-Goâve',
                'color' => 'blue',
                'order' => 4,
                'description' => "Bureau local de la DPC à Petit-Goâve. Il assure un service de proximité pour le dépôt et le suivi des dossiers de pension civile.",
            ],
            [
                'abbr' => 'DDSE',
                'nom' => 'Direction Départementale du Sud-Est Jacmel',
                'ville' => 'Jacmel',
                'color' => 'yellow',
                'order' => 5,
                'description' => "Représentation régionale de la DPC dans le département du Sud-Est, basée à Jacmel. Elle constitue le point de contact local pour les pensionnaires et les agents publics.",
            ],
            [
                'abbr' => 'DDGA',
                'nom' => "Direction Départementale de la Grand'Anse Jérémie",
                'ville' => 'Jérémie',
                'color' => 'pink',
                'order' => 6,
                'description' => "Représentation régionale de la DPC dans le département de la Grand'Anse, basée à Jérémie. Elle accompagne les usagers du département dans leurs démarches auprès de la pension civile.",
            ],
            [
                'abbr' => 'DDN',
                'nom' => 'Direction Départementale du Nord Cap-Haïtien',
                'ville' => 'Cap-Haïtien',
                'color' => 'red',
                'order' => 7,
                'description' => "Représentation régionale de la DPC dans le département du Nord, basée au Cap-Haïtien. Elle accompagne les usagers du Cap-Haïtien et des communes voisines.",
            ],
            [
                'abbr' => 'DDNE',
                'nom' => 'Direction Départementale du Nord-Est Fort-Liberté',
                'ville' => 'Fort-Liberté',
                'color' => 'amber',
                'order' => 8,
                'description' => "Représentation régionale de la DPC dans le département du Nord-Est, basée à Fort-Liberté. Elle rapproche les services de la pension civile des usagers du département.",
            ],
            [
                'abbr' => 'DDNO',
                'nom' => 'Direction Départementale du Nord-Ouest Port-de-Paix',
                'ville' => 'Port-de-Paix',
                'color' => 'indigo',
                'order' => 9,
                'description' => "Représentation régionale de la DPC dans le département du Nord-Ouest, basée à Port-de-Paix. Elle rapproche les services de la pension civile des usagers du département.",
            ],
            [
                'abbr' => 'DDC',
                'nom' => 'Direction Départementale du Centre Hinche',
                'ville' => 'Hinche',
                'color' => 'cyan',
                'order' => 10,
                'description' => "Représentation régionale de la DPC dans le département du Centre, basée à Hinche. Elle assure l'accueil et l'orientation des usagers pour les formalités de pension civile.",
            ],
            [
                'abbr' => 'DDA',
                'nom' => "Direction Départementale de l'Artibonite Gonaïves",
                'ville' => 'Gonaïves',
                'color' => 'green',
                'order' => 11,
                'description' => "Représentation régionale de la DPC dans le département de l'Artibonite, basée aux Gonaïves. Elle assure la proximité du service public de la pension civile et des communes rattachées.",
            ],
            [
                'abbr' => 'BSM',
                'nom' => 'Bureau de St Marc',
                'ville' => 'Saint-Marc',
                'color' => 'slate',
                'order' => 12,
                'description' => "Bureau local de la DPC à Saint-Marc. Il accueille les usagers, reçoit les dossiers et oriente vers les services compétents.",
            ],
        ];

        foreach ($directions as $data) {
            DirectionDepartementale::updateOrCreate(['abbr' => $data['abbr']], $data);
        }

        DirectionDepartementale::whereNotIn('abbr', array_column($directions, 'abbr'))->delete();
    }
}
