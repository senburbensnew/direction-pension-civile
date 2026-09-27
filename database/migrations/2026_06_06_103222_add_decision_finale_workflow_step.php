<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop the FK that currently depends on workflow_steps_service_type_unique
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        // 2. Now the unique index can be safely dropped
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropUnique('workflow_steps_service_type_unique');
        });

        // 3. Give the FK its own dedicated index (so it no longer depends on a unique)
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->index('service_id', 'workflow_steps_service_id_index');
        });

        // 4. Re-add the FK — adjust onDelete/onUpdate to match the original definition
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services')
                  ->cascadeOnDelete();
        });

        // 5. New unique constraint: one step per (code, type_demande)
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->unique(['code', 'type_demande'], 'workflow_steps_code_type_unique');
        });

        // 6. Seed the DECISION_FINALE step
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

        // 1. Drop FK before touching indexes
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
        });

        // 2. Drop the new unique constraint
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropUnique('workflow_steps_code_type_unique');
        });

        // 3. Restore the original unique — important: same name as the original!
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->unique(['service_id', 'type_demande'], 'workflow_steps_service_type_unique');
        });

        // 4. Drop the temporary plain index (FK can now rely on the unique again)
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->dropIndex('workflow_steps_service_id_index');
        });

        // 5. Re-add FK
        Schema::table('workflow_steps', function (Blueprint $table) {
            $table->foreign('service_id')
                  ->references('id')
                  ->on('services')
                  ->cascadeOnDelete();
        });
    }
};