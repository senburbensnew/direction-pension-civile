<?php

namespace App\Http\Controllers;

use App\Models\RdvAgentAvailability;
use App\Services\RencontreAvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RdvAgentAvailabilityController extends Controller
{
    public function edit(
        Request $request,
        RencontreAvailabilityService $availability
    ) {
        $user = $request->user();

        abort_unless(
            $user?->hasRole($availability->agentRole()),
            403
        );

        $rows = RdvAgentAvailability::query()
            ->where('user_id', $user->id)
            ->orderBy('available_on')
            ->orderBy('weekday')
            ->orderBy('start_time')
            ->get();

        $recurring = $rows
            ->where('is_blocked', false)
            ->whereNull('available_on')
            ->groupBy('weekday');

        $dateAvailabilities = $rows
            ->where('is_blocked', false)
            ->whereNotNull('available_on')
            ->values();

        $blocked = $rows
            ->where('is_blocked', true)
            ->values();

        $minDate = now()->toDateString();
        $maxDate = now()->addMonths(6)->toDateString();

        return view('demandes.rencontre.disponibilites', [
            'recurringAvailabilities' => $recurring,
            'dateAvailabilities' => $dateAvailabilities,
            'blockedAvailabilities' => $blocked,
            'holidayEvents' => $availability->holidayEvents(),
            'weekdays' => $availability->weekdayLabels(),
            'allowWeekends' => $availability->allowsWeekends(),
            'bookingDaysLabel' => $availability->bookingDaysLabel(),
            'globalStart' => $availability->bookingWindowStart(),
            'globalEnd' => $availability->bookingWindowEnd(),
            'calendarDisplay' => $availability->calendarDisplayWindow(),
            'slotMinutes' => $availability->slotMinutes(),
            'slotPresets' => $availability->slotPresets(),
            'minDate' => $minDate,
            'maxDate' => $maxDate,
        ]);
    }

    public function update(
        Request $request,
        RencontreAvailabilityService $availability
    ) {
        $user = $request->user();

        abort_unless(
            $user?->hasRole($availability->agentRole()),
            403
        );

        $minDate = now()->toDateString();
        $maxDate = now()->addMonths(6)->toDateString();
        $maxWeekday = $availability->allowsWeekends() ? 7 : 5;

        $validated = $request->validate([
            'recurring' => ['nullable', 'array'],
            'recurring.*.weekday' => ['required', 'integer', 'between:1,'.$maxWeekday],
            'recurring.*.start_time' => ['required', 'date_format:H:i'],
            'recurring.*.end_time' => ['required', 'date_format:H:i'],

            'dates' => ['nullable', 'array'],
            'dates.*.available_on' => ['required', 'date', 'after_or_equal:'.$minDate, 'before_or_equal:'.$maxDate],
            'dates.*.start_time' => ['required', 'date_format:H:i'],
            'dates.*.end_time' => ['required', 'date_format:H:i'],

            'blocks' => ['nullable', 'array'],
            'blocks.*.available_on' => ['nullable', 'date', 'after_or_equal:'.$minDate, 'before_or_equal:'.$maxDate],
            'blocks.*.weekday' => ['nullable', 'integer', 'between:1,'.$maxWeekday],
            'blocks.*.start_time' => ['nullable', 'date_format:H:i'],
            'blocks.*.end_time' => ['nullable', 'date_format:H:i'],
            'blocks.*.all_day' => ['nullable', 'boolean'],
        ]);

        $payload = [];

        foreach (array_values($validated['recurring'] ?? []) as $index => $window) {
            if (empty($window['start_time']) || empty($window['end_time'])) {
                continue;
            }

            if ($window['start_time'] >= $window['end_time']) {
                throw ValidationException::withMessages([
                    "recurring.{$index}.end_time" =>
                        'L’heure de fin doit être postérieure à l’heure de début.',
                ]);
            }

            $payload[] = [
                'available_on' => null,
                'weekday' => (int) $window['weekday'],
                'start_time' => $window['start_time'],
                'end_time' => $window['end_time'],
                'is_blocked' => false,
            ];
        }

        foreach (array_values($validated['dates'] ?? []) as $index => $window) {
            if (empty($window['available_on'])) {
                continue;
            }

            if ($window['start_time'] >= $window['end_time']) {
                throw ValidationException::withMessages([
                    "dates.{$index}.end_time" =>
                        'L’heure de fin doit être postérieure à l’heure de début.',
                ]);
            }

            $day = Carbon::parse($window['available_on']);

            if ($availability->isWeekendBlocked($day)) {
                throw ValidationException::withMessages([
                    "dates.{$index}.available_on" =>
                        'Les rendez-vous ne sont pas planifiés le week-end.',
                ]);
            }

            $payload[] = [
                'available_on' => $window['available_on'],
                'weekday' => (int) $day->dayOfWeekIso,
                'start_time' => $window['start_time'],
                'end_time' => $window['end_time'],
                'is_blocked' => false,
            ];
        }

        foreach (array_values($validated['blocks'] ?? []) as $index => $block) {
            $allDay = !empty($block['all_day']);
            $hasDate = filled($block['available_on'] ?? null);
            $hasWeekday = filled($block['weekday'] ?? null);

            if (!$hasDate && !$hasWeekday) {
                continue;
            }

            if ($hasDate && $hasWeekday) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.available_on" =>
                        'Choisissez une date précise ou un jour récurrent, pas les deux.',
                ]);
            }

            $start = $allDay ? '00:00' : ($block['start_time'] ?? null);
            $end = $allDay ? '23:59' : ($block['end_time'] ?? null);

            if (!$start || !$end) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.start_time" =>
                        'Indiquez une plage horaire ou cochez « journée entière ».',
                ]);
            }

            if ($start >= $end && !$allDay) {
                throw ValidationException::withMessages([
                    "blocks.{$index}.end_time" =>
                        'L’heure de fin doit être postérieure à l’heure de début.',
                ]);
            }

            $weekday = $hasWeekday
                ? (int) $block['weekday']
                : (int) Carbon::parse($block['available_on'])->dayOfWeekIso;

            if ($hasDate) {
                $day = Carbon::parse($block['available_on']);
                if ($availability->isWeekendBlocked($day)) {
                    throw ValidationException::withMessages([
                        "blocks.{$index}.available_on" =>
                            'Indisponibilité : date invalide (week-end).',
                    ]);
                }
            }

            $payload[] = [
                'available_on' => $hasDate ? $block['available_on'] : null,
                'weekday' => $weekday,
                'start_time' => $start,
                'end_time' => $end,
                'is_blocked' => true,
            ];
        }

        DB::transaction(function () use ($user, $payload) {
            RdvAgentAvailability::query()
                ->where('user_id', $user->id)
                ->delete();

            foreach ($payload as $row) {
                RdvAgentAvailability::create([
                    'user_id' => $user->id,
                    ...$row,
                ]);
            }
        });

        return redirect()
            ->route('rencontres.pilotage.disponibilites.edit')
            ->with(
                'success',
                'Vos disponibilités ont été enregistrées.'
            );
    }
}
