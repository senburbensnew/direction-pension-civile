<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rdv_agent_availabilities', function (Blueprint $table) {
            $table->date('available_on')->nullable()->after('user_id');
            $table->boolean('is_blocked')->default(false)->after('end_time');

            $table->index(['user_id', 'available_on']);
        });
    }

    public function down(): void
    {
        Schema::table('rdv_agent_availabilities', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'available_on']);
            $table->dropColumn(['available_on', 'is_blocked']);
        });
    }
};
