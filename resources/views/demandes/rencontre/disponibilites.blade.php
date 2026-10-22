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

    $fieldClass = 'dpc-filter-field w-full text-sm';
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mes disponibilités
        </h2>
    </x-slot>

    <x-portal-page bottom="pb-28" class="space-y-6 dpc-square-ui">
        <div class="p-4 sm:p-6 bg-white shadow sm:rounded-lg border border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="max-w-2xl">
                    <p class="text-sm font-medium text-[#173052]">Planification agent RDV</p>
                    <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                        Indiquez quand vous pouvez recevoir des rendez-vous (créneaux de {{ $slotMinutes }}&nbsp;min,
                        plage {{ $globalStart }}–{{ $globalEnd }}, hors week-ends et jours fériés haïtiens). Ces règles
                        sont utilisées lors de l’affectation automatique ou manuelle des RDV.
                    </p>
                    <ol class="mt-4 space-y-1.5 text-sm text-gray-600 list-decimal list-inside">
                        <li>Choisissez un <strong class="text-gray-800">mode</strong> à gauche (ou ci-dessous sur mobile).</li>
                        <li><strong class="text-gray-800">Glissez</strong> sur le calendrier pour ajouter une plage.</li>
                        <li>Ajustez les listes si besoin, puis <strong class="text-gray-800">Enregistrer</strong>.</li>
                    </ol>
                </div>
                <div class="flex flex-wrap gap-2 text-xs shrink-0 lg:max-w-md">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-200 font-medium text-gray-700">
                        <span class="rdv-legend-swatch rdv-legend-swatch--date w-2.5 h-2.5 rounded-sm"></span> Date
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-200 font-medium text-gray-700">
                        <span class="rdv-legend-swatch rdv-legend-swatch--recurring w-2.5 h-2.5 rounded-sm"></span> Récurrent
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-200 font-medium text-gray-700">
                        <span class="rdv-legend-swatch rdv-legend-swatch--block-date w-2.5 h-2.5 rounded-sm"></span> Bloqué (date)
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-200 font-medium text-gray-700">
                        <span class="rdv-legend-swatch rdv-legend-swatch--block-weekday w-2.5 h-2.5 rounded-sm"></span> Bloqué (jour)
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-gray-50 border border-gray-200 font-medium text-gray-700">
                        <span class="rdv-legend-swatch rdv-legend-swatch--holiday w-2.5 h-2.5 rounded-sm"></span> Férié
                    </span>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 text-sm space-y-1 rounded-lg" role="alert">
                @foreach($errors->all() as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('rencontres.pilotage.disponibilites.update') }}"
            class="space-y-5"
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
                dirty: false,
                sectionOpen: {
                    recurring: @js(count($recurringInitial) > 0),
                    dates: @js(count($datesInitial) > 0),
                    blocks: @js(count($blocksInitial) > 0),
                },
                paintModeLabels: {
                    date: 'Date précise',
                    recurring: 'Récurrent (chaque semaine)',
                    block_date: 'Bloquer une date',
                    block_weekday: 'Bloquer un jour récurrent',
                },
                markDirty() { this.dirty = true; },
                addRecurring(day) {
                    this.recurring.push({
                        weekday: day,
                        start_time: this.globalStart,
                        end_time: this.globalEnd,
                    });
                    this.sectionOpen.recurring = true;
                    this.markDirty();
                },
                addRecurringPreset(day, preset) {
                    this.recurring.push({
                        weekday: day,
                        start_time: preset.start,
                        end_time: preset.end,
                    });
                    this.sectionOpen.recurring = true;
                    this.markDirty();
                },
                removeRecurring(index) {
                    this.recurring.splice(index, 1);
                    this.markDirty();
                },
                addDateRow() {
                    this.dates.push({
                        available_on: '',
                        start_time: this.globalStart,
                        end_time: this.globalEnd,
                    });
                    this.sectionOpen.dates = true;
                    this.markDirty();
                },
                addDatePreset(preset) {
                    this.dates.push({
                        available_on: '',
                        start_time: preset.start,
                        end_time: preset.end,
                    });
                    this.sectionOpen.dates = true;
                    this.markDirty();
                },
                removeDate(index) {
                    this.dates.splice(index, 1);
                    this.markDirty();
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
                    this.sectionOpen.blocks = true;
                    this.markDirty();
                },
                removeBlock(index) {
                    this.blocks.splice(index, 1);
                    this.markDirty();
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
                $watch('paintMode', () => refreshCalendar());
            "
            @change="markDirty()"
            @submit="dirty = false"
        >
            @csrf

            <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 xl:items-start">
                {{-- Panneau modes + résumé --}}
                <aside class="xl:col-span-3 space-y-4 order-2 xl:order-1 xl:sticky xl:top-20">
                    <section class="bg-white shadow sm:rounded-lg border border-gray-100 p-4 sm:p-5 space-y-3">
                        <h2 class="text-sm font-semibold text-gray-900">Mode de saisie</h2>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            <strong>Clic droit</strong> sur le calendrier : changer de mode ou bloquer une journée entière.
                        </p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([
                                'date' => [
                                    'label' => 'Date précise',
                                    'active' => 'bg-[#059669] text-white border-[#047857]',
                                    'idle' => 'border-[#059669]/35 hover:bg-emerald-50/80',
                                    'swatch' => 'rdv-legend-swatch--date',
                                ],
                                'recurring' => [
                                    'label' => 'Récurrent',
                                    'active' => 'bg-navy text-white border-navy',
                                    'idle' => 'border-navy/35 hover:bg-[#173052]/5',
                                    'swatch' => 'rdv-legend-swatch--recurring',
                                ],
                                'block_date' => [
                                    'label' => 'Bloquer date',
                                    'active' => 'bg-[#dc2626] text-white border-[#b91c1c]',
                                    'idle' => 'border-[#dc2626]/35 hover:bg-red-50/80',
                                    'swatch' => 'rdv-legend-swatch--block-date',
                                ],
                                'block_weekday' => [
                                    'label' => 'Bloquer jour',
                                    'active' => 'bg-[#6d28d9] text-white border-[#5b21b6]',
                                    'idle' => 'border-[#6d28d9]/35 hover:bg-violet-50/80',
                                    'swatch' => 'rdv-legend-swatch--block-weekday',
                                ],
                            ] as $mode => $cfg)
                                <button type="button" @click="paintMode = '{{ $mode }}'"
                                    class="w-full px-3 py-2.5 text-xs sm:text-sm font-semibold border rounded-md text-left transition-colors flex items-center gap-2"
                                    :class="paintMode === '{{ $mode }}' ? '{{ $cfg['active'] }}' : 'bg-white text-gray-800 border-gray-200 {{ $cfg['idle'] }}'"
                                    :aria-pressed="paintMode === '{{ $mode }}' ? 'true' : 'false'">
                                    <span class="rdv-legend-swatch {{ $cfg['swatch'] }} w-2.5 h-2.5 rounded-sm shrink-0"></span>
                                    {{ $cfg['label'] }}
                                </button>
                            @endforeach
                        </div>
                        <div class="rounded-md bg-slate-50 border border-slate-100 px-3 py-2"
                             :class="{
                                'border-[#059669]/40 bg-emerald-50/40': paintMode === 'date',
                                'border-navy/30 bg-[#173052]/5': paintMode === 'recurring',
                                'border-[#dc2626]/35 bg-red-50/40': paintMode === 'block_date',
                                'border-[#6d28d9]/35 bg-violet-50/40': paintMode === 'block_weekday',
                             }">
                            <p class="text-xs font-medium text-gray-500">Mode actif</p>
                            <p class="text-sm font-semibold pt-0.5"
                               :class="{
                                    'text-[#047857]': paintMode === 'date',
                                    'text-navy': paintMode === 'recurring',
                                    'text-[#b91c1c]': paintMode === 'block_date',
                                    'text-[#5b21b6]': paintMode === 'block_weekday',
                               }"
                               x-text="paintModeLabels[paintMode]"></p>
                            <p id="dpc-agent-availability-hint" class="text-xs text-gray-600 mt-1 min-h-[2rem]"></p>
                        </div>
                    </section>

                    <section class="bg-white shadow sm:rounded-lg border border-gray-100 p-4 sm:p-5 text-sm space-y-2">
                        <h2 class="font-semibold text-gray-900">Résumé</h2>
                        <ul class="space-y-1 text-gray-700">
                            <li><span x-text="recurring.length"></span> plage(s) récurrente(s)</li>
                            <li><span x-text="dates.length"></span> date(s) précise(s)</li>
                            <li><span x-text="blocks.length"></span> indisponibilité(s)</li>
                        </ul>
                        <p class="text-xs text-gray-500 pt-1">
                            Fenêtre : {{ $minDate }} → {{ $maxDate }} · {{ $bookingDaysLabel ?? 'lun.–ven.' }}
                        </p>
                    </section>
                </aside>

                {{-- Calendrier --}}
                <section class="xl:col-span-9 bg-white shadow sm:rounded-lg border border-gray-100 p-4 sm:p-5 order-1 xl:order-2">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                        <h2 class="text-lg font-semibold text-gray-900">Calendrier</h2>
                        <p class="text-xs text-gray-500">Glisser pour peindre · molette pour naviguer</p>
                    </div>
                    <div
                        id="dpc-agent-availability-calendar"
                        class="dpc-agent-availability-scheduler border border-gray-200 rounded-lg overflow-hidden min-h-[28rem] xl:min-h-[34rem] transition-colors duration-200"
                        data-paint-mode="date"
                        x-effect="$el.dataset.paintMode = paintMode"
                        data-slot-minutes="{{ $slotMinutes }}"
                        data-min-time="{{ $globalStart }}"
                        data-max-time="{{ $globalEnd }}"
                        data-max-date="{{ $maxDate }}"
                        data-holidays='@json($holidayEvents ?? [])'
                        data-allow-weekends="{{ ($allowWeekends ?? false) ? '1' : '0' }}"
                        data-display-min-time="{{ $calendarDisplay['display_start'] ?? $globalStart }}"
                        data-display-max-time="{{ $calendarDisplay['display_end'] ?? $globalEnd }}"
                    ></div>
                </section>
            </div>

            {{-- Horaires récurrents --}}
            <section class="bg-white shadow sm:rounded-lg border border-gray-100 overflow-hidden">
                <button type="button"
                    class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left hover:bg-gray-50/80"
                    @click="sectionOpen.recurring = !sectionOpen.recurring">
                    <span>
                        <span class="text-lg font-semibold text-gray-900">Horaires récurrents</span>
                        <span class="ml-2 text-sm text-gray-500" x-text="'(' + recurring.length + ')'"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform shrink-0"
                         :class="sectionOpen.recurring ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sectionOpen.recurring" x-cloak x-transition class="px-5 pb-5 space-y-4 border-t border-gray-100">
                    <p class="text-sm text-gray-600 pt-3">
                        Chaque semaine ({{ $bookingDaysLabel ?? 'du lundi au vendredi' }}), sauf date précise ou blocage ce jour-là.
                        Sans ligne : plage générale {{ $globalStart }}–{{ $globalEnd }}.
                    </p>

                    <template x-if="recurring.length === 0">
                        <p class="text-sm text-gray-500 italic">Aucune plage récurrente.</p>
                    </template>

                    <template x-for="(row, index) in recurring" :key="'r-' + index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end border-b border-gray-100 pb-4">
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Jour</label>
                                <select
                                    :name="`recurring[${index}][weekday]`"
                                    x-model.number="row.weekday"
                                    class="{{ $fieldClass }}"
                                >
                                    @foreach($weekdays as $num => $label)
                                        <option value="{{ $num }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                                <input type="time" :name="`recurring[${index}][start_time]`" x-model="row.start_time"
                                    class="{{ $fieldClass }}" step="900">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                                <input type="time" :name="`recurring[${index}][end_time]`" x-model="row.end_time"
                                    class="{{ $fieldClass }}" step="900">
                            </div>
                            <div class="sm:col-span-3">
                                <button type="button" @click="removeRecurring(index)"
                                    class="w-full px-3 py-2 text-sm text-red-700 border border-red-200 hover:bg-red-50">
                                    Retirer
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="flex flex-wrap gap-2">
                        @foreach($weekdays as $num => $label)
                            <button type="button" @click="addRecurring({{ $num }})"
                                class="px-3 py-1.5 text-xs font-semibold bg-slate-100 text-navy hover:bg-slate-200">
                                + {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <details class="text-sm">
                        <summary class="cursor-pointer text-navy font-medium">Créneaux de {{ $slotMinutes }} min (lundi par défaut)</summary>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="preset in slotPresets" :key="preset.label">
                                <button type="button"
                                    @click="addRecurring(1); recurring[recurring.length-1].start_time = preset.start; recurring[recurring.length-1].end_time = preset.end; sectionOpen.recurring = true;"
                                    class="px-2 py-1 text-xs bg-gray-100 border border-gray-200"
                                    x-text="preset.label"></button>
                            </template>
                        </div>
                    </details>
                </div>
            </section>

            {{-- Dates précises --}}
            <section class="bg-white shadow sm:rounded-lg border border-gray-100 overflow-hidden">
                <button type="button"
                    class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left hover:bg-gray-50"
                    @click="sectionOpen.dates = !sectionOpen.dates">
                    <span>
                        <span class="text-lg font-semibold text-gray-900">Dates précises</span>
                        <span class="ml-2 text-sm text-gray-500" x-text="'(' + dates.length + ')'"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform shrink-0"
                         :class="sectionOpen.dates ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sectionOpen.dates" x-cloak x-transition class="px-5 pb-5 space-y-4 border-t border-gray-100">
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                        <p class="text-sm text-gray-600">Prioritaires sur la récurrence pour le jour concerné.</p>
                        <button type="button" @click="addDateRow()"
                            class="px-4 py-2 text-sm font-semibold bg-navy text-white hover:bg-orange-600 transition-colors">
                            + Ajouter une date
                        </button>
                    </div>

                    <template x-if="dates.length === 0">
                        <p class="text-sm text-gray-500 italic">Aucune date précise.</p>
                    </template>

                    <template x-for="(row, index) in dates" :key="'d-' + index">
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end border-b border-gray-100 pb-4">
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                <input type="date" :name="`dates[${index}][available_on]`" x-model="row.available_on"
                                    :min="minDate" :max="maxDate"
                                    class="{{ $fieldClass }}">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Début</label>
                                <input type="time" :name="`dates[${index}][start_time]`" x-model="row.start_time"
                                    class="{{ $fieldClass }}" step="900">
                            </div>
                            <div class="sm:col-span-3">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                                <input type="time" :name="`dates[${index}][end_time]`" x-model="row.end_time"
                                    class="{{ $fieldClass }}" step="900">
                            </div>
                            <div class="sm:col-span-2">
                                <button type="button" @click="removeDate(index)"
                                    class="w-full px-3 py-2 text-sm text-red-700 border border-red-200">
                                    Retirer
                                </button>
                            </div>
                        </div>
                    </template>

                    <details class="text-sm">
                        <summary class="cursor-pointer text-navy font-medium">Créneaux types</summary>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="preset in slotPresets" :key="'dp-' + preset.label">
                                <button type="button" @click="addDatePreset(preset)"
                                    class="px-2 py-1 text-xs bg-gray-100 border border-gray-200"
                                    x-text="preset.label"></button>
                            </template>
                        </div>
                    </details>
                </div>
            </section>

            {{-- Indisponibilités --}}
            <section class="bg-white shadow sm:rounded-lg border border-red-100 overflow-hidden">
                <button type="button"
                    class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left hover:bg-red-50/40"
                    @click="sectionOpen.blocks = !sectionOpen.blocks">
                    <span>
                        <span class="text-lg font-semibold text-gray-900">Indisponibilités</span>
                        <span class="ml-2 text-sm text-gray-500" x-text="'(' + blocks.length + ')'"></span>
                    </span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform shrink-0"
                         :class="sectionOpen.blocks ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="sectionOpen.blocks" x-cloak x-transition class="px-5 pb-5 space-y-4 border-t border-red-50">
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-3">
                        <p class="text-sm text-gray-600">Congés, formations, absences partielles ou journées entières.</p>
                        <div class="flex gap-2">
                            <button type="button" @click="addBlock('date')"
                                class="px-3 py-2 text-sm font-semibold border border-red-200 text-red-800 hover:bg-red-50">
                                + Date
                            </button>
                            <button type="button" @click="addBlock('weekday')"
                                class="px-3 py-2 text-sm font-semibold border border-red-200 text-red-800 hover:bg-red-50">
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
                                        class="{{ $fieldClass }}">
                                </div>
                            </template>
                            <template x-if="row.mode === 'weekday'">
                                <div class="sm:col-span-4">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Chaque</label>
                                    <select :name="`blocks[${index}][weekday]`" x-model.number="row.weekday"
                                        class="{{ $fieldClass }}">
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
                                        class="{{ $fieldClass }}" step="900">
                                </div>
                            </template>
                            <template x-if="!row.all_day">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Fin</label>
                                    <input type="time" :name="`blocks[${index}][end_time]`" x-model="row.end_time"
                                        class="{{ $fieldClass }}" step="900">
                                </div>
                            </template>
                            <div class="sm:col-span-2">
                                <button type="button" @click="removeBlock(index)"
                                    class="w-full px-3 py-2 text-sm text-red-700 border border-red-200">
                                    Retirer
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </section>

            {{-- Barre d'enregistrement fixe --}}
            <div class="fixed bottom-0 right-0 left-0 z-30 border-t border-gray-200 bg-white/95 backdrop-blur-sm shadow-[0_-4px_20px_rgba(0,0,0,0.08)] transition-[left] duration-200 lg:left-60 group-data-[collapsed=true]/body:lg:left-[4.25rem]">
                <div class="site-gutter py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 min-w-0">
                    <p class="text-sm text-gray-600 min-w-0">
                        <span x-show="dirty" x-cloak class="inline-flex items-center gap-1.5 font-medium text-amber-800">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            Modifications non enregistrées
                        </span>
                        <span x-show="!dirty" class="text-gray-500">L’enregistrement remplace toutes vos règles actuelles.</span>
                    </p>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-md bg-[#173052] text-white text-sm font-semibold hover:bg-orange-600 transition-colors shrink-0 shadow-sm disabled:opacity-50"
                        :disabled="!dirty">
                        <i class="fas fa-save mr-1.5 text-xs"></i>
                        Enregistrer
                    </button>
                </div>
            </div>
        </form>
    </x-portal-page>
</x-app-layout>
