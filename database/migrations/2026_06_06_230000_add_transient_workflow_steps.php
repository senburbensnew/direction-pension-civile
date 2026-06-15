<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $transients = [
            [
                'code'       => 'COMPLEMENT_REQUIS',
                'nom'        => 'Complément requis',
                'description'=> 'Dossier en attente de pièces complémentaires de l\'usager',
                'type_noeud' => 'intermediaire',
                'ordre'      => 0,
            ],
            [
                'code'       => 'TRANSFERT_EN_ATTENTE',
                'nom'        => 'Transfert en attente',
                'description'=> 'Dossier transféré, en attente d\'acceptation par le service destinataire',
                'type_noeud' => 'intermediaire',
                'ordre'      => 0,
            ],
            [
                'code'       => 'TRANSFERT_REFUSE',
                'nom'        => 'Transfert refusé',
                'description'=> 'Transfert refusé par le service destinataire',
                'type_noeud' => 'intermediaire',
                'ordre'      => 0,
            ],
        ];

        foreach ($transients as $t) {
            \DB::table('workflow_steps')->updateOrInsert(
                ['code' => $t['code'], 'type_demande' => null],
                array_merge($t, [
                    'service_id'   => null,
                    'type_demande' => null,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ])
            );
        }
    }

    public function down(): void
    {
        \DB::table('workflow_steps')
            ->whereIn('code', ['COMPLEMENT_REQUIS', 'TRANSFERT_EN_ATTENTE', 'TRANSFERT_REFUSE'])
            ->whereNull('type_demande')
            ->delete();
    }
};
