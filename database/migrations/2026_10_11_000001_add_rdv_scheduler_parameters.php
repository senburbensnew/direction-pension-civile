<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $rows = [
            [
                'name' => 'rdv_allow_weekends',
                'description' => 'Autoriser la réservation le week-end (samedi et dimanche)',
                'value' => 'false',
            ],
            [
                'name' => 'rdv_allow_all_day_hours',
                'description' => 'Autoriser la réservation à toute heure de la journée',
                'value' => 'false',
            ],
        ];

        foreach ($rows as $row) {
            DB::table('parameters')->updateOrInsert(
                ['name' => $row['name']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'value' => $row['value'],
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('parameters')->whereIn('name', [
            'rdv_allow_weekends',
            'rdv_allow_all_day_hours',
        ])->delete();
    }
};
