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
        Schema::create('actualite_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actualite_id')
                  ->constrained()   // links to actualites table
                  ->onDelete('cascade');
            $table->string('image_path');  // store the path to the image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actualite_images');
    }
};
