<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pension_mandats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beneficiaire_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type')->default('procuration');
            $table->string('status')->default('en_attente');
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->json('allowed_demande_types')->nullable();
            $table->json('allowed_pension_codes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();
            $table->text('refusal_reason')->nullable();
            $table->foreignId('demande_creation_compte_id')
                ->nullable()
                ->constrained('demande_creation_comptes')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['beneficiaire_id', 'status']);
            $table->unique('demande_creation_compte_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pension_mandats');
    }
};
