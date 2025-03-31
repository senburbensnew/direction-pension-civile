<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestType;

class RequestTypeSeeder extends Seeder
{
    public function run(): void
    {
        RequestType::insert([
            ['code' => 'bank_transfer_request', 'name' => 'Demande de virement', 'description' => ''],
            ['code' => 'check_transfer_request', 'name' => 'Demande de transfert de cheques', 'description' => ''],
            ['code' => 'payment_stop_request', 'name' => 'Demande d\'arret de virement', 'description' => ''],
            ['code' => 'existence_proof_request', 'name' => 'Demande de preuve d\'existence', 'description' => ''],
        ]);
    }
}
