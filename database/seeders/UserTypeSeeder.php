<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('user_type')->insertOrIgnore([
            ['name' => 'pensionnaire'],
            ['name' => 'fonctionnaire'],
            ['name' => 'institution'],
        ]);        
    }
}
