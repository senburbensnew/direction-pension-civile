<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_login_events')) {
            return;
        }

        Schema::create('user_login_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('country_name', 120)->nullable();
            $table->string('region_name', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();

            $table->index('logged_in_at');
            $table->index(['user_id', 'logged_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_login_events');
    }
};
