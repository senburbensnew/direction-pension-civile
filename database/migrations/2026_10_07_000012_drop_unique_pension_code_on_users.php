<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'pension_code')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['pension_code']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'pension_code')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('pension_code');
        });
    }
};
