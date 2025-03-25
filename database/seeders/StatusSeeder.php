<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'en attente', 'description' => ''],
            ['name' => 'approuvé(e)', 'description' => ''],
            ['name' => 'en cours', 'description' => ''],
            ['name' => 'rejeté(e)', 'description' => ''],
            ['name' => 'traité(e)', 'description' => ''],
            ['name' => 'annulé(e)', 'description' => '']
        ];

        DB::table('status')->insert($statuses);
    }
}