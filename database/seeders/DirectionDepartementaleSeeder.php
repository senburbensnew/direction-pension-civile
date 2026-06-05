<?php

namespace Database\Seeders;

use App\Models\DirectionDepartementale;
use Illuminate\Database\Seeder;

class DirectionDepartementaleSeeder extends Seeder
{
    public function run(): void
    {
        $directions = [
            ['abbr' => 'DDO',   'nom' => "Direction Départementale de l'Ouest",      'ville' => 'Port-au-Prince', 'color' => 'blue',   'order' => 1],
            ['abbr' => 'DDA',   'nom' => "Direction Départementale de l'Artibonite", 'ville' => 'Gonaïves',       'color' => 'green',  'order' => 2],
            ['abbr' => 'DDS',   'nom' => 'Direction Départementale du Sud',           'ville' => 'Les Cayes',      'color' => 'purple', 'order' => 3],
            ['abbr' => 'DDN',   'nom' => 'Direction Départementale du Nord',          'ville' => 'Cap-Haïtien',    'color' => 'red',    'order' => 4],
            ['abbr' => 'DDSE',  'nom' => 'Direction Départementale du Sud-Est',       'ville' => 'Jacmel',         'color' => 'yellow', 'order' => 5],
            ['abbr' => 'DDNO',  'nom' => 'Direction Départementale du Nord-Ouest',    'ville' => 'Port-de-Paix',   'color' => 'indigo', 'order' => 6],
            ['abbr' => 'DDNip', 'nom' => 'Direction Départementale des Nippes',       'ville' => 'Miragoâne',      'color' => 'orange', 'order' => 7],
            ['abbr' => 'DDC',   'nom' => 'Direction Départementale du Centre',        'ville' => 'Hinche',         'color' => 'cyan',   'order' => 8],
            ['abbr' => 'DDGA',  'nom' => "Direction Départementale de la Grand'Anse", 'ville' => 'Jérémie',        'color' => 'pink',   'order' => 9],
        ];

        foreach ($directions as $data) {
            DirectionDepartementale::updateOrCreate(['abbr' => $data['abbr']], $data);
        }
    }
}
