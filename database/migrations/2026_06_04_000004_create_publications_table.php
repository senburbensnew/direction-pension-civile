<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('document'); // loi, decret, circulaire, document, texte
            $table->string('file_path')->nullable();
            $table->string('url')->nullable();
            $table->unsignedSmallInteger('order_column')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publications');
    }
};
