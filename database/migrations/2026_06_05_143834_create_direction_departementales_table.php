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
        Schema::create('direction_departementales', function (Blueprint $table) {
            $table->id();
            $table->string('abbr');
            $table->string('nom');
            $table->string('ville');
            $table->string('color')->default('blue');
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('direction_departementales');
    }
};
