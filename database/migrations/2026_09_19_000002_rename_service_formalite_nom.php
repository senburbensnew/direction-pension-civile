<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('services')
            ->where('code', 'service_formalite')
            ->update(['nom' => 'Accueil et Formalités']);
    }

    public function down(): void
    {
        DB::table('services')
            ->where('code', 'service_formalite')
            ->update(['nom' => 'Service Accueil et Formalités']);
    }
};
