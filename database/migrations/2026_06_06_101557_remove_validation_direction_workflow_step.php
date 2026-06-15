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
        // Remove VALIDATION_DIRECTION — replaced by SOUMISE step (also at Direction service).
        // First orphan its step_transitions, then delete.
        \DB::table('workflow_step_transitions')
            ->whereIn('from_step_id', fn($q) => $q->select('id')->from('workflow_steps')->where('code', 'VALIDATION_DIRECTION'))
            ->orWhereIn('to_step_id',   fn($q) => $q->select('id')->from('workflow_steps')->where('code', 'VALIDATION_DIRECTION'))
            ->delete();

        \DB::table('workflow_steps')->where('code', 'VALIDATION_DIRECTION')->delete();

        // Add BROUILLON step (no service — virtual usager step)
        \DB::table('workflow_steps')->updateOrInsert(
            ['code' => 'BROUILLON', 'type_demande' => null],
            ['nom' => 'Brouillon', 'description' => 'Dossier en cours de préparation par l\'usager', 'service_id' => null, 'ordre' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        // Add/update SOUMISE step (Direction service)
        $directionId = \DB::table('services')->where('code', 'direction')->value('id');
        if ($directionId) {
            \DB::table('workflow_steps')->updateOrInsert(
                ['code' => 'SOUMISE', 'type_demande' => null],
                ['nom' => 'Soumission initiale — Direction', 'description' => 'Dossier soumis et pris en charge par la Direction', 'service_id' => $directionId, 'ordre' => 5, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        // Not reversible — re-seed if needed
    }
};
