@if(($canReassignAgent ?? false) && ($rdvPending ?? true))
    <div class="border-t border-gray-100 pt-5 mb-5">
        <details class="group">
            <summary class="cursor-pointer inline-flex items-center gap-2 text-sm font-semibold text-navy hover:text-navy/80">
                <i class="fa-solid fa-user-pen"></i>
                Réattribuer à un autre agent
            </summary>
            <form
                method="POST"
                action="{{ route('demandes.rencontre.reattribuer', $demande) }}"
                class="mt-3 flex flex-wrap items-end gap-3 bg-slate-50 border border-gray-200 rounded-lg p-4"
                onsubmit="return confirm('Confirmer la réattribution de ce rendez-vous ?');"
            >
                @csrf
                <div class="min-w-[14rem] flex-1">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nouvel agent</label>
                    <select name="agent_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Choisir un agent</option>
                        @foreach($bookingAgents ?? [] as $agent)
                            <option
                                value="{{ $agent->id }}"
                                @selected((int) ($demande->data['agent_id'] ?? 0) === (int) $agent->id)
                            >
                                {{ $agent->displayName() }}
                            </option>
                        @endforeach
                    </select>
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
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                    >
                </div>
                <button type="submit" class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg">
                    Réattribuer
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-2">
                Seuls les agents éligibles ce jour-là (rotation, disponibilités déclarées, charge journalière et fériés) sont proposés. Le créneau date/heure reste inchangé.
            </p>
        </details>
    </div>
@endif
