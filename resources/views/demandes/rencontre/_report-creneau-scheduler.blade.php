{{--
    Sélection date/heure pour un report (FullCalendar — rdv-scheduler.js / flatpickr stack via app.js).
    Requiert $rdvScheduler (reservedSlotsByService, holidayEvents, slotConfig, …).
--}}
@php
    $demande = $demande ?? $request ?? null;
    abort_unless($demande, 500);

    $cfg = $rdvScheduler ?? [];
    $slotConfig = $cfg['slotConfig'] ?? [];
    $slotMinutes = $slotConfig['minutes'] ?? 15;
    $rdvStart = $slotConfig['start'] ?? '14:00';
    $rdvEnd = $slotConfig['end'] ?? '16:00';
    $displayMin = $slotConfig['display_start'] ?? $rdvStart;
    $displayMax = $slotConfig['display_end'] ?? $rdvEnd;
    $oldDate = old('date_souhaitee', $demande->data['date_souhaitee'] ?? '');
    $oldTime = old('heure_souhaitee', $demande->data['heure_souhaitee'] ?? '');
    $oldTime = $oldTime ? \App\Models\Demande::normalizeRencontreTime($oldTime) : '';
    $bookingDaysLabel = $cfg['bookingDaysLabel'] ?? 'du lundi au vendredi';
    $bookingHoursLabel = $cfg['bookingHoursLabel'] ?? ($rdvStart.' à '.$rdvEnd);
    $allowWeekends = $cfg['allowWeekends'] ?? false;
    $defaultService = $cfg['defaultService'] ?? \App\Models\Service::FORMALITE;
    $schedulerInvalid = $errors->has('date_souhaitee') || $errors->has('heure_souhaitee');
@endphp

<div class="space-y-3">
    <p class="text-xs text-gray-600">
        Créneaux de {{ $slotMinutes }} min · {{ $bookingHoursLabel }} · {{ $bookingDaysLabel }} (hors jours fériés).
        Cliquez une plage libre dans l’agenda.
    </p>

    <input type="hidden" name="date_souhaitee" value="{{ $oldDate }}">
    <input type="hidden" name="heure_souhaitee" value="{{ $oldTime }}">

    <div
        class="dpc-rdv-scheduler border overflow-hidden bg-white {{ $schedulerInvalid ? 'border-red-400' : 'border-gray-200' }}"
        data-date="{{ $oldDate }}"
        data-time="{{ $oldTime }}"
        data-slot-minutes="{{ $slotMinutes }}"
        data-min-time="{{ $rdvStart }}"
        data-max-time="{{ $rdvEnd }}"
        data-display-min-time="{{ $displayMin }}"
        data-display-max-time="{{ $displayMax }}"
        data-reserved-by-service='@json($cfg['reservedSlotsByService'] ?? [])'
        data-holidays='@json($cfg['holidayEvents'] ?? [])'
        data-allow-weekends="{{ $allowWeekends ? '1' : '0' }}"
        data-booking-days-label="{{ $bookingDaysLabel }}"
        data-booking-hours-label="{{ $bookingHoursLabel }}"
        data-default-service="{{ $defaultService }}"
    ></div>

    <p
        data-dpc-rdv-selection
        class="min-h-[1.25rem] text-sm font-medium text-[#173052]"
        aria-live="polite"
    ></p>

    @error('date_souhaitee')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('heure_souhaitee')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
