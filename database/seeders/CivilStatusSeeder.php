<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CivilStatusSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'célibataire', 'description' => ''],
            ['name' => 'marié(e)', 'description' => ''],
            ['name' => 'divorcé(e)', 'description' => ''],
            ['name' => 'veuf(ve)', 'description' => ''],
        ];

        DB::table('civil_status')->insert($statuses);
    }
}