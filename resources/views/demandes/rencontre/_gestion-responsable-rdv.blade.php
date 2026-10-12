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
                    class="mt-3 border border-gray-200 bg-gray-50/80 p-4 space-y-4"
                    onsubmit="return confirm('Confirmer le nouveau créneau pour ce rendez-vous ?');"
                >
                    @csrf
                    <input type="hidden" name="report" value="1">
                    @if(! empty($rdvScheduler))
                        @include('demandes.rencontre._report-creneau-scheduler', [
                            'demande' => $demande,
                            'rdvScheduler' => $rdvScheduler,
                        ])
                    @else
                        <p class="text-sm text-gray-600">Configuration agenda indisponible.</p>
                    @endif
                    @if(($bookingAgents ?? collect())->isNotEmpty())
                        <div class="max-w-md">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Agent (optionnel)</label>
                            <select name="agent_id" class="w-full border border-gray-300 px-3 py-2 text-sm bg-white">
                                <option value="">— Automatique —</option>
                                @foreach($bookingAgents as $agent)
                                    <option value="{{ $agent->id }}" @selected((int) old('agent_id', $demande->data['agent_id'] ?? 0) === (int) $agent->id)>
                                        {{ $agent->displayName() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="flex justify-end">
                        <button type="submit" class="dossier-btn-primary">
                            Enregistrer le créneau
                        </button>
                    </div>
                </form>
            </details>
        @endif
    </div>
@endif
