<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pension_requests', function (Blueprint $table) {
            $table->id();
            $table->string('certificat_carriere');
            $table->string('copie_moniteur');
            $table->string('acte_mariage');
            $table->string('acte_naissance');
            $table->string('acte_divorce')->nullable();
            $table->string('matricule_fiscal_cin');
            $table->json('photos');
            $table->string('certificat_medical');
            $table->string('souche_cheque');
            // Foreign key constraints
            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete()
                ->nullable(false);
            $table->foreignId('status_id')->constrained('statuses')->onDelete('restrict');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pension_requests');
    }
};