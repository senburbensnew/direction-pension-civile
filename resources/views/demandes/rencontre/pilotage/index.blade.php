<x-app-layout>
<x-portal-page class="space-y-6 dpc-square-ui">

    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Pilotage des rendez-vous</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                Consultation des rendez-vous et réattribution à un autre agent lorsque vous en avez l’habilitation.
            </p>
            @php
                $pilotageQueryPersist = request()->only(['statut', 'modalite', 'agent_id', 'q', 'date_du', 'date_au']);
            @endphp
            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                <a href="{{ route('rencontres.pilotage.index', array_merge($pilotageQueryPersist, ['filtre' => 'programmes'])) }}"
                   class="px-3 py-1 rounded-full {{ ($filtre ?? 'programmes') === 'programmes' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">Programmés</a>
                <a href="{{ route('rencontres.pilotage.index', array_merge($pilotageQueryPersist, ['filtre' => 'historique'])) }}"
                   class="px-3 py-1 rounded-full {{ ($filtre ?? '') === 'historique' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">Historique</a>
                <a href="{{ route('rencontres.pilotage.index', array_merge($pilotageQueryPersist, ['filtre' => 'tous'])) }}"
                   class="px-3 py-1 rounded-full {{ ($filtre ?? '') === 'tous' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">Tous</a>
            </div>
        </div>
        <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-full">
            {{ $demandes->total() }} demande(s)
        </span>
    </div>

    @if(isset($appelsVeille) && $appelsVeille->isNotEmpty())
        <x-rencontre-appels-veille-panel :appels-veille="$appelsVeille" :collapsed-limit="8" />
    @endif

    @php
        $pilotageInputClass = 'dpc-filter-field';
        $pilotageLabelClass = 'block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1 truncate';
        $pilotageStatutOptions = collect($statuts ?? [])
            ->map(fn ($s) => ['value' => $s->value, 'label' => $s->label()])
            ->prepend(['value' => '', 'label' => 'Tous'])
            ->values()
            ->all();
        $pilotageAgentOptions = collect($agents ?? [])
            ->map(fn ($agent) => ['value' => (string) $agent->id, 'label' => $agent->displayName()])
            ->prepend(['value' => '', 'label' => 'Tous'])
            ->values()
            ->all();
    @endphp
    <form method="get" action="{{ route('rencontres.pilotage.index') }}"
          class="border border-gray-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="filtre" value="{{ $filtre ?? 'programmes' }}">

        <div class="flex flex-wrap items-end gap-x-2 gap-y-3 xl:flex-nowrap xl:gap-x-2.5">
            <div class="w-full min-w-[10rem] basis-[11rem] grow xl:grow-[1.4] xl:max-w-[14rem]">
                <label for="pilotage-q" class="{{ $pilotageLabelClass }}">Recherche</label>
                <input type="search" id="pilotage-q" name="q" value="{{ request('q') }}"
                       placeholder="Code, nom…"
                       class="{{ $pilotageInputClass }}">
            </div>
            <div class="w-[calc(50%-0.25rem)] sm:w-[9.5rem] shrink-0">
                <x-searchable-select
                    name="statut"
                    id="pilotage-statut"
                    label="Statut"
                    :options="$pilotageStatutOptions"
                    :value="request('statut', '')"
                    placeholder="Tous"
                    :input-class="$pilotageInputClass"
                    :label-class="$pilotageLabelClass"
                />
            </div>
            <div class="w-[calc(50%-0.25rem)] sm:w-[8.5rem] shrink-0">
                <label for="pilotage-modalite" class="{{ $pilotageLabelClass }}">Modalité</label>
                <select id="pilotage-modalite" name="modalite" class="{{ $pilotageInputClass }}">
                    <option value="">Toutes</option>
                    <option value="visio" @selected(request('modalite') === 'visio')>Visio</option>
                    <option value="physique" @selected(request('modalite') === 'physique')>Présentiel</option>
                </select>
            </div>
            <div class="w-full sm:w-[9.5rem] shrink-0">
                <x-searchable-select
                    name="agent_id"
                    id="pilotage-agent"
                    label="Agent"
                    :options="$pilotageAgentOptions"
                    :value="request('agent_id', '')"
                    placeholder="Tous"
                    :force-search="true"
                    :input-class="$pilotageInputClass"
                    :label-class="$pilotageLabelClass"
                />
            </div>
            <div class="shrink-0">
                <span class="{{ $pilotageLabelClass }}">Période RDV</span>
                <div class="flex gap-1.5">
                    <input type="date" id="pilotage-date-du" name="date_du" value="{{ request('date_du') }}"
                           aria-label="RDV du"
                           class="{{ $pilotageInputClass }} w-[8.75rem]">
                    <input type="date" id="pilotage-date-au" name="date_au" value="{{ request('date_au') }}"
                           aria-label="RDV au"
                           class="{{ $pilotageInputClass }} w-[8.75rem]">
                </div>
            </div>
            <div class="flex shrink-0 items-center gap-2 pb-0.5 w-full sm:w-auto">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-navy text-white hover:bg-orange-500 transition-colors whitespace-nowrap">
                    <i class="fas fa-filter text-xs" aria-hidden="true"></i>
                    Filtrer
                </button>
                @if(request()->hasAny(['statut', 'modalite', 'agent_id', 'q', 'date_du', 'date_au']))
                    <a href="{{ route('rencontres.pilotage.index', ['filtre' => $filtre ?? 'programmes']) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors whitespace-nowrap">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full table-fixed text-sm">
            <colgroup>
                <col class="w-[9%]">
                <col class="w-[14%]">
                <col class="w-[18%]">
                <col class="w-[10%]">
                <col class="w-[12%]">
                <col class="w-[12%]">
                <col class="w-[8%]">
                <col class="w-[9%]">
                <col class="w-[8%]">
            </colgroup>
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Déposé le</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Demandeur</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Objet</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Date souhaitée</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Service</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Agent</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Modalité</th>
                    <th class="px-2 sm:px-3 py-3 text-left text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Statut</th>
                    <th class="px-2 sm:px-3 py-3 text-right text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wide leading-tight">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($demandes as $d)
                    @php
                        $data   = $d->data ?? [];
                        $rdvStatut = $d->rencontreStatutMetier();
                        $aCloturer = $d->doitApparaitreCommeACloturer();
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-2 sm:px-3 py-3 text-[11px] sm:text-xs text-gray-600 align-top leading-snug">
                            {{ $d->created_at?->format('d/m/Y') ?? '—' }}
                            <span class="block text-gray-400">{{ $d->created_at?->format('H:i') }}</span>
                        </td>
                        <td class="px-2 sm:px-3 py-3 align-top min-w-0">
                            <p class="font-medium text-gray-800 text-xs leading-snug line-clamp-2" title="{{ $d->rencontreDemandeurNom() }}">{{ $d->rencontreDemandeurNom() }}</p>
                            <p class="text-[11px] text-gray-400 truncate mt-0.5" title="{{ $d->rencontreDemandeurEmail() }}">{{ $d->rencontreDemandeurEmail() }}</p>
                            @if(!empty($data['organisation']))
                                <p class="text-[11px] text-gray-400 italic truncate" title="{{ $data['organisation'] }}">{{ $data['organisation'] }}</p>
                            @endif
                        </td>
                        <td class="px-2 sm:px-3 py-3 text-gray-700 text-xs align-top min-w-0">
                            <span class="line-clamp-2" title="{{ $d->rencontreObjet() }}">{{ $d->rencontreObjet() }}</span>
                        </td>
                        <td class="px-2 sm:px-3 py-3 text-gray-600 text-[11px] sm:text-xs align-top leading-snug">
                            {{ isset($data['date_souhaitee']) ? \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y') : '—' }}
                            @if(!empty($data['heure_souhaitee']))
                                <span class="block text-gray-400">{{ $data['heure_souhaitee'] }}</span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-3 py-3 text-[11px] sm:text-xs text-gray-600 align-top min-w-0">
                            <span class="line-clamp-2" title="{{ $data['service_responsable'] ?? $d->service?->nom ?? '—' }}">
                                {{ $data['service_responsable'] ?? $d->service?->nom ?? '—' }}
                            </span>
                        </td>
                        <td class="px-2 sm:px-3 py-3 text-[11px] sm:text-xs text-gray-600 align-top min-w-0">
                            @php $agentLabel = $data['agent_nom'] ?? $d->assignments->first()?->agent?->displayName() ?? '—'; @endphp
                            <span class="line-clamp-2" title="{{ $agentLabel }}">{{ $agentLabel }}</span>
                        </td>
                        <td class="px-2 sm:px-3 py-3 align-top">
                            @if(($data['modalite'] ?? 'visio') === 'physique')
                                <span class="inline-block text-[10px] sm:text-xs px-1.5 sm:px-2 py-0.5 rounded-full font-medium bg-orange-100 text-orange-700 whitespace-nowrap">Présentiel</span>
                            @else
                                <span class="inline-block text-[10px] sm:text-xs px-1.5 sm:px-2 py-0.5 rounded-full font-medium whitespace-nowrap {{ match($data['plateforme'] ?? '') { 'zoom' => 'bg-blue-100 text-blue-700', 'teams' => 'bg-purple-100 text-purple-700', 'meet' => 'bg-green-100 text-green-700', default => 'bg-gray-100 text-gray-600', } }}">
                                    {{ ucfirst($data['plateforme'] ?? 'Visio') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-3 py-3 align-top min-w-0">
                            <span class="inline-block max-w-full truncate text-[10px] sm:text-xs px-1.5 sm:px-2 py-0.5 rounded-full font-medium {{ $rdvStatut->badgeClass() }}" title="{{ $rdvStatut->label() }}">
                                {{ $rdvStatut->label() }}
                            </span>
                            @if($aCloturer)
                                <span class="mt-1 inline-block max-w-full truncate text-[10px] px-1.5 py-0.5 rounded-full font-medium bg-amber-100 text-amber-900" title="Créneau passé — clôture à effectuer dans le dossier">
                                    À clôturer
                                </span>
                            @endif
                        </td>
                        <td class="px-2 sm:px-3 py-3 text-right align-top">
                            <a href="{{ route('rencontres.pilotage.show', $d) }}"
                               class="inline-flex items-center justify-center px-2 sm:px-3 py-1.5 text-[10px] sm:text-xs font-semibold rounded-lg bg-navy text-white hover:opacity-90 transition whitespace-nowrap">
                                Ouvrir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-10 text-center text-gray-400">
                            Aucune demande de rencontre.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($demandes->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $demandes->links() }}
            </div>
        @endif
    </div>

</x-portal-page>
</x-app-layout>
