<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RencontreAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RdvSettingsController extends Controller
{
    public function index(RencontreAvailabilityService $availability)
    {
        return view('admin.rdv-settings.index', [
            'allowWeekends' => $availability->allowsWeekends(),
            'bookingStart' => $availability->startTime(),
            'bookingEnd' => $availability->endTime(),
            'slotMinutes' => $availability->slotMinutes(),
            'bookingHoursLabel' => $availability->bookingHoursLabel(),
            'bookingDaysLabel' => $availability->bookingDaysLabel(),
        ]);
    }

    public function update(Request $request, RencontreAvailabilityService $availability)
    {
        $validated = $request->validate([
            'allow_weekends' => ['nullable', 'boolean'],
            'booking_start' => ['required', 'date_format:H:i'],
            'booking_end' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$|^24:00$/'],
        ]);

        $start = $availability->normalizeBookingTime($validated['booking_start']);
        $end = $availability->normalizeBookingTime($validated['booking_end']);

        if (! $start || ! $end) {
            throw ValidationException::withMessages([
                'booking_start' => 'Heure de début invalide.',
                'booking_end' => 'Heure de fin invalide.',
            ]);
        }

        if ($end !== '24:00' && $start >= $end) {
            throw ValidationException::withMessages([
                'booking_end' => 'L’heure de fin doit être postérieure à l’heure de début (ou 24:00 pour la fin de journée).',
            ]);
        }

        $weekends = ! empty($validated['allow_weekends']);

        DB::table('parameters')->updateOrInsert(
            ['name' => 'rdv_allow_weekends'],
            [
                'name' => 'rdv_allow_weekends',
                'description' => 'Autoriser la réservation le week-end (samedi et dimanche)',
                'value' => $weekends ? 'true' : 'false',
            ]
        );

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

        Cache::forget('rdv_allow_weekends');
        Cache::forget('rdv_booking_start');
        Cache::forget('rdv_booking_end');

        return back()->with('success', 'Paramètres des rendez-vous enregistrés.');
    }
}
