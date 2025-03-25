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
            ['name' => 'carrière', 'description' => ''],
            ['name' => 'reversibilité', 'description' => ''],
            ['name' => 'civile', 'description' => ''],
            ['name' => 'militaire', 'description' => ''],
            ['name' => 'bndai', 'description' => ''],
            ['name' => 'minoterie', 'description' => ''],
            ['name' => 'sélection nationale', 'description' => ''],
        ]);        
    }
}
