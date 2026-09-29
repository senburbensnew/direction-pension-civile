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
            
            // --- Données brutes issues de l'Excel (vos noms exacts) ---
            // Tout est en string pour accepter les données telles quelles sans bloquer l'import.
            
            $table->string('PENSIONNAIRE_ID')->index(); // Ex: "8-18698"
            $table->string('LOCALITE_ID')->nullable();
            $table->string('LOC_DESCRIPTION')->nullable();
            $table->string('NATURE_ID')->nullable();
            
            // J'ai laissé la faute de frappe exacte de votre liste
            $table->string('NAT_DESCRITION')->nullable(); 
            
            $table->string('NIF')->nullable()->index();
            $table->string('NOM')->nullable()->index();
            $table->string('PRENOM')->nullable();
            $table->string('SEXE')->nullable();
            
            // En staging, on stocke la date telle qu'elle apparaît dans l'Excel
            $table->string('DATE_NAISSANCE')->nullable(); 
            
            $table->string('MONTANT_PENSION')->nullable();
            $table->string('CREATION_DATE')->nullable();
            $table->string('MODE_PAIEMENT')->nullable();
            $table->string('NO_COMPTE')->nullable();
            $table->string('BANK_ID')->nullable();

            $table->string('import_batch_id')->nullable(); // Identifiant du fichier importé

            $table->timestamps(); // created_at et updated_at
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