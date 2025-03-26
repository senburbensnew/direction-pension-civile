<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PensionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Inserting some example pension types into the table
        DB::table('pension_type')->insert([
            ['name' => 'Pension de carrière', 'description' => ''],
            ['name' => 'Pension de réversibilité (veuf(ve))', 'description' => ''],
        ]);
    }
}