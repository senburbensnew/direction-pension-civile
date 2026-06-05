<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();          // ministre, directeur-general, directeur
            $table->string('role');                     // Le Ministre, Directeur Général, Directrice
            $table->string('nom');
            $table->char('sexe', 1)->default('M');     // M or F
            $table->string('photo')->nullable();        // stored path
            $table->longText('biographie')->nullable();
            $table->longText('discours')->nullable();
            $table->string('citation', 500)->nullable(); // italic quote shown on speech page
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};
