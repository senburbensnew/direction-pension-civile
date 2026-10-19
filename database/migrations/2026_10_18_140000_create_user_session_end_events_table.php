<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_session_end_events')) {
            return;
        }

        Schema::create('user_session_end_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('initiated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 32);
            $table->string('session_id', 128)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('country_name')->nullable();
            $table->string('region_name')->nullable();
            $table->string('city')->nullable();
            $table->timestamp('ended_at');
            $table->timestamps();

            $table->index('ended_at');
            $table->index(['user_id', 'ended_at']);
            $table->index('reason');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_session_end_events');
    }
};
