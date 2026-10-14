<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demande_creation_comptes', function (Blueprint $table) {
            $table->boolean('mandataire_est_pensionne')->default(false)->after('is_mandataire');
        });
    }

    public function down(): void
    {
        Schema::table('demande_creation_comptes', function (Blueprint $table) {
            $table->dropColumn('mandataire_est_pensionne');
        });
    }
};
