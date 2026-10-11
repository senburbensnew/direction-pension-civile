@php
    $oldDate = old('date_souhaitee');
    $oldTime = old('heure_souhaitee');

    $defaultServiceCode = \App\Models\Service::FORMALITE;
    if (old('motif')) {
        $codes = $motifServiceCodes ?? [];
        $defaultServiceCode = $codes[old('motif')] ?? $defaultServiceCode;
    }

    $schedulerInvalid =
        $errors->has('date_souhaitee') ||
        $errors->has('heure_souhaitee');

    $slotMinutes = $slotConfig['minutes']
        ?? $rdvSlotMinutes
        ?? 15;

    $rdvStart = $slotConfig['start']
        ?? $rdvStart
        ?? '14:00';

    $rdvEnd = $slotConfig['end']
        ?? $rdvEnd
        ?? '16:00';

    $bookingDaysLabel = $bookingDaysLabel ?? 'du lundi au vendredi';
    $bookingHoursLabel = $bookingHoursLabel ?? ($rdvStart.' à '.$rdvEnd);
    $allowWeekends = $allowWeekends ?? false;
    $displayMin = $slotConfig['display_start'] ?? '08:00';
    $displayMax = $slotConfig['display_end'] ?? '18:00';
@endphp

<div class="mb-5" id="rdv-scheduler-section">

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-3">
        <div>
            <p class="block text-base font-semibold text-gray-800">
                Date et heure <span class="text-red-500">*</span>
            </p>
            <p class="text-sm text-gray-600 mt-0.5">
                Créneaux de {{ $slotMinutes }} min · {{ $bookingHoursLabel }} · {{ $bookingDaysLabel }}
                (hors jours fériés).
            </p>
        </div>
    </div>

    {{-- Créneau choisi (visible dans le formulaire Alpine) --}}
    <div
        class="mb-4 rounded-xl border-2 px-4 py-3 transition-colors"
        :class="dateSouhaitee && heureSouhaitee
            ? 'border-navy bg-[#f4f6f9]'
            : 'border-dashed border-gray-300 bg-gray-50'"
    >
        <template x-if="dateSouhaitee && heureSouhaitee">
            <div class="flex items-start gap-3">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-navy text-white">
                    <i class="fa-solid fa-calendar-check text-sm" aria-hidden="true"></i>
                </span>
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Créneau retenu</p>
                    <p class="text-base font-semibold text-navy" x-text="recapDateHeure()"></p>
                </div>
            </div>
        </template>
        <template x-if="!dateSouhaitee || !heureSouhaitee">
            <div class="flex items-start gap-3 text-gray-600">
                <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gray-200 text-gray-500">
                    <i class="fa-regular fa-calendar text-sm" aria-hidden="true"></i>
                </span>
                <p class="text-sm leading-relaxed">
                    Aucun créneau pour l’instant. Dans l’agenda ci-dessous, cliquez une
                    <strong class="text-gray-800">case blanche</strong> sur un jour à venir, dans la plage horaire.
                </p>
            </div>
        </template>
    </div>

    {{-- Légende --}}
    <div class="mb-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5 text-xs text-gray-600">
        <p class="font-semibold text-gray-800 mb-2">Légende</p>
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1.5">
            <li class="flex items-center gap-2">
                <span class="rdv-legend-swatch rdv-legend-available" aria-hidden="true"></span>
                Plage réservable — cliquez pour choisir
            </li>
            <li class="flex items-center gap-2">
                <span class="rdv-legend-swatch rdv-legend-muted" aria-hidden="true"></span>
                Hors plage horaire
            </li>
            <li class="flex items-center gap-2">
                <span class="rdv-legend-swatch rdv-legend-past" aria-hidden="true"></span>
                Créneau déjà passé (aujourd’hui)
            </li>
            <li class="flex items-center gap-2">
                <span class="rdv-legend-swatch rdv-legend-reserved" aria-hidden="true"></span>
                Indisponible (complet)
            </li>
        </ul>
    </div>

    <input
        type="hidden"
        name="date_souhaitee"
        id="date_souhaitee"
        x-model="dateSouhaitee"
        value="{{ $oldDate }}"
    >

    <input
        type="hidden"
        name="heure_souhaitee"
        id="heure_souhaitee"
        x-model="heureSouhaitee"
        value="{{ $oldTime }}"
    >

    <div
        id="dpc-rdv-scheduler"
        class="dpc-rdv-scheduler border rounded-xl overflow-hidden
            {{ $schedulerInvalid ? 'border-red-400' : 'border-gray-200' }}"
        data-date="{{ $oldDate }}"
        data-time="{{ $oldTime }}"
        data-slot-minutes="{{ $slotMinutes }}"
        data-min-time="{{ $rdvStart }}"
        data-max-time="{{ $rdvEnd }}"
        data-display-min-time="{{ $displayMin }}"
        data-display-max-time="{{ $displayMax }}"
        data-reserved-by-service='@json($reservedSlotsByService ?? [])'
        data-holidays='@json($holidayEvents ?? [])'
        data-allow-weekends="{{ $allowWeekends ? '1' : '0' }}"
        data-booking-days-label="{{ $bookingDaysLabel }}"
        data-booking-hours-label="{{ $bookingHoursLabel }}"
        data-default-service="{{ $defaultServiceCode }}"
    ></div>

    <p
        id="dpc-rdv-selection"
        class="mt-3 min-h-[1.25rem] text-sm font-medium"
        aria-live="polite"
    ></p>

    <p class="mt-2 text-xs text-gray-500 flex items-start gap-2">
        <i class="fa-solid fa-user-check text-navy/70 mt-0.5 shrink-0" aria-hidden="true"></i>
        <span>
            Un agent disponible vous sera attribué automatiquement selon le motif choisi.
            Vous n’avez pas à sélectionner d’agent.
        </span>
    </p>

    @error('date_souhaitee')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror

    @error('heure_souhaitee')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror

</div>
