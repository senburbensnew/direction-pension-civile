<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affectations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->enum('statut', ['EN_ATTENTE', 'EN_COURS', 'TERMINE', 'REJETE'])->default('EN_ATTENTE');
            $table->text('avis')->nullable();
            $table->foreignId('affecte_par_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('date_affectation')->useCurrent();
            $table->timestamp('date_reponse')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affectations');
    }
};
