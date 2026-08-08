<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_interactions', function (Blueprint $table) {
            $table->foreignId('to_step_id')
                ->nullable()
                ->after('to_service_id')
                ->constrained('workflow_steps')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demande_interactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('to_step_id');
        });
    }
};
