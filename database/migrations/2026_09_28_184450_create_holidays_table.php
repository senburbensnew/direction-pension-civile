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

        Schema::create('holidays', function (Blueprint $table) {
            $table->id();

            $table->date('date')->unique();

            $table->string('name');

            $table->string('country', 2)->default('HT');

            $table->boolean('is_recurring')->default(false);

            $table->boolean('is_official')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['country', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
