<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faq_items', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('category')->default('Général');
            $table->unsignedSmallInteger('order_column')->default(0);
            $table->boolean('published')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faq_items');
    }
};
