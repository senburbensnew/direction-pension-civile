<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            // Snapshot of flux_transitions + required_circuit_services at submission time.
            // In-flight dossiers follow the circuit that was active when they were submitted.
            $table->json('circuit_snapshot')->nullable()->after('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropColumn('circuit_snapshot');
        });
    }
};
