<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('demande_id')->constrained('demandes')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // viewed, transferred, printed, downloaded
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['demande_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_activity_logs');
    }
};
