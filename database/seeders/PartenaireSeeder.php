<?php

namespace Database\Seeders;

use App\Models\Partenaire;
use Illuminate\Database\Seeder;

class PartenaireSeeder extends Seeder
{
    public function run(): void
    {
        if (Partenaire::exists()) {
            return;
        }

        $partenaires = [
            ['name' => "Ministère de l'Economie et des Finances",                              'logo' => 'images/logo_MEF.png'],
            ['name' => 'Banque Interaméricaine de Développement',                               'logo' => 'images/logo_BID.svg'],
            ['name' => "Office d'Assurance Véhicules Contre Tiers",                             'logo' => 'images/logo_OAVCT.png'],
            ['name' => 'Unité de Lutte Contre la Corruption',                                   'logo' => 'images/logo_ulcc.jpg'],
            ['name' => "Office d'Assurance Accidents du Travail, Maladie et Maternité",         'logo' => 'images/logo_ofatma.jpg'],
            ['name' => "Office des Postes d'Haïti",                                             'logo' => 'images/logo_oph.png'],
            ['name' => "Ministère de l'Intérieur et des Collectivités Territoriales",           'logo' => null],
            ['name' => 'Ministère de la Culture et de la Communication',                        'logo' => 'images/logo_mcc.jpg'],
            ['name' => "Ministère du Commerce et de l'Industrie",                               'logo' => 'images/logo_mci.png'],
            ['name' => 'Ministère des Travaux Publics, Transports et Communications',           'logo' => 'images/logo_mtptc.jpg'],
            ['name' => "Ministère des Haïtiens Vivant à l'Étranger",                           'logo' => null],
            ['name' => 'Ministère de la Santé Publique et de la Population',                    'logo' => 'images/logo_mspp.png'],
            ['name' => "Ministère de l'Éducation Nationale et de la Formation Professionnelle", 'logo' => 'images/logo_menfp.jpg'],
        ];

        foreach ($partenaires as $i => $data) {
            Partenaire::create([
                'name'        => $data['name'],
                'logo'        => $data['logo'],
                'website_url' => null,
                'order'       => $i + 1,
                'active'      => true,
            ]);
        }
    }
}
