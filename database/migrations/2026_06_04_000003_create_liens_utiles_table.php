<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('liens_utiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('abbr')->nullable();
            $table->string('url');
            $table->string('category')->default('Gouvernement');
            $table->unsignedSmallInteger('order_column')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('liens_utiles');
    }
};
