<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flux_transitions', function (Blueprint $table) {
            $table->boolean('is_urgent_only')->default(false)->after('ordre');
        });
    }

    public function down(): void
    {
        Schema::table('flux_transitions', function (Blueprint $table) {
            $table->dropColumn('is_urgent_only');
        });
    }
};
