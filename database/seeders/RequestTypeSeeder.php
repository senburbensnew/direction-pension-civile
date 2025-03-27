<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestType;

class RequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        RequestType::insert([
            ['name' => 'Demande de virement', 'description' => ''],
            ['name' => 'Demande de transfert de cheques', 'description' => ''],
            ['name' => 'Demande d\'arret de virement', 'description' => ''],
        ]);
    }
}
