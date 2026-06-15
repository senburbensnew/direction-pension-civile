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
        Schema::table('demandes', function (Blueprint $table) {
            $table->foreignId('current_step_id')
                ->nullable()
                ->after('current_service_id')
                ->constrained('workflow_steps')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropForeign(['current_step_id']);
            $table->dropColumn('current_step_id');
        });
    }
};
