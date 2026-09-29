<x-app-layout>
    @php
        $data = $demande->data ?? [];
        $carte = $demande->getFirstMedia('carte_pension');
        $canAct = $canValidate ?? false;
        $rdvStatut = $rdvStatut ?? $demande->rencontreStatut();
        $pending = ! $rdvStatut->isTerminal();
    @endphp

    <div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pilotage du rendez-vous {{ $demande->code }}</h2>
            <a href="{{ route('rencontres.pilotage.index') }}" class="text-sm text-navy font-semibold hover:underline">Retour à la liste</a>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">{{ session('success') }}</div>
        @endif

        <section class="bg-white border border-gray-200 rounded-2xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-400">Statut du rendez-vous</p>
                    <p class="inline-flex mt-1 text-sm font-semibold px-2.5 py-1 rounded-full {{ $rdvStatut->badgeClass() }}">{{ $rdvStatut->label() }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ ($data['modalite'] ?? '') === 'physique' ? 'Présentiel' : 'Visioconférence' }}
                        @if(!empty($data['date_souhaitee']))
                            · {{ \Carbon\Carbon::parse($data['date_souhaitee'])->format('d/m/Y') }}
                        @endif
                        @if(!empty($data['heure_souhaitee']))
                            à {{ $data['heure_souhaitee'] }}
                        @endif
                    </p>
                </div>
                <div class="text-sm text-gray-600">
                    <p>Service Formalités : <strong>{{ $data['service_responsable'] ?? $demande->service?->nom ?? 'Formalités' }}</strong></p>
                    <p>Service proposé (motif) : <strong>{{ $data['service_propose'] ?? '—' }}</strong></p>
                    <p>Agent : <strong>{{ $data['agent_nom'] ?? $demande->assignments->first()?->agent?->displayName() ?? '—' }}</strong></p>
                </div>
            </div>
            <ol class="mt-5 flex flex-wrap gap-2 text-xs">
                @foreach(\App\Enums\RencontreStatutEnum::cases() as $etat)
                    <li class="px-2 py-1 rounded-full {{ $rdvStatut === $etat ? $etat->badgeClass().' font-semibold ring-1 ring-navy/20' : 'bg-gray-50 text-gray-400' }}">
                        {{ $etat->label() }}
                    </li>
                @endforeach
            </ol>
        </section>

        <section class="bg-white border border-gray-200 rounded-2xl p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4">Informations transmises par l’usager</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Nom et prénom</dt><dd class="font-medium">{{ trim(($data['prenom'] ?? '').' '.($data['nom'] ?? '')) ?: '—' }}</dd></div>
                <div><dt class="text-gray-500">N° pension / matricule</dt><dd class="font-medium">{{ $data['numero_pension'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $data['telephone'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Courriel</dt><dd class="font-medium">{{ $data['email'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Motif</dt><dd class="font-medium">{{ $data['objet'] ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Lieu ou visio</dt><dd class="font-medium">{{ $data['lieu_rdv'] ?? ($demande->visio_token ? 'Lien sécurisé DPC' : '—') }}</dd></div>
                <div class="md:col-span-2"><dt class="text-gray-500">Relève de Formalités ?</dt><dd class="font-medium">{{ ($data['service_propose'] ?? 'Formalités') === 'Formalités' || ($data['service_propose_code'] ?? '') === \App\Models\Service::FORMALITE ? 'Oui — motif Formalités' : 'À confirmer : motif orienté vers '.($data['service_propose'] ?? 'un autre service') }}</dd></div>
            </dl>
            @if($carte)
                <p class="mt-4 text-sm">
                    <a href="{{ $carte->getUrl() }}" target="_blank" class="text-navy font-semibold hover:underline">Voir la carte de pension jointe</a>
                </p>
            @endif
        </section>

        @if($rdvStatut === \App\Enums\RencontreStatutEnum::ACTIF && !empty($confirmation))
            @include('demandes.rencontre._confirmation', ['confirmation' => $confirmation])
        @endif

        @if($canAct && $pending)
            <section class="bg-white border border-gray-200 rounded-2xl p-6 space-y-5">
                <h3 class="text-base font-bold text-gray-800">Formalités — examen, attribution et validation</h3>
                <div class="flex flex-wrap gap-3">
                    @if($rdvStatut === \App\Enums\RencontreStatutEnum::DEMANDE)
                        <form method="POST" action="{{ route('rencontres.pilotage.examiner', $demande) }}">
                            @csrf
                            <button class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg">Prendre en charge</button>
                        </form>
                    @endif
                    @if(in_array($rdvStatut, [\App\Enums\RencontreStatutEnum::DEMANDE, \App\Enums\RencontreStatutEnum::EN_COURS], true))
                        <form method="POST" action="{{ route('rencontres.pilotage.attribuer', $demande) }}">
                            @csrf
                            <button class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-lg">Attribuer le créneau</button>
                        </form>
                    @endif
                    @if(in_array($rdvStatut, [\App\Enums\RencontreStatutEnum::ATTRIBUE, \App\Enums\RencontreStatutEnum::REPORTE], true))
                        <form method="POST" action="{{ route('rencontres.pilotage.accepter', $demande) }}">
                            @csrf
                            <input type="hidden" name="return" value="{{ route('rencontres.pilotage.show', $demande) }}">
                            <button class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">Valider définitivement</button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('rencontres.pilotage.refuser', $demande) }}" class="flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="hidden" name="return" value="{{ route('rencontres.pilotage.show', $demande) }}">
                        <input type="text" name="motif" required maxlength="500" placeholder="Motif d’annulation"
                               class="border border-red-200 rounded-lg px-3 py-2 text-sm">
                        <button class="px-4 py-2 bg-red-50 text-red-700 border border-red-200 text-sm font-semibold rounded-lg">Annuler</button>
                    </form>
                </div>

                <form method="POST" action="{{ route('rencontres.pilotage.proposer-creneau', $demande) }}" class="border-t border-gray-100 pt-4 space-y-3">
                    @csrf
                    <p class="text-sm font-semibold text-gray-700">Proposer un autre créneau</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <input type="date" name="date_souhaitee" required value="{{ $data['date_souhaitee'] ?? '' }}"
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <select name="heure_souhaitee" required class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            @foreach($rdvTimes ?? [] as $time)
                                <option value="{{ $time }}" @selected(($data['heure_souhaitee'] ?? '') === $time)>{{ $time }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                            <input type="checkbox" name="report" value="1" @checked($rdvStatut === \App\Enums\RencontreStatutEnum::ACTIF)>
                            Marquer comme reporté
                        </label>
                    </div>
                    <button class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg">Enregistrer le créneau</button>
                </form>

                <form method="POST" action="{{ route('rencontres.pilotage.reorienter', $demande) }}" class="border-t border-gray-100 pt-4 space-y-3">
                    @csrf
                    <p class="text-sm font-semibold text-gray-700">Réorienter vers un autre service</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <select name="service_id" required class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                            <option value="">Choisir un service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" @selected((int) $demande->current_service_id === (int) $service->id)>{{ $service->nom }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="commentaire" required maxlength="500" placeholder="Motif de la réorientation"
                               class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <button class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg">Réorienter</button>
                </form>
            </section>
        @endif

        @if($canAct && $rdvStatut === \App\Enums\RencontreStatutEnum::ACTIF)
            <section class="bg-white border border-gray-200 rounded-2xl p-6">
                <h3 class="text-base font-bold text-gray-800 mb-3">Clôturer le rendez-vous</h3>
                <form method="POST" action="{{ route('rencontres.pilotage.clore', $demande) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <select name="statut" required class="border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        <option value="{{ \App\Enums\RencontreStatutEnum::REALISE->value }}">Réalisé</option>
                        <option value="{{ \App\Enums\RencontreStatutEnum::NON_HONORE->value }}">Non honoré</option>
                    </select>
                    <input type="text" name="commentaire" maxlength="500" placeholder="Commentaire de clôture"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm min-w-[16rem] flex-1">
                    <button class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg">Clôturer</button>
                </form>
            </section>
        @endif

        <section class="bg-white border border-gray-200 rounded-2xl p-6">
            <h3 class="text-base font-bold text-gray-800 mb-3">Suites données</h3>
            @forelse(($data['suites'] ?? []) as $suite)
                <div class="border-b border-gray-100 py-3 text-sm">
                    <p class="text-gray-800">{{ $suite['texte'] ?? '' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $suite['par'] ?? '' }} · {{ !empty($suite['at']) ? \Carbon\Carbon::parse($suite['at'])->format('d/m/Y H:i') : '' }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-400">Aucune suite enregistrée.</p>
            @endforelse

            @if($canAct)
                <form method="POST" action="{{ route('rencontres.pilotage.suite', $demande) }}" class="mt-4 space-y-2">
                    @csrf
                    <textarea name="suite" required maxlength="2000" rows="3" placeholder="Enregistrer la suite donnée à ce rendez-vous…"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm"></textarea>
                    <button class="px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg">Enregistrer la suite</button>
                </form>
            @endif
        </section>

        <section class="bg-white border border-gray-200 rounded-2xl p-6">
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
    </div>
</x-app-layout>
