<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $allDay = DB::table('parameters')
            ->where('name', 'rdv_allow_all_day_hours')
            ->value('value') === 'true';

        $start = $allDay ? '00:00' : '14:00';
        $end = $allDay ? '24:00' : '16:00';

        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_booking_start'],
            [
                'name' => 'rdv_booking_start',
                'description' => 'Heure de début des créneaux réservables (HH:MM)',
                'value' => $start,
            ]
        );

        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_booking_end'],
            [
                'name' => 'rdv_booking_end',
                'description' => 'Heure de fin des créneaux réservables (HH:MM, 24:00 = fin de journée)',
                'value' => $end,
            ]
        );

        DB::table('parameters')
            ->where('name', 'rdv_allow_all_day_hours')
            ->delete();
    }

    public function down(): void
    {
        DB::table('parameters')->whereIn('name', [
            'rdv_booking_start',
            'rdv_booking_end',
        ])->delete();

        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_allow_all_day_hours'],
            [
                'name' => 'rdv_allow_all_day_hours',
                'description' => 'Autoriser la réservation à toute heure de la journée',
                'value' => 'false',
            ]
        );
    }
};
