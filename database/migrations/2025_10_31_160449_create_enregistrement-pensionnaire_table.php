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
        Schema::create('enregistrement_pensionnaire', function (Blueprint $table) {
            $table->id();

            // Personal Information
            $table->string('code_pensionnaire');
            $table->string('cin_pensionnaire');
            $table->string('nif_pensionnaire');
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('no_moniteur');
            $table->string('photo');

            // Pension Information
            $table->date('date_liquidation_pension');
            $table->date('date_pension');

            // ENUMS REPLACED BY STRING
            $table->string('statut_pension');
            $table->string('mode_paiement');
            $table->string('aval');
            $table->decimal('montant_pension', 10, 2);
            $table->string('personne_contact');
            $table->string('mandataire');

            // Classification & Location
            $table->string('localite');
            $table->string('statut_matrimonial');
            $table->string('cat_pension');
            $table->string('type_pension');
            $table->string('plan_assurance');

            // Dependants as JSON
            $table->json('dependants');

            // Contact Information
            $table->text('adresse');
            $table->string('telephone');
            $table->string('email');

            // Declaration
            $table->boolean('declaration');

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('code_pensionnaire');
            $table->index('nif_pensionnaire');
            $table->index('nom');
            $table->index('statut_pension');
            $table->index('localite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enregistrement_pensionnaire');
    }
};
