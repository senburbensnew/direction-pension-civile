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
                'code' => 'BROUILLON',
                'label' => 'Brouillon',
                'description' => 'La demande est en cours de préparation et n’a pas encore été soumise',
            ],
            [
                'code' => 'SOUMISE',
                'label' => 'Soumise',
                'description' => 'La demande a été soumise par l’institution et transmise au système',
            ],
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
            [
                'code' => 'COMPLEMENT_REQUIS',
                'label' => 'Complément requis',
                'description' => 'Un complément d\'information ou de documents est requis de la part de l\'usager',
            ],
            [
                'code' => 'TRANSFERT_EN_ATTENTE',
                'label' => 'Transfert en attente',
                'description' => 'Le dossier a été transféré vers un service et est en attente de confirmation de réception',
            ],
            [
                'code' => 'TRANSFERT_REFUSE',
                'label' => 'Transfert refusé',
                'description' => 'Le service destinataire a refusé la réception du dossier',
            ],
        ];

        DB::table('statuses')->insert($statuses);
    }
}