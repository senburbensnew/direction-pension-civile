<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annees_fiscales', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('libelle')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->boolean('active')->default(false);
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();

            $table->index('active');
            $table->index('ordre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annees_fiscales');
    }
};