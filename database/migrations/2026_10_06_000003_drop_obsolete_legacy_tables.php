<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Legacy tables replaced by demandes / demande_histories (models removed from app).
     */
    public function up(): void
    {
        Schema::dropIfExists('request_histories');
        Schema::dropIfExists('request_types');
        Schema::dropIfExists('error_logs');
    }

    public function down(): void
    {
        // Intentionally empty — obsolete schema, not restored.
    }
};
