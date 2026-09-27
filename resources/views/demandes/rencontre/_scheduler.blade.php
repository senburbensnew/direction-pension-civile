@php
    $oldDate = old('date_souhaitee');
    $oldTime = old('heure_souhaitee');

    $schedulerInvalid =
        $errors->has('date_souhaitee') ||
        $errors->has('heure_souhaitee');

    /*
     * Le contrôleur expose $slotConfig (start/end/minutes).
     * Fallback sur les anciennes variables pour compat.
     */
    $slotMinutes = $slotConfig['minutes']
        ?? $rdvSlotMinutes
        ?? 15;

    $rdvStart = $slotConfig['start']
        ?? $rdvStart
        ?? '14:00';

    $rdvEnd = $slotConfig['end']
        ?? $rdvEnd
        ?? '16:00';
@endphp

<div class="mb-5">

    <p class="block text-base font-medium text-gray-700 mb-2">
        Date et heure <span class="text-red-500">*</span>
    </p>

    <p class="text-gray-600 mb-3">
        Sélectionnez un créneau disponible pour votre rendez-vous.
        Les rendez-vous sont organisés par créneaux de
        {{ $slotMinutes }} minutes,
        de {{ $rdvStart }} à {{ $rdvEnd }},
        du lundi au vendredi.
    </p>

    {{-- Valeur de la date sélectionnée --}}
    <input
        type="hidden"
        name="date_souhaitee"
        id="date_souhaitee"
        x-model="dateSouhaitee"
        value="{{ $oldDate }}"
    >

    {{-- Valeur de l'heure sélectionnée --}}
    <input
        type="hidden"
        name="heure_souhaitee"
        id="heure_souhaitee"
        x-model="heureSouhaitee"
        value="{{ $oldTime }}"
    >

    {{-- Scheduler --}}
    <div
        id="dpc-rdv-scheduler"
        class="dpc-rdv-scheduler border
            {{ $schedulerInvalid ? 'border-red-400' : 'border-gray-200' }}"
        data-date="{{ $oldDate }}"
        data-time="{{ $oldTime }}"
        data-slot-minutes="{{ $slotMinutes }}"
        data-min-time="{{ $rdvStart }}"
        data-max-time="{{ $rdvEnd }}"
        data-reserved='@json($reservedSlots ?? [])'
    ></div>

    {{-- Information sur l'attribution automatique --}}
    <div class="mt-4 rounded-xl border border-blue-100 bg-blue-50 p-4">
        <div class="flex items-start gap-3">

            <i class="fa-solid fa-circle-info text-blue-600 mt-0.5"></i>

            <div>
                <p class="font-semibold text-navy">
                    Attribution automatique
                </p>

                <p class="text-sm text-gray-600 mt-1">
                    Vous choisissez uniquement la date et l’heure.
                    Le système attribue automatiquement votre rendez-vous
                    à un agent disponible du service Formalités.
                </p>

                <p class="text-sm text-gray-600 mt-1">
                    Les rendez-vous sont organisés par créneaux de
                    {{ $slotMinutes }} minutes, du lundi au vendredi,
                    entre {{ $rdvStart }} et {{ $rdvEnd }}.
                </p>
            </div>

        </div>
    </div>

    {{-- Résumé du créneau sélectionné --}}
    <p
        id="dpc-rdv-selection"
        class="mt-3 text-base text-navy font-medium"
        aria-live="polite"
    ></p>

    {{-- Erreur date --}}
    @error('date_souhaitee')
        <p class="mt-1 text-base text-red-600">
            {{ $message }}
        </p>
    @enderror

    {{-- Erreur heure --}}
    @error('heure_souhaitee')
        <p class="mt-1 text-base text-red-600">
            {{ $message }}
        </p>
    @enderror

</div>