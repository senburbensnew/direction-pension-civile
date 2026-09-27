<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('user_types')->insertOrIgnore([
            ['name' => 'pensionne'],
            ['name' => 'fonctionnaire'],
            ['name' => 'institution'],
            ['name' => 'externe'],
        ]);        
    }
}
