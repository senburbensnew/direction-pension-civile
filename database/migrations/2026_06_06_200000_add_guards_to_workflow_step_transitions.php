<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_step_transitions', function (Blueprint $table) {
            // Spatie permission slug required to execute this transition (null = any authenticated user)
            $table->string('required_permission', 100)->nullable()->after('is_urgent_only');
            // JSON map of guard conditions evaluated against the demande before allowing the transition
            // Supported keys: docs_complets (bool), avis_complets (bool), avis_favorables (bool)
            $table->json('guard_conditions')->nullable()->after('required_permission');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_step_transitions', function (Blueprint $table) {
            $table->dropColumn(['required_permission', 'guard_conditions']);
        });
    }
};
