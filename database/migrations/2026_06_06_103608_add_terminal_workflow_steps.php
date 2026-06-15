<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $directionId = \DB::table('services')->where('code', 'direction')->value('id');
        if (!$directionId) return;

        $terminals = [
            ['code' => 'APPROUVEE', 'nom' => 'Approuvée',        'description' => 'Dossier approuvé par la Direction',            'ordre' => 110],
            ['code' => 'FINALISEE', 'nom' => 'Clôturée',         'description' => 'Dossier finalisé et clôturé par la Direction',  'ordre' => 120],
            ['code' => 'REJETEE',   'nom' => 'Rejetée',          'description' => 'Dossier rejeté par la Direction',               'ordre' => 130],
            ['code' => 'ANNULEE',   'nom' => 'Annulée',          'description' => 'Dossier annulé par la Direction',               'ordre' => 140],
        ];

        foreach ($terminals as $t) {
            \DB::table('workflow_steps')->updateOrInsert(
                ['code' => $t['code'], 'type_demande' => null],
                array_merge($t, ['service_id' => $directionId, 'type_demande' => null, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // Transitions depuis DECISION_FINALE vers chaque terminal
        $decisionStep = \DB::table('workflow_steps')->where('code', 'DECISION_FINALE')->whereNull('type_demande')->first();
        if (!$decisionStep) return;

        $actions = [
            'APPROUVEE' => ['action' => 'Approuver', 'ordre' => 110],
            'FINALISEE' => ['action' => 'Clôturer',  'ordre' => 120],
            'REJETEE'   => ['action' => 'Rejeter',   'ordre' => 130],
            'ANNULEE'   => ['action' => 'Annuler',   'ordre' => 140],
        ];

        foreach ($actions as $code => $meta) {
            $toStep = \DB::table('workflow_steps')->where('code', $code)->whereNull('type_demande')->first();
            if (!$toStep) continue;
            \DB::table('workflow_step_transitions')->updateOrInsert(
                ['from_step_id' => $decisionStep->id, 'to_step_id' => $toStep->id],
                ['action' => $meta['action'], 'is_urgent_only' => 0, 'ordre' => $meta['ordre'], 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        $codes = ['APPROUVEE', 'FINALISEE', 'REJETEE', 'ANNULEE'];
        $stepIds = \DB::table('workflow_steps')->whereIn('code', $codes)->pluck('id');
        \DB::table('workflow_step_transitions')->whereIn('to_step_id', $stepIds)->delete();
        \DB::table('workflow_steps')->whereIn('code', $codes)->delete();
    }
};
