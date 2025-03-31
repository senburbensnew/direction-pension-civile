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
// In your request_histories migration file
Schema::create('request_histories', function (Blueprint $table) {
    $table->id();
    $table->string('request_id');
    $table->string('request_type');
    $table->json('request_data');
    $table->string('event_type');
    $table->datetime('event_date');
    $table->foreignId('created_by')
    ->constrained('users')
    ->restrictOnDelete()
    ->nullable(false);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_histories');
    }
};
