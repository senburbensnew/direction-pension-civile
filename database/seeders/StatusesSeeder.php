<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'EN_ATTENTE',
                'label' => 'En attente de traitement',
                'description' => 'La demande a été reçue mais n’a pas encore été traitée',
            ],
            [
                'code' => 'APPROUVEE',
                'label' => 'Demande approuvée',
                'description' => 'La demande a été validée',
            ],
            [
                'code' => 'EN_COURS',
                'label' => 'En cours de traitement',
                'description' => 'La demande est actuellement en traitement',
            ],
            [
                'code' => 'REJETEE',
                'label' => 'Demande rejetée',
                'description' => 'La demande a été refusée',
            ],
            [
                'code' => 'FINALISEE',
                'label' => 'Traitement finalisé',
                'description' => 'Le traitement est terminé',
            ],
            [
                'code' => 'ANNULEE',
                'label' => 'Demande annulée',
                'description' => 'La demande a été annulée',
            ],
        ];

        DB::table('statuses')->insert($statuses);
    }
}