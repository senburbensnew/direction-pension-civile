<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('glossaire_terms', function (Blueprint $table) {
            $table->id();
            $table->string('term');
            $table->text('definition');
            $table->string('category')->default('Général');
            $table->string('icon')->default('fa-book');
            $table->unsignedSmallInteger('order_column')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('glossaire_terms');
    }
};
