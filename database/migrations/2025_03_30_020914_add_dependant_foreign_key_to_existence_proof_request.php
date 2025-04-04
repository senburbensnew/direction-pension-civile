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
        Schema::table('dependants', function (Blueprint $table) {
            $table->foreignId('existence_proof_request_id')->constrained('existence_proof_requests')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dependants', function (Blueprint $table) {
            $table->dropForeign('existence_proof_requests_id');
            $table->dropColumn('existence_proof_requests_id');
        });
    }
};
