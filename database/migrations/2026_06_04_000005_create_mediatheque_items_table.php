<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mediatheque_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // image, video, audio, document
            $table->string('file_path')->nullable();
            $table->string('url')->nullable(); // external URL (YouTube, etc.)
            $table->unsignedSmallInteger('order_column')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediatheque_items');
    }
};
