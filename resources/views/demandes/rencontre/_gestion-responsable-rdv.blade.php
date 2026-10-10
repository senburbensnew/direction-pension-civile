@if(($canManageRencontreRdv ?? false) && ($rdvPending ?? true))
    <div class="px-6 pb-6 border-t border-gray-100 pt-5 space-y-5">
        <div>
            <h4 class="text-sm font-bold text-gray-800">Pilotage — Responsable des formalités</h4>
            <p class="text-xs text-gray-500 mt-1">
                Réattribuez le rendez-vous à un autre agent ou modifiez la date et l’heure (report).
            </p>
        </div>

        @include('demandes.rencontre._reattribuer-agent', [
            'demande' => $demande,
            'bookingAgents' => $bookingAgents ?? collect(),
            'canReassignAgent' => true,
            'rdvPending' => $rdvPending ?? true,
        ])

        @php
            $peutReporter = in_array($rdvStatut ?? null, [
                \App\Enums\RencontreStatutEnum::ACTIF,
                \App\Enums\RencontreStatutEnum::ATTRIBUE,
                \App\Enums\RencontreStatutEnum::REPORTE,
            ], true);
        @endphp

        @if($peutReporter)
            <details class="group">
                <summary class="cursor-pointer inline-flex items-center gap-2 text-sm font-semibold text-blue-700 hover:text-blue-900">
                    <i class="fa-solid fa-calendar-day"></i>
                    Réassigner le créneau (date / heure)
                </summary>
                <form
                    method="POST"
                    action="{{ route('demandes.rencontre.proposerCreneau', $demande) }}"
                    class="mt-3 flex flex-wrap items-end gap-3 bg-blue-50 border border-blue-200 rounded-lg p-4"
                    onsubmit="return confirm('Confirmer le nouveau créneau pour ce rendez-vous ?');"
                >
                    @csrf
                    <input type="hidden" name="report" value="1">
                    <div class="min-w-[10rem]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nouvelle date</label>
                        <input type="date" name="date_souhaitee" required
                            min="{{ now()->toDateString() }}"
                            value="{{ old('date_souhaitee') }}"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="min-w-[8rem]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nouvelle heure</label>
                        @if(!empty($rdvTimes) && count($rdvTimes))
                            <select name="heure_souhaitee" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                @foreach($rdvTimes as $time)
                                    <option value="{{ $time }}" @selected(old('heure_souhaitee') === $time)>{{ $time }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="time" name="heure_souhaitee" required
                                value="{{ old('heure_souhaitee') }}"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @endif
                    </div>
                    @if(($bookingAgents ?? collect())->isNotEmpty())
                        <div class="min-w-[14rem] flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Agent (optionnel)</label>
                            <select name="agent_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                                <option value="">— Automatique —</option>
                                @foreach($bookingAgents as $agent)
                                    <option value="{{ $agent->id }}" @selected((int) old('agent_id', $demande->data['agent_id'] ?? 0) === (int) $agent->id)>
                                        {{ $agent->displayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg">
                        Enregistrer le créneau
                    </button>
                </form>
            </details>
        @endif
    </div>
@endif
