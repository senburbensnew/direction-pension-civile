<?php

namespace Database\Seeders;

use App\Models\LienUtile;
use Illuminate\Database\Seeder;

class LienUtileSeeder extends Seeder
{
    public function run(): void
    {
        $liens = [
            [
                'name'         => 'Ministère de l\'Économie et des Finances',
                'abbr'         => 'MEF',
                'url'          => 'https://www.mef.gouv.ht',
                'category'     => 'gouvernement',
                'order_column' => 1,
                'published'    => true,
            ],
            [
                'name'         => 'Ministère de la Fonction Publique',
                'abbr'         => 'MFP',
                'url'          => 'https://www.mfpa.gouv.ht',
                'category'     => 'gouvernement',
                'order_column' => 2,
                'published'    => true,
            ],
            [
                'name'         => 'Trésor Public',
                'abbr'         => 'TRESO',
                'url'          => 'https://www.tresor.gouv.ht',
                'category'     => 'gouvernement',
                'order_column' => 3,
                'published'    => true,
            ],
            [
                'name'         => 'Office National d\'Identification',
                'abbr'         => 'ONI',
                'url'          => 'https://www.oni.gouv.ht',
                'category'     => 'gouvernement',
                'order_column' => 4,
                'published'    => true,
            ],
            [
                'name'         => 'Direction Générale des Impôts',
                'abbr'         => 'DGI',
                'url'          => 'https://www.dgi.gouv.ht',
                'category'     => 'gouvernement',
                'order_column' => 5,
                'published'    => true,
            ],
            [
                'name'         => 'Office d\'Assurance Accidents du Travail, Maladie et Maternité',
                'abbr'         => 'OFATMA',
                'url'          => 'https://www.ofatma.gouv.ht',
                'category'     => 'securite_sociale',
                'order_column' => 6,
                'published'    => true,
            ],
            [
                'name'         => 'Office National d\'Assurance Vieillesse',
                'abbr'         => 'ONA',
                'url'          => 'https://www.ona.gouv.ht',
                'category'     => 'securite_sociale',
                'order_column' => 7,
                'published'    => true,
            ],
            [
                'name'         => 'Banque de la République d\'Haïti',
                'abbr'         => 'BRH',
                'url'          => 'https://www.brh.ht',
                'category'     => 'finance',
                'order_column' => 8,
                'published'    => true,
            ],
            [
                'name'         => 'Le Moniteur — Journal Officiel de la République',
                'abbr'         => 'Le Moniteur',
                'url'          => 'https://www.lemoniteur.gouv.ht',
                'category'     => 'juridique',
                'order_column' => 9,
                'published'    => true,
            ],
            [
                'name'         => 'Primature — Bureau du Premier Ministre',
                'abbr'         => 'Primature',
                'url'          => 'https://www.primature.gouv.ht',
                'category'     => 'gouvernement',
                'order_column' => 10,
                'published'    => true,
            ],
        ];

        foreach ($liens as $data) {
            LienUtile::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
