<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('beneficiaires', 'galipec_pensionnaire_id')) {
            Schema::table('beneficiaires', function (Blueprint $table) {
                $table->dropColumn('galipec_pensionnaire_id');
            });
        }

        if (Schema::hasColumn('demande_creation_comptes', 'galipec_snapshot')) {
            Schema::table('demande_creation_comptes', function (Blueprint $table) {
                $table->dropColumn('galipec_snapshot');
            });
        }
    }

    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            if (! Schema::hasColumn('beneficiaires', 'galipec_pensionnaire_id')) {
                $table->string('galipec_pensionnaire_id')->nullable()->after('is_mineur');
            }
        });

        Schema::table('demande_creation_comptes', function (Blueprint $table) {
            if (! Schema::hasColumn('demande_creation_comptes', 'galipec_snapshot')) {
                $table->json('galipec_snapshot')->nullable();
            }
        });
    }
};
