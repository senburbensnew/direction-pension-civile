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
        Schema::table('workflow_steps', function (Blueprint $table) {
            // Permet plusieurs steps par service (ex: Direction = SOUMISE + DECISION_FINALE)
            $table->dropUnique('workflow_steps_service_type_unique');
            $table->unique(['code', 'type_demande'], 'workflow_steps_code_type_unique');
        });

        $directionId = \DB::table('services')->where('code', 'direction')->value('id');
        if ($directionId) {
            \DB::table('workflow_steps')->updateOrInsert(
                ['code' => 'DECISION_FINALE', 'type_demande' => null],
                [
                    'nom'         => 'Décision finale',
                    'description' => 'Approbation ou rejet définitif du dossier par la Direction',
                    'service_id'  => $directionId,
                    'ordre'       => 100,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        \DB::table('workflow_steps')->where('code', 'DECISION_FINALE')->delete();

        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropUnique('workflow_steps_code_type_unique');
            $table->unique(['service_id', 'type_demande']);
        });
    }
};
