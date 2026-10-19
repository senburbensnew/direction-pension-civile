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
        if (Schema::hasColumn('users', 'service_id')) {
            return;
        }

        $after = Schema::hasColumn('users', 'signature')
            ? 'signature'
            : (Schema::hasColumn('users', 'address') ? 'address' : 'remember_token');

        Schema::table('users', function (Blueprint $table) use ($after) {
            $table->foreignId('service_id')
                ->nullable()
                ->after($after)
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('users', 'service_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn('service_id');
        });
    }
};
