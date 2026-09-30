<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formalites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('annee_fiscale_id')
                ->constrained('annees_fiscales')
                ->restrictOnDelete();

            $table->timestamp('realisee_at');

            $table->foreignId('realisee_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('demande_id')
                ->nullable()
                ->constrained('demandes')
                ->nullOnDelete();

            $table->string('motif_key')->nullable();

            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->text('commentaire')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'annee_fiscale_id']);
            $table->index('realisee_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formalites');
    }
};