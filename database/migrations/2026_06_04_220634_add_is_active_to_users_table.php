<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy column removed in 2026_10_06_000002_drop_is_active_from_users_table.
    }

    public function down(): void
    {
        // No-op.
    }
};
