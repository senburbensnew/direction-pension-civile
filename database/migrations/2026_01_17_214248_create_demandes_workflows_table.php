<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demande_workflows', function (Blueprint $table) {
            $table->id();

            $table->foreignId('demande_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->foreignId('to_service_id')
                ->nullable()
                ->constrained('services')
                ->nullOnDelete();

            $table->foreignId('status_id')
                ->constrained('statuses');

            $table->foreignId('action_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('commentaire')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demande_workflows');
    }
};