<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beneficiaires', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('nif')->nullable();
            $table->date('date_naissance')->nullable();
            $table->boolean('is_mineur')->default(false);
            $table->timestamps();

            $table->index('nif');
        });

        Schema::create('user_beneficiaire_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beneficiaire_id')->constrained()->cascadeOnDelete();
            $table->string('relation');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'beneficiaire_id', 'relation']);
        });

        Schema::create('affiliation_pensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('beneficiaire_id')->constrained()->cascadeOnDelete();
            $table->string('code_pension');
            $table->boolean('is_primary')->default(false);
            $table->string('loc_description')->nullable();
            $table->timestamps();

            $table->unique('code_pension');
            $table->index(['beneficiaire_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliation_pensions');
        Schema::dropIfExists('user_beneficiaire_links');
        Schema::dropIfExists('beneficiaires');
    }
};
