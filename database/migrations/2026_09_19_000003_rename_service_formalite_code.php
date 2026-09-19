<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_CODE = 'service_formalite';
    private const NEW_CODE = 'service_accueil_formalites';

    public function up(): void
    {
        DB::table('services')
            ->where('code', self::OLD_CODE)
            ->update(['code' => self::NEW_CODE]);

        DB::table('roles')
            ->where('name', self::OLD_CODE)
            ->update(['name' => self::NEW_CODE]);
    }

    public function down(): void
    {
        DB::table('services')
            ->where('code', self::NEW_CODE)
            ->update(['code' => self::OLD_CODE]);

        DB::table('roles')
            ->where('name', self::NEW_CODE)
            ->update(['name' => self::OLD_CODE]);
    }
};
