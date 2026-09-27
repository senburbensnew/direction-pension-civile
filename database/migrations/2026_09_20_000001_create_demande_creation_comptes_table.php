<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_creation_comptes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('user_type');

            // Identité
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('name')->nullable();

            // Coordonnées / identifiants de connexion
            $table->string('email')->nullable();
            $table->string('username')->nullable();          // ← AJOUT
            $table->string('telephone')->nullable();
            $table->string('adresse', 500)->nullable();

            // Identifiants administratifs
            $table->string('nif')->nullable();
            $table->string('ninu')->nullable();
            $table->string('pension_code')->nullable();

            // Mineur / représentant
            $table->boolean('is_mineur')->default(false);
            $table->string('representant_lien')->nullable();
            $table->string('piece_identite_representant_type')->nullable();

            // Données OCR et vérification
            $table->json('pieces_identite')->nullable();
            $table->json('ocr_fields')->nullable();
            $table->json('ocr_documents')->nullable();       // ← AJOUT
            $table->json('galipec_snapshot')->nullable();
            $table->json('verification_mismatches')->nullable();
            $table->json('submitted_payload')->nullable();   // ← AJOUT

            // Décision / traitement
            $table->text('message')->nullable();
            $table->text('refusal_reason')->nullable();      // ← AJOUT
            $table->timestamp('accepted_terms_at')->nullable();
            $table->string('status')->default('en_attente');
            $table->timestamp('reviewed_at')->nullable();    // ← AJOUT
            $table->foreignId('reviewed_by')                 // ← AJOUT
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Relations
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();

            $table->index('email');
            $table->index('nif');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_creation_comptes');
    }
};