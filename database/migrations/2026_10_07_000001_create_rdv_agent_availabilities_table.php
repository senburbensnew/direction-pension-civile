<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rdv_agent_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('available_on')->nullable();
            $table->unsignedTinyInteger('weekday');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_blocked')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'weekday']);
            $table->index(['user_id', 'available_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rdv_agent_availabilities');
    }
};
