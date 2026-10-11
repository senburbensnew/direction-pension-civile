<x-app-layout>
<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">

    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Pilotage des rendez-vous</h1>
            <p class="text-sm text-gray-500 mt-0.5">
                @if($canResponsablePilotageActions ?? false)
                    Consultation, réattribution à un autre agent et annulation des rendez-vous programmés.
                @else
                    Consultation, acceptation, réorientation et historique.
                @endif
            </p>
            <div class="mt-3 flex flex-wrap gap-2 text-xs">
                <a href="{{ route('rencontres.pilotage.index', ['filtre' => 'programmes']) }}"
                   class="px-3 py-1 rounded-full {{ ($filtre ?? 'programmes') === 'programmes' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">Programmés</a>
                <a href="{{ route('rencontres.pilotage.index', ['filtre' => 'historique']) }}"
                   class="px-3 py-1 rounded-full {{ ($filtre ?? '') === 'historique' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">Historique</a>
                <a href="{{ route('rencontres.pilotage.index', ['filtre' => 'tous']) }}"
                   class="px-3 py-1 rounded-full {{ ($filtre ?? '') === 'tous' ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">Tous</a>
            </div>
        </div>
        <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-full">
            {{ $demandes->total() }} demande(s)
        </span>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
            {{ session('error') }}
        </div>
    @endif

    @if(isset($appelsVeille) && $appelsVeille->isNotEmpty())
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-sky-800 mb-2">
                Appels de rappel — service des Formalités ({{ $appelsVeille->count() }})
            </p>
            <ul class="space-y-2 text-sm text-sky-900">
                @foreach($appelsVeille as $rdv)
                    @php $data = $rdv->data ?? []; @endphp
                    <li>
                        {{ $rdv->rencontreDemandeurNom() }}
                        · {{ $data['telephone'] ?? 'n° manquant' }}
                        · demain {{ $data['heure_souhaitee'] ?? '' }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
        <table class="w-full min-w-[56rem] text-sm table-fixed sm:table-auto">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap w-[8.5rem]">Déposé le</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide min-w-[11rem] sm:min-w-[13rem]">Demandeur</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Objet</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Date souhaitée</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Service</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Agent Formalités</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Modalité</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Statut</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-nowrap w-[7rem]">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($demandes as $d)
                    @php
                        $data   = $d->data ?? [];
                        $code   = $d->currentStep?->code;
                        $rdvStatut = $d->rencontreStatut();
                        $isPending = ! $rdvStatut->isTerminal();
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $d->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 min-w-[11rem] sm:min-w-[13rem] align-top">
                            <p class="font-medium text-gray-800 leading-snug break-words">{{ $d->rencontreDemandeurNom() }}</p>
                            <p class="text-xs text-gray-400 break-all sm:break-normal mt-0.5">{{ $d->rencontreDemandeurEmail() }}</p>
                            @if(!empty($data['organisation']))
                                <p class="text-xs text-gray-400 italic">{{ $data['organisation'] }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 max-w-xs truncate">{{ $d->rencontreObjet() }}</td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ isset($data['date_souhaitee']) ? \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y') : '—' }}
                            @if(!empty($data['heure_souhaitee']))
                                <span class="text-gray-400">à {{ $data['heure_souhaitee'] }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $data['service_responsable'] ?? $d->service?->nom ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $data['agent_nom'] ?? $d->assignments->first()?->agent?->displayName() ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if(($data['modalite'] ?? 'visio') === 'physique')
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-orange-100 text-orange-700">Présentiel</span>
                            @else
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                    {{ match($data['plateforme'] ?? '') {
                                        'zoom'  => 'bg-blue-100 text-blue-700',
                                        'teams' => 'bg-purple-100 text-purple-700',
                                        'meet'  => 'bg-green-100 text-green-700',
                                        default => 'bg-gray-100 text-gray-600',
                                    } }}">
                                    {{ ucfirst($data['plateforme'] ?? 'Visio') }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $rdvStatut->badgeClass() }}">
                                {{ $rdvStatut->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right align-top whitespace-nowrap">
                            <a href="{{ route('rencontres.pilotage.show', $d) }}"
                               class="inline-flex items-center justify-center min-w-[5.5rem] px-3 py-1.5 text-xs font-semibold rounded-lg bg-navy text-white hover:opacity-90 transition">
                                @if(($canResponsablePilotageActions ?? false) && $isPending)
                                    Gérer
                                @else
                                    Ouvrir
                                @endif
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

</div>
</x-app-layout>
