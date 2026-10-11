<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParametersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $params = [
            ['name' => 'is_maintenance_mode', 'description' => 'En maintenance',           'value' => 'false'],
            [
                'name' => 'rdv_allow_weekends',
                'description' => 'Autoriser la réservation le week-end (samedi et dimanche)',
                'value' => 'false',
            ],
            [
                'name' => 'rdv_booking_start',
                'description' => 'Heure de début des créneaux réservables (HH:MM)',
                'value' => '14:00',
            ],
            [
                'name' => 'rdv_booking_end',
                'description' => 'Heure de fin des créneaux réservables (HH:MM, 24:00 = fin de journée)',
                'value' => '16:00',
            ],
            ['name' => 'contact_address',      'description' => 'Adresse du siège social',  'value' => '5, Avenue Charles Sumner, Port-au-Prince, Haïti (W.I)'],
            ['name' => 'contact_phone',        'description' => 'Numéro de téléphone',      'value' => '+(509) 29 92 1007'],
            ['name' => 'contact_hours',        'description' => 'Heures d\'ouverture',      'value' => 'Lun–Ven : 8h00 - 16h00'],
            ['name' => 'contact_email',        'description' => 'Adresse e-mail officielle','value' => 'dpc.info@mef.gouv.ht'],
            ['name' => 'contact_map_url',      'description' => 'URL Google Maps iframe',   'value' => 'https://maps.google.com/maps?q=18.544074,-72.3400433&hl=fr&z=16&output=embed'],
            ['name' => 'social_facebook',      'description' => 'Lien Facebook',            'value' => '#'],
            ['name' => 'social_twitter',       'description' => 'Lien X (Twitter)',         'value' => '#'],
            ['name' => 'social_linkedin',      'description' => 'Lien LinkedIn',            'value' => '#'],
            ['name' => 'social_youtube',       'description' => 'Lien YouTube',             'value' => '#'],
        ];

        foreach ($params as $param) {
            DB::table('parameters')->updateOrInsert(['name' => $param['name']], $param);
        }
    }
}
