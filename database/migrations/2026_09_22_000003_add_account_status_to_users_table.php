<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'account_status')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('account_status')->default('actif')->after('remember_token');
            $table->index('account_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'account_status')) {
                $table->dropIndex(['account_status']);
                $table->dropColumn('account_status');
            }
        });
    }
};
