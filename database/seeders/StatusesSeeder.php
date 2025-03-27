<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'En attente de traitement', 'description' => ''],
            ['name' => 'Demande approuvée', 'description' => ''],
            ['name' => 'En cours de traitement', 'description' => ''],
            ['name' => 'Demande rejetée', 'description' => ''],
            ['name' => 'Traitement finalisé', 'description' => ''],
            ['name' => 'Demande annulée', 'description' => '']
        ];

        DB::table('statuses')->insert($statuses);
    }
}