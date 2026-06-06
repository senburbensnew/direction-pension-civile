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
        Schema::create('step_required_documents', function (Blueprint $table) {
            $table->id();
            // The service that requires this document before it can transmit the dossier further
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('type_demande', 100)->nullable();
            $table->string('label'); // human-readable document label
            $table->string('document_type'); // matches DemandeDocument.type
            $table->timestamps();

            $table->index(['service_id', 'type_demande']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('step_required_documents');
    }
};
