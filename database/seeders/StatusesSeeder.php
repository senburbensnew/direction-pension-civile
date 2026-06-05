<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'BROUILLON',           'label' => 'Brouillon',                'description' => "La demande est en cours de preparation et n'a pas encore ete soumise"],
            ['code' => 'SOUMISE',             'label' => 'Soumise',                  'description' => "La demande a ete soumise par l'institution et transmise au systeme"],
            ['code' => 'EN_ATTENTE',          'label' => 'En attente de traitement', 'description' => "La demande a ete recue mais n'a pas encore ete traitee"],
            ['code' => 'APPROUVEE',           'label' => 'Demande approuvee',        'description' => 'La demande a ete validee'],
            ['code' => 'EN_COURS',            'label' => 'En cours de traitement',   'description' => 'La demande est actuellement en traitement'],
            ['code' => 'REJETEE',             'label' => 'Demande rejetee',          'description' => 'La demande a ete refusee'],
            ['code' => 'FINALISEE',           'label' => 'Traitement finalise',      'description' => 'Le traitement est termine'],
            ['code' => 'ANNULEE',             'label' => 'Demande annulee',          'description' => 'La demande a ete annulee'],
            ['code' => 'COMPLEMENT_REQUIS',   'label' => 'Complement requis',        'description' => "Un complement d'information ou de documents est requis de la part de l'usager"],
            ['code' => 'TRANSFERT_EN_ATTENTE','label' => 'Transfert en attente',     'description' => 'Le dossier a ete transfere vers un service et est en attente de confirmation de reception'],
            ['code' => 'TRANSFERT_REFUSE',    'label' => 'Transfert refuse',         'description' => 'Le service destinataire a refuse la reception du dossier'],
        ];

        DB::table('statuses')->upsert($statuses, ['code'], ['label', 'description']);
    }
}
