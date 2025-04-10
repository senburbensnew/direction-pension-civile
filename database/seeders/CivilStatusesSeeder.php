<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CivilStatusesSeeder extends Seeder
{
    public function run()
    {
        $statuses = [
            ['name' => 'célibataire', 'description' => ''],
            ['name' => 'marié(e)', 'description' => ''],
            ['name' => 'divorcé(e)', 'description' => ''],
            ['name' => 'veuf(ve)', 'description' => ''],
            ['name' => 'concubinage', 'description' => ''],
        ];

        DB::table('civil_statuses')->insert($statuses);
    }
}