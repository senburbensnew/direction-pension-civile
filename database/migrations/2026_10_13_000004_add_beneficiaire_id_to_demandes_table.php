<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->foreignId('beneficiaire_id')
                ->nullable()
                ->after('created_by')
                ->constrained('beneficiaires')
                ->nullOnDelete();

            $table->index('beneficiaire_id');
        });
    }

    public function down(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('beneficiaire_id');
        });
    }
};
