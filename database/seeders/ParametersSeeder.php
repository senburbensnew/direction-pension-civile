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
            ['name' => 'contact_address',      'description' => 'Adresse du siège social',  'value' => '5, Avenue Charles Sumner, Port-au-Prince, Haïti (W.I)'],
            ['name' => 'contact_phone',        'description' => 'Numéro de téléphone',      'value' => '+(509) 29 92 1007'],
            ['name' => 'contact_hours',        'description' => 'Heures d\'ouverture',      'value' => 'Lun–Ven : 8h00 - 16h00'],
            ['name' => 'contact_email',        'description' => 'Adresse e-mail officielle','value' => 'dpc.info@mef.gouv.ht'],
            ['name' => 'contact_map_url',      'description' => 'URL Google Maps iframe',   'value' => 'https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15130.65651919688!2d-72.3400433!3d18.544074!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x410db1f320d7ae01!2sMinist%C3%A8re%20de%20l\'%C3%89conomie%20et%20des%20Finances%20(MEF)!5e0!3m2!1sen!2sht!4v1592604157945!5m2!1sen!2sht'],
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
