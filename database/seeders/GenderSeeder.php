<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Insert default gender values
        DB::table('gender')->insert([
            ['name' => 'Masculin'],
            ['name' => 'Féminin'],
            ['name' => 'Autre'],
        ]);
    }
}
