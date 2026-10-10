<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('account_status', 'provisoire')
            ->update(['account_status' => 'en_attente_validation']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('account_status', 'en_attente_validation')
            ->update(['account_status' => 'provisoire']);
    }
};
