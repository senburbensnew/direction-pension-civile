<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaire_relation_demandes', function (Blueprint $table) {
            $table->foreignId('demande_creation_compte_id')
                ->nullable()
                ->after('user_beneficiaire_link_id')
                ->constrained('demande_creation_comptes')
                ->nullOnDelete();

            $table->unique('demande_creation_compte_id');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiaire_relation_demandes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_creation_compte_id');
        });
    }
};
