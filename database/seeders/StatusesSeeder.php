<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            ['code' => 'BROUILLON',            'nom' => 'Brouillon',             'type_noeud' => 'initial',    'ordre' => 1],
            ['code' => 'SOUMISE',              'nom' => 'Soumise',               'type_noeud' => 'initial',    'ordre' => 2],
            ['code' => 'EN_ATTENTE',           'nom' => 'En attente',            'type_noeud' => 'intermediaire', 'ordre' => 5],
            ['code' => 'EN_COURS',             'nom' => 'En cours',              'type_noeud' => 'intermediaire', 'ordre' => 6],
            ['code' => 'APPROUVEE',            'nom' => 'Approuvée',             'type_noeud' => 'terminal',   'ordre' => 90],
            ['code' => 'REJETEE',              'nom' => 'Rejetée',               'type_noeud' => 'terminal',   'ordre' => 91],
            ['code' => 'FINALISEE',            'nom' => 'Finalisée',             'type_noeud' => 'terminal',   'ordre' => 92],
            ['code' => 'ANNULEE',              'nom' => 'Annulée',               'type_noeud' => 'terminal',   'ordre' => 93],
            ['code' => 'COMPLEMENT_REQUIS',    'nom' => 'Complément requis',     'type_noeud' => 'intermediaire', 'ordre' => 0],
            ['code' => 'TRANSFERT_EN_ATTENTE', 'nom' => 'Transfert en attente',  'type_noeud' => 'intermediaire', 'ordre' => 0],
            ['code' => 'TRANSFERT_REFUSE',     'nom' => 'Transfert refusé',      'type_noeud' => 'intermediaire', 'ordre' => 0],
        ];

        foreach ($steps as $step) {
            DB::table('workflow_steps')->updateOrInsert(
                ['code' => $step['code'], 'service_id' => null, 'type_demande' => null],
                array_merge($step, ['service_id' => null, 'type_demande' => null, 'updated_at' => now()])
            );
        }
    }
}
