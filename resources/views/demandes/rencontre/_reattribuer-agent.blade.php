@if(($canReassignAgent ?? false) && ($rdvPending ?? true))
    @php
        $reattribuerAction = $reattribuerRoute
            ?? route('demandes.rencontre.reattribuer', $demande);
        $standalone = (bool) ($reassignStandalone ?? false);
    @endphp
    <div @class([
        'border-t border-gray-100 pt-5 mb-5' => ! $standalone,
        'space-y-3' => $standalone,
    ])>
        @if($standalone)
            <p class="text-sm font-semibold text-navy">
                <i class="fa-solid fa-user-pen mr-1"></i>
                Choisir un nouvel agent
            </p>
        @else
            <details class="group">
                <summary class="cursor-pointer inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-navy/80">
                    <i class="fa-solid fa-user-pen"></i>
                    Réattribuer à un autre agent
                </summary>
        @endif
            <form
                method="POST"
                action="{{ $reattribuerAction }}"
                @class([
                    'mt-3 flex flex-wrap items-end gap-3 bg-slate-50 border border-gray-200 p-4',
                    'mt-0' => $standalone,
                ])
                onsubmit="return confirm('Confirmer la réattribution de ce rendez-vous ?');"
            >
                @csrf
                @php
                    $reattribuerAgentOptions = collect($bookingAgents ?? [])
                        ->map(fn ($agent) => ['value' => (string) $agent->id, 'label' => $agent->displayName()])
                        ->prepend(['value' => '', 'label' => 'Choisir un agent'])
                        ->values()
                        ->all();
                    $reattribuerSelectedAgent = (string) (($demande->data['agent_id'] ?? '') ?: '');
                @endphp
                <div class="min-w-[14rem] flex-1">
                    <x-searchable-select
                        name="agent_id"
                        label="Nouvel agent"
                        :options="$reattribuerAgentOptions"
                        :value="$reattribuerSelectedAgent"
                        placeholder="Choisir un agent"
                        :force-search="true"
                        required
                        input-class="dpc-filter-field"
                        label-class="block text-xs font-medium text-gray-600 mb-1"
                    />
                    @error('agent_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="min-w-[14rem] flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Commentaire (optionnel)</label>
                    <input
                        type="text"
                        name="commentaire"
                        maxlength="2000"
                        placeholder="Motif de la réattribution"
                        class="dpc-filter-field"
                    >
                </div>
                <button type="submit" class="px-4 py-2 bg-navy text-white text-sm font-semibold">
                    Réattribuer
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2">
                Seuls les agents éligibles ce jour-là (rotation, disponibilités déclarées, charge journalière et fériés) sont proposés. Le créneau date/heure reste inchangé.
            </p>
        @if(! $standalone)
            </details>
        @endif
    </div>
@endif
