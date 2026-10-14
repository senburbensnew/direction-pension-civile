<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            $table->json('informations_complementaires')->nullable()->after('is_mineur');
        });

        DB::table('beneficiaires')
            ->whereNotNull('nif')
            ->where('nif', '!=', '')
            ->orderBy('id')
            ->each(function (object $row): void {
                DB::table('beneficiaires')
                    ->where('id', $row->id)
                    ->update([
                        'informations_complementaires' => json_encode(['nif' => $row->nif], JSON_THROW_ON_ERROR),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('beneficiaires', function (Blueprint $table) {
            $table->dropColumn('informations_complementaires');
        });
    }
};
