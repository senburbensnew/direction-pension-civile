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
    $table->foreignId('request_id')->constrained('bank_transfer_requests')->onDelete('cascade');
    $table->string('title');
    $table->text('description');
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
