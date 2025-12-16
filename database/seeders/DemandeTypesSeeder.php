<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemandeTypesSeeder extends Seeder
{
    public function run()
    {
        // Exemple simple : on stocke dans une table "demande_types" si tu veux.
        // Ici on crée seulement un helper pour insérer des demandes de test.

        // Crée quelques demandes exemples pour test
        DB::table('demandes')->insert([
            [
                'user_id' => 1,
                'numero_dossier' => 'PEN-'.Str::upper(Str::random(8)),
                'categorie' => 'pensionnaire',
                'type' => 'preuve_existence',
                'etat' => 'reçue',
                'data' => json_encode(['nom'=>'Jean Dupont','cin'=>'12345678']),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 1,
                'numero_dossier' => 'PEN-'.Str::upper(Str::random(8)),
                'categorie' => 'pensionnaire',
                'type' => 'virement',
                'etat' => 'en attente de documents',
                'data' => json_encode(['banque'=>'BNC','rib'=>'HT123456']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
