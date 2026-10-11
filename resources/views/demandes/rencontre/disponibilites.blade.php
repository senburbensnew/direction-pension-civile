@php
    $formatTime = fn ($value) => substr((string) $value, 0, 5);

    $recurringInitial = ($recurringAvailabilities ?? collect())
        ->flatMap(fn ($rows, $day) => $rows->map(fn ($r) => [
            'weekday' => (int) $day,
            'start_time' => $formatTime($r->start_time),
            'end_time' => $formatTime($r->end_time),
        ]))
        ->values()
        ->all();

    $datesInitial = ($dateAvailabilities ?? collect())
        ->map(fn ($r) => [
            'available_on' => $r->available_on?->format('Y-m-d'),
            'start_time' => $formatTime($r->start_time),
            'end_time' => $formatTime($r->end_time),
        ])
        ->values()
        ->all();

    $blocksInitial = ($blockedAvailabilities ?? collect())
        ->map(function ($r) use ($formatTime) {
            $start = $formatTime($r->start_time);
            $end = $formatTime($r->end_time);

            return [
                'available_on' => $r->available_on?->format('Y-m-d') ?? '',
                'weekday' => $r->available_on ? '' : (int) $r->weekday,
                'start_time' => $start,
                'end_time' => $end,
                'all_day' => $start === '00:00' && $end === '23:59',
                'mode' => $r->available_on ? 'date' : 'weekday',
            ];
        })
        ->values()
        ->all();
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-navy">Mes disponibilités pour les rendez-vous</h1>
            <p class="text-sm text-gray-600 mt-2 leading-relaxed max-w-4xl">
                Utilisez le <strong>calendrier</strong> (comme pour la prise de rendez-vous) ou les listes ci-dessous.
                Créneaux usagers : {{ $slotMinutes }} min, plage {{ $globalStart }}–{{ $globalEnd }} par défaut.
            </p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm space-y-1">
                @foreach($errors->all() as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('rencontres.pilotage.disponibilites.update') }}"
            class="space-y-6"
            x-data="{
                recurring: @js($recurringInitial),
                dates: @js($datesInitial),
                blocks: @js($blocksInitial),
                slotPresets: @js($slotPresets),
                globalStart: @js($globalStart),
                globalEnd: @js($globalEnd),
                minDate: @js($minDate),
                maxDate: @js($maxDate),
                paintMode: 'date',
                addRecurring(day) {
                    this.recurring.push({
                        weekday: day,
                        start_time: this.globalStart,
                        end_time: this.globalEnd,
                    });
                },
                addRecurringPreset(day, preset) {
                    this.recurring.push({
                        weekday: day,
                        start_time: preset.start,
                        end_time: preset.end,
                    });
                },
                removeRecurring(index) {
                    this.recurring.splice(index, 1);
                },
                addDateRow() {
                    this.dates.push({
                        available_on: '',
                        start_time: this.globalStart,
                        end_time: this.globalEnd,
                    });
                },
                addDatePreset(preset) {
                    this.dates.push({
                        available_on: '',
                        start_time: preset.start,
                        end_time: preset.end,
                    });
                },
                removeDate(index) {
                    this.dates.splice(index, 1);
                },
                addBlock(mode = 'date') {
                    this.blocks.push({
                        mode,
                        available_on: '',
                        weekday: mode === 'weekday' ? 1 : '',
                        start_time: this.globalStart,
                        end_time: this.globalEnd,
                        all_day: false,
                    });
                },
                removeBlock(index) {
                    this.blocks.splice(index, 1);
                },
                refreshCalendar() {
                    window.dispatchEvent(new CustomEvent('dpc-availability-refresh'));
                },
            }"
            x-init="
                window.__dpcAgentAvailability = $data;
                window.dispatchEvent(new Event('dpc-agent-availability-ready'));
                $watch('recurring', () => refreshCalendar());
                $watch('dates', () => refreshCalendar());
                $watch('blocks', () => refreshCalendar());
            "
        >
            @csrf

            {{-- Calendrier (FullCalendar — même stack que la prise de RDV) --}}
            <section class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-6 space-y-4">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Calendrier</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            <strong>Glissez</strong> sur une plage horaire pour ajouter (mode actif).
                            <strong>Clic droit</strong> : menu (autres modes, plage complète).
                            Clic sur un bloc : retirer.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide self-center mr-1">
                            Mode
                        </label>
                        <button type="button" @click="paintMode = 'date'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors"
                            :class="paintMode === 'date' ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-gray-700 border-gray-200'">
                            Date précise
                        </button>
                        <button type="button" @click="paintMode = 'recurring'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors"
                            :class="paintMode === 'recurring' ? 'bg-navy text-white border-navy' : 'bg-white text-gray-700 border-gray-200'">
                            Récurrent
                        </button>
                        <button type="button" @click="paintMode = 'block_date'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors"
                            :class="paintMode === 'block_date' ? 'bg-red-600 text-white border-red-600' : 'bg-white text-gray-700 border-gray-200'">
                            Bloquer date
                        </button>
                        <button type="button" @click="paintMode = 'block_weekday'"
                            class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors"
                            :class="paintMode === 'block_weekday' ? 'bg-violet-700 text-white border-violet-700' : 'bg-white text-gray-700 border-gray-200'">
                            Bloquer jour
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-600">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-600"></span> Date précise</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-[#173052]"></span> Récurrent</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-red-600"></span> Bloqué (date)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-violet-700"></span> Bloqué (jour récurrent)</span>
                </div>

                <p id="dpc-agent-availability-hint" class="text-sm text-gray-600"></p>

                <div
                    id="dpc-agent-availability-calendar"
                    class="dpc-rdv-scheduler dpc-agent-availability-scheduler border-2 min-h-[32rem] transition-colors duration-200"
                    data-paint-mode="date"
                    x-effect="$el.dataset.paintMode = paintMode"
                    data-slot-minutes="{{ $slotMinutes }}"
                    data-min-time="{{ $globalStart }}"
                    data-max-time="{{ $globalEnd }}"
                    data-max-date="{{ $maxDate }}"
                    data-holidays='@json($holidayEvents ?? [])'
                    data-allow-weekends="{{ ($allowWeekends ?? false) ? '1' : '0' }}"
                    data-display-min-time="{{ $calendarDisplay['display_start'] ?? '08:00' }}"
                    data-display-max-time="{{ $calendarDisplay['display_end'] ?? '18:00' }}"
                ></div>
            </section>

            {{-- Récurrence hebdomadaire --}}
            <section class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Horaires récurrents</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Plages qui s’appliquent chaque semaine ({{ $bookingDaysLabel ?? 'du lundi au vendredi' }}), sauf si une date précise ou une indisponibilité prime ce jour-là.
                    </p>
                </div>

                <template x-if="recurring.length === 0">
                    <p class="text-sm text-gray-500 italic">Aucune plage récurrente — plage générale {{ $globalStart }}–{{ $globalEnd }}.</p>
                </template>

                <template x-for="(row, index) in recurring" :key="'r-' + index">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end border-b border-gray-100 pb-4">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Jour</label>
                            <select
                                :name="`recurring[${index}][weekday]`"
                                x-model.number="row.weekday"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"
                            >
                                @foreach($weekdays as $num => $label)
                                    <option value="{{ $num }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                            <input type="time" :name="`recurring[${index}][start_time]`" x-model="row.start_time"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" step="900">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                            <input type="time" :name="`recurring[${index}][end_time]`" x-model="row.end_time"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" step="900">
                        </div>
                        <div class="sm:col-span-3 flex flex-wrap gap-2">
                            <button type="button" @click="removeRecurring(index)"
                                class="px-3 py-2 text-sm text-red-700 border border-red-200 rounded-lg hover:bg-red-50">
                                Retirer
                            </button>
                        </div>
                    </div>
                </template>

                <div class="flex flex-wrap gap-2 pt-1">
                    @foreach($weekdays as $num => $label)
                        <button type="button" @click="addRecurring({{ $num }})"
                            class="px-3 py-1.5 text-xs font-semibold bg-slate-100 text-navy rounded-lg hover:bg-slate-200">
                            + {{ $label }} (plage)
                        </button>
                    @endforeach
                </div>

                <details class="text-sm">
                    <summary class="cursor-pointer text-navy font-medium">Ajouter un créneau unitaire ({{ $slotMinutes }} min)</summary>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="preset in slotPresets" :key="preset.label">
                            <button type="button"
                                @click="addRecurring(1); recurring[recurring.length-1].start_time = preset.start; recurring[recurring.length-1].end_time = preset.end;"
                                class="px-2 py-1 text-xs bg-gray-100 rounded border border-gray-200"
                                x-text="preset.label"></button>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Le bouton ajoute une ligne (lundi par défaut) — changez le jour si besoin.</p>
                </details>
            </section>

            {{-- Dates précises --}}
            <section class="bg-white border border-gray-200 rounded-2xl p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Dates précises</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Pour un jour donné uniquement (prioritaire sur la récurrence ce jour-là).
                            Du {{ $minDate }} au {{ $maxDate }}.
                        </p>
                    </div>
                    <button type="button" @click="addDateRow()"
                        class="px-4 py-2 text-sm font-semibold bg-navy text-white rounded-lg">
                        + Date
                    </button>
                </div>

                <template x-if="dates.length === 0">
                    <p class="text-sm text-gray-500 italic">Aucune date précise enregistrée.</p>
                </template>

                <template x-for="(row, index) in dates" :key="'d-' + index">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end border-b border-gray-100 pb-4">
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                            <input type="date" :name="`dates[${index}][available_on]`" x-model="row.available_on"
                                :min="minDate" :max="maxDate"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                            <input type="time" :name="`dates[${index}][start_time]`" x-model="row.start_time"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" step="900">
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                            <input type="time" :name="`dates[${index}][end_time]`" x-model="row.end_time"
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" step="900">
                        </div>
                        <div class="sm:col-span-2">
                            <button type="button" @click="removeDate(index)"
                                class="w-full px-3 py-2 text-sm text-red-700 border border-red-200 rounded-lg">
                                Retirer
                            </button>
                        </div>
                    </div>
                </template>

                <details class="text-sm">
                    <summary class="cursor-pointer text-navy font-medium">Créneaux types pour une date</summary>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <template x-for="preset in slotPresets" :key="'dp-' + preset.label">
                            <button type="button" @click="addDatePreset(preset)"
                                class="px-2 py-1 text-xs bg-gray-100 rounded border border-gray-200"
                                x-text="preset.label"></button>
                        </template>
                    </div>
                </details>
            </section>

            {{-- Indisponibilités --}}
            <section class="bg-white border border-red-100 rounded-2xl p-6 space-y-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Indisponibilités</h2>
                        <p class="text-sm text-gray-600 mt-1">
                            Bloquez une date ou un jour récurrent (congé, formation, etc.).
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="addBlock('date')"
                            class="px-3 py-2 text-sm font-semibold border border-red-200 text-red-800 rounded-lg">
                            + Date bloquée
                        </button>
                        <button type="button" @click="addBlock('weekday')"
                            class="px-3 py-2 text-sm font-semibold border border-red-200 text-red-800 rounded-lg">
                            + Jour récurrent
                        </button>
                    </div>
                </div>

                <template x-if="blocks.length === 0">
                    <p class="text-sm text-gray-500 italic">Aucune indisponibilité.</p>
                </template>

                <template x-for="(row, index) in blocks" :key="'b-' + index">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end border-b border-gray-100 pb-4">
                        <template x-if="row.mode === 'date'">
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                <input type="date" :name="`blocks[${index}][available_on]`" x-model="row.available_on"
                                    :min="minDate" :max="maxDate"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            </div>
                        </template>
                        <template x-if="row.mode === 'weekday'">
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Chaque</label>
                                <select :name="`blocks[${index}][weekday]`" x-model.number="row.weekday"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                                    @foreach($weekdays as $num => $label)
                                        <option value="{{ $num }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </template>
                        <div class="sm:col-span-2 flex items-center pb-2">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" :name="`blocks[${index}][all_day]`" value="1"
                                    x-model="row.all_day" class="rounded border-gray-300">
                                Journée entière
                            </label>
                        </div>
                        <template x-if="!row.all_day">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                                <input type="time" :name="`blocks[${index}][start_time]`" x-model="row.start_time"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" step="900">
                            </div>
                        </template>
                        <template x-if="!row.all_day">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                                <input type="time" :name="`blocks[${index}][end_time]`" x-model="row.end_time"
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" step="900">
                            </div>
                        </template>
                        <div class="sm:col-span-2">
                            <button type="button" @click="removeBlock(index)"
                                class="w-full px-3 py-2 text-sm text-red-700 border border-red-200 rounded-lg">
                                Retirer
                            </button>
                        </div>
                    </div>
                </template>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-orange-600 transition-colors">
                    Enregistrer toutes les disponibilités
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
