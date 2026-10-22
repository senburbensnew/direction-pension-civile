<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_circuit_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->json('snapshot');
            $table->timestamps();
        });

        Schema::create('demande_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['TRANSFERT', 'AVIS']);
            $table->foreignId('from_service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('to_service_id')->constrained('services');
            $table->foreignId('to_step_id')->nullable()->constrained('workflow_steps')->nullOnDelete();
            $table->foreignId('initiated_by')->constrained('users');
            $table->text('commentaire')->nullable();
            $table->enum('statut', ['EN_ATTENTE', 'ACCEPTE', 'REJETE', 'EN_COURS', 'TERMINE'])
                ->default('EN_ATTENTE');
            $table->text('reponse')->nullable();
            $table->timestamp('repondu_at')->nullable();
            $table->foreignId('repondu_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_interactions');
        Schema::dropIfExists('demande_circuit_snapshots');
    }
};
