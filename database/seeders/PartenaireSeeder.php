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
            ['name' => "Ministère de l'Economie et des Finances",                              'logo' => 'cms/partenaires/logo_MEF.png'],
            ['name' => 'Banque Interaméricaine de Développement',                               'logo' => 'cms/partenaires/logo_BID.svg'],
            ['name' => "Office d'Assurance Véhicules Contre Tiers",                             'logo' => 'cms/partenaires/logo_OAVCT.png'],
            ['name' => 'Unité de Lutte Contre la Corruption',                                   'logo' => 'cms/partenaires/logo_ulcc.jpg'],
            ['name' => "Office d'Assurance Accidents du Travail, Maladie et Maternité",         'logo' => 'cms/partenaires/logo_ofatma.jpg'],
            ['name' => "Office des Postes d'Haïti",                                             'logo' => 'cms/partenaires/logo_oph.png'],
            ['name' => "Ministère de l'Intérieur et des Collectivités Territoriales",           'logo' => null],
            ['name' => 'Ministère de la Culture et de la Communication',                        'logo' => 'cms/partenaires/logo_mcc.jpg'],
            ['name' => "Ministère du Commerce et de l'Industrie",                               'logo' => 'cms/partenaires/logo_mci.png'],
            ['name' => 'Ministère des Travaux Publics, Transports et Communications',           'logo' => 'cms/partenaires/logo_mtptc.jpg'],
            ['name' => "Ministère des Haïtiens Vivant à l'Étranger",                           'logo' => null],
            ['name' => 'Ministère de la Santé Publique et de la Population',                    'logo' => 'cms/partenaires/logo_mspp.png'],
            ['name' => "Ministère de l'Éducation Nationale et de la Formation Professionnelle", 'logo' => 'cms/partenaires/logo_menfp.jpg'],
        ];

        foreach ($partenaires as $i => $data) {
            Partenaire::create([
                'name' => $data['name'],
                'logo' => $data['logo'],
                'website_url' => null,
                'order' => $i + 1,
                'active' => true,
            ]);
        }
    }
}
