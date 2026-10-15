<x-app-layout>
    @php
        $data = $demande->data ?? [];
        $carte = $demande->getFirstMedia('carte_pension');
        $rdvStatut = $rdvStatut ?? $demande->rencontreStatut();
        $rdvReservable = $rdvStatut->reservesSlot();
        $aCloturer = $demande->doitApparaitreCommeACloturer();
        $isPhysique = ($data['modalite'] ?? '') === 'physique';
        $visioPilotage = app(\App\Services\RencontreVisioService::class);
        $visioPilotageOk = ! $isPhysique
            && filled($demande->visio_token)
            && $visioPilotage->canAccess(auth()->user(), $demande);
        $visioPilotageStatus = $visioPilotageOk ? $visioPilotage->status($demande) : null;
        $visioLienExpire = ! $isPhysique && $visioPilotage->isVisioLinkExpired($demande);
        $visioSlotEndsAt = ! $isPhysique ? $visioPilotage->slotEndsAt($demande) : null;
        $serviceLabel = $data['service_responsable'] ?? $demande->service?->nom ?? '—';
        $agentLabel = $data['agent_nom'] ?? $demande->assignments->first()?->agent?->displayName() ?? '—';
        $motifLabel = $demande->rencontreMotifLabel();
        $objetDetail = $demande->rencontreObjet();
        $showObjetDetail = $objetDetail !== '—'
            && $objetDetail !== $motifLabel
            && $objetDetail !== (string) ($data['motif'] ?? '');
        $showConfirmationRecap = $rdvStatut === \App\Enums\RencontreStatutEnum::ACTIF && ! empty($confirmation);
    @endphp

    <x-portal-page class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-emerald-600">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="dossier-kicker">Pilotage · {{ $demande->code }}</p>
                    <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">{{ $demande->rencontreDemandeurNom() }}</h1>
                    <p class="mt-2">
                        <span class="inline-flex text-xs font-medium px-2.5 py-0.5 ring-1 ring-inset {{ $rdvStatut->badgeClass() }}">{{ $rdvStatut->label() }}</span>
                    </p>
                    <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                        {{ ($data['modalite'] ?? '') === 'physique' ? 'Présentiel' : 'Visioconférence' }}
                        @if(! empty($data['date_souhaitee']))
                            · {{ \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y') }}
                        @endif
                        @if(! empty($data['heure_souhaitee']))
                            à {{ $data['heure_souhaitee'] }}
                        @endif
                        · Agent <span class="font-medium text-gray-800">{{ $agentLabel }}</span>
                        · {{ $serviceLabel }}
                        @if($motifLabel !== '—')
                            · {{ $motifLabel }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('rencontres.pilotage.index', request()->only(['filtre', 'statut', 'modalite', 'agent_id', 'q', 'date_du', 'date_au'])) }}"
                   class="dossier-btn-secondary shrink-0">Retour à la liste</a>
            </div>
        </header>

        @include('demandes.rencontre._workflow-banner', [
            'demande' => $demande,
            'rdvStatut' => $rdvStatut,
            'compteCreation' => $compteCreation ?? null,
        ])

        @if($aCloturer)
            <div class="dossier-notice dossier-notice--amber">
                <p class="dossier-notice__head bg-amber-50/80 text-amber-900">Clôture en attente</p>
                <div class="p-4 text-sm text-gray-700">
                    Le créneau est passé. Marquez le rendez-vous comme <strong>réalisé</strong> ou <strong>non honoré</strong> depuis le dossier agent.
                    <a href="{{ $dossierShowRoute ?? route('personal.request.show', $demande) }}"
                       class="ml-1 font-semibold text-navy hover:underline">Ouvrir le dossier</a>
                </div>
            </div>
        @endif

        <section class="dossier-card p-5 sm:p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4">Coordonnées et compléments</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">N° pension / matricule</dt><dd class="font-medium">{{ $data['numero_pension'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $data['telephone'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Courriel</dt><dd class="font-medium">{{ $demande->rencontreDemandeurEmail() }}</dd></div>
                @if($showObjetDetail)
                    <div class="md:col-span-2"><dt class="text-gray-500">Précisions de la demande</dt><dd class="font-medium">{{ $objetDetail }}</dd></div>
                @endif
                @unless($showConfirmationRecap)
                    <div class="md:col-span-2"><dt class="text-gray-500">Lieu ou visio</dt><dd class="font-medium">{{ $data['lieu_rdv'] ?? ($demande->visio_token ? 'Lien sécurisé DPC' : '—') }}</dd></div>
                @endunless
            </dl>
            @if($carte)
                <p class="mt-4 text-sm">
                    <a href="{{ $demande->urlForMedia($carte) }}" target="_blank" class="text-navy font-semibold hover:underline">Voir la carte de pension jointe</a>
                </p>
            @endif
            @if($visioPilotageOk)
                <p class="mt-4">
                    @if($visioLienExpire)
                        <span class="text-sm text-gray-500 italic">
                            @if($rdvStatut->isTerminal())
                                Rendez-vous {{ mb_strtolower($rdvStatut->label()) }} — lien de visio indisponible.
                            @else
                                Fenêtre visio terminée (fin de créneau {{ $visioSlotEndsAt?->format('H:i') ?? '—' }}).
                            @endif
                        </span>
                    @elseif($visioPilotageStatus === 'actif')
                        <a href="{{ $demande->visioPageUrl('pilotage') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-orange-600">
                            Rejoindre la visioconférence
                        </a>
                        <span class="block text-xs text-gray-500 mt-1">
                            Lien actif jusqu’à {{ $visioSlotEndsAt?->format('d/m/Y à H:i') ?? '—' }}
                        </span>
                    @elseif($visioPilotageStatus === 'en_attente')
                        <span class="text-sm text-amber-800">
                            Salle disponible à partir du
                            {{ $visioPilotage->opensAt($demande)?->format('d/m/Y à H:i') ?? '—' }}
                            — la page se met à jour automatiquement.
                        </span>
                        <a href="{{ $demande->visioPageUrl('pilotage') }}"
                           class="ml-2 text-sm font-semibold text-navy hover:underline">
                            Page visio
                        </a>
                    @else
                        <span class="text-sm text-gray-500">Lien de visioconférence indisponible.</span>
                    @endif
                </p>
            @endif
        </section>

        @if($showConfirmationRecap)
            @include('demandes.rencontre._confirmation', [
                'confirmation' => $confirmation,
                'compact' => true,
            ])
        @endif

        @if(($canReassignAgent ?? false) && $rdvReservable)
            <section class="bg-white border border-gray-200 p-6 space-y-1">
                <h3 class="text-base font-bold text-gray-800 mb-1">Réattribuer à un autre agent</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Transférez ce rendez-vous à un autre agent formalités. La date et l’heure du créneau restent inchangées.
                </p>
                @include('demandes.rencontre._reattribuer-agent', [
                    'demande' => $demande,
                    'bookingAgents' => $bookingAgents ?? collect(),
                    'canReassignAgent' => true,
                    'rdvPending' => $rdvReservable,
                    'reattribuerRoute' => $reattribuerRoute ?? null,
                    'reassignStandalone' => true,
                ])
            </section>
        @endif

        @if(! empty($data['suites']))
            <section class="bg-white border border-gray-200 p-6">
                <h3 class="text-base font-bold text-gray-800 mb-3">Suites données</h3>
                @foreach($data['suites'] as $suite)
                    <div class="border-b border-gray-100 py-3 text-sm last:border-0">
                        <p class="text-gray-800">{{ $suite['texte'] ?? '' }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $suite['par'] ?? '' }} · {{ ! empty($suite['at']) ? \Carbon\Carbon::parse($suite['at'])->format('d/m/Y H:i') : '' }}</p>
                    </div>
                @endforeach
            </section>
        @endif

        <section class="bg-white border border-gray-200 p-6">
            <h3 class="text-base font-bold text-gray-800 mb-3">Historique du rendez-vous</h3>
            <ol class="space-y-3">
                @forelse($histories as $history)
                    <li class="text-sm border-l-2 border-navy/20 pl-3">
                        <p class="font-medium text-gray-800">
                            {{ is_callable($eventLabel ?? null) ? $eventLabel($history->event) : $history->event }}
                            · {{ \App\Enums\RencontreStatutEnum::tryFrom((string) $history->statut)?->label() ?? $history->statut ?? '—' }}
                        </p>
                        <p class="text-gray-600">{{ $history->commentaire }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $history->changer?->displayName() ?? 'Système' }} · {{ $history->created_at?->format('d/m/Y H:i') }}</p>
                    </li>
                @empty
                    <li class="text-sm text-gray-400">Aucun historique pour le moment.</li>
                @endforelse
            </ol>
        </section>
    </x-portal-page>

    @if(($visioPilotageStatus ?? null) === 'en_attente' && filled($demande->visio_token))
        <x-rencontre-visio-auto-refresh :token="$demande->visio_token" />
    @endif
</x-app-layout>
