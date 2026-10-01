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
        Schema::create('pensionnaires_reference', function (Blueprint $table) {
            // Clé primaire technique pour Laravel
            $table->id();

            // --- Données brutes issues de l'Excel ---
            // Tous les champs sont en TEXT et nullable
            $table->text('PENSIONNAIRE_ID')->nullable();
            $table->text('LOCALITE_ID')->nullable();
            $table->text('LOC_DESCRIPTION')->nullable();
            $table->text('NATURE_ID')->nullable();
            $table->text('NAT_DESCRITION')->nullable();
            $table->text('NIF')->nullable();
            $table->text('NOM')->nullable();
            $table->text('PRENOM')->nullable();
            $table->text('SEXE')->nullable();
            $table->text('DATE_NAISSANCE')->nullable();
            $table->text('CHEQUE_ID')->nullable();
            $table->text('MONTANT_PENSION')->nullable();
            $table->text('MONTANT_AVAL')->nullable();
            $table->text('CREATION_DATE')->nullable();
            $table->text('MODE_PAIEMENT')->nullable();
            $table->text('NO_COMPTE')->nullable();
            $table->text('BANK_ID')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pensionnaires_reference');
    }
};