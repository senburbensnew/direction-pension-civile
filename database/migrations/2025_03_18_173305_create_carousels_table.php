<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carousels', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image');
            $table->boolean('status')->default(true);
            $table->string('link')->nullable();
            $table->string('overlay_position')->default('bottom-left');
            $table->string('cta_label')->nullable();
            $table->string('text_size')->default('md');
            $table->string('text_color', 20)->default('#ffffff');
            $table->json('text_styles')->nullable();
            $table->string('font_family', 50)->default('sans');
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carousels');
    }
};
