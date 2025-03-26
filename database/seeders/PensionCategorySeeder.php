<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PensionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('pension_category')->insert([
            ['name' => 'Civile', 'description' => ''],
            ['name' => 'Militaire', 'description' => ''],
            ['name' => 'BNDAI', 'description' => ''],
            ['name' => 'Minoterie', 'description' => ''],
            ['name' => 'Sélection Nationale', 'description' => ''],
        ]);        
    }
}
