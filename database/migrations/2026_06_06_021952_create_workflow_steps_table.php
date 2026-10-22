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
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('nom');
            $table->string('description')->nullable();
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('type_demande')->nullable();
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->string('type_noeud')->default('intermediaire');
            $table->timestamps();

            $table->unique(['code', 'type_demande'], 'workflow_steps_code_type_unique');
        });

        Schema::table('demandes', function (Blueprint $table) {
            $table->foreignId('current_step_id')
                ->nullable()
                ->constrained('workflow_steps')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('current_step_id');
        });

        Schema::dropIfExists('workflow_steps');
    }
};
