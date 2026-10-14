<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_beneficiaire_links', function (Blueprint $table) {
            $table->string('status')->default('active')->after('relation');
            $table->json('permissions')->nullable()->after('status');
            $table->text('motif')->nullable()->after('permissions');
            $table->foreignId('demande_par')->nullable()->after('motif')->constrained('users')->nullOnDelete();
            $table->foreignId('verifie_par')->nullable()->after('demande_par')->constrained('users')->nullOnDelete();
            $table->timestamp('verifie_le')->nullable()->after('verifie_par');
            $table->text('motif_decision')->nullable()->after('verifie_le');
        });

        DB::table('user_beneficiaire_links')->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('user_beneficiaire_links', function (Blueprint $table) {
            $table->dropConstrainedForeignId('demande_par');
            $table->dropConstrainedForeignId('verifie_par');
            $table->dropColumn([
                'status',
                'permissions',
                'motif',
                'verifie_le',
                'motif_decision',
            ]);
        });
    }
};
