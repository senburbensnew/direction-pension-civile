<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Legacy column removed in 2026_10_07_000020_drop_pension_code_from_users_table.
    }

    public function down()
    {
        // No-op.
    }
};
