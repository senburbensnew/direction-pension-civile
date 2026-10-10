{{--
    Panneau compact « création de compte » affiché sur le dossier rendez-vous (personal/request).
    Le dossier complet reste sur formalites/comptes-demandes/{id}.
--}}
@php
    $cp = $compte;
    $pieces = $cp->identityPieces();
    $estTraitable = $cp->canBeDecided();
    $canDecideCompte = auth()->user()->hasRole(\App\Models\User::ROLE_AGENT_FORMALITES);
    $compteShowRoute = auth()->user()->hasRole('admin')
        ? route('admin.comptes-demandes.show', $cp)
        : route('formalites.comptes-demandes.show', $cp);
    $rdvRealise = $request->rencontreStatut() === \App\Enums\RencontreStatutEnum::REALISE;
    $cpPending = $cp->isPending();
    $stepRdv = $rdvRealise ? 'done' : ($cpPending ? 'current' : 'done');
    $stepPieces = count($pieces) > 0 ? 'done' : ($cpPending ? 'current' : 'muted');
    $stepDecision = $estTraitable ? 'current' : (! $cpPending ? 'done' : 'muted');
@endphp

<div id="compte-creation" class="max-w-7xl mx-auto mt-5 sm:px-6 lg:px-8 scroll-mt-24">
    <div class="bg-white rounded-2xl border border-indigo-200 shadow-sm overflow-hidden">

        <div class="h-1 w-full bg-gradient-to-r from-indigo-600 via-violet-500 to-purple-500"></div>

        <div class="px-5 py-4 sm:px-6 border-b border-gray-100 flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3 min-w-0">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-base font-bold text-gray-900">Création de compte (provisoire)</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $cp->statusBadgeClass() }}">
                            {{ $cp->statusLabel() }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-0.5 truncate">{{ $cp->displayName() ?: '—' }} · {{ $cp->code }}</p>
                </div>
            </div>
            <a href="{{ $compteShowRoute }}"
               class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition-colors">
                Dossier complet
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        {{-- Étapes --}}
        <div class="px-5 sm:px-6 py-3 bg-gray-50/80 border-b border-gray-100">
            <ol class="flex flex-wrap items-center gap-2 text-xs font-medium">
                @foreach([
                    ['label' => 'RDV réalisé', 'state' => $stepRdv],
                    ['label' => 'Pièces déposées', 'state' => $stepPieces],
                    ['label' => 'Décision', 'state' => $stepDecision],
                ] as $step)
                    <li @class([
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full ring-1',
                        'bg-green-50 text-green-800 ring-green-200' => $step['state'] === 'done',
                        'bg-indigo-50 text-indigo-800 ring-indigo-200' => $step['state'] === 'current',
                        'bg-white text-gray-400 ring-gray-200' => $step['state'] === 'muted',
                    ])>
                        @if($step['state'] === 'done')
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @endif
                        {{ $step['label'] }}
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="p-5 sm:p-6 space-y-5">

            @if($estTraitable)
                <p class="text-sm text-green-800 bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                    Le rendez-vous est <strong>réalisé</strong>. Vous pouvez accepter ou refuser la création du compte après vérification des pièces.
                </p>
            @elseif($cpPending && ! $rdvRealise)
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    Marquez d’abord ce rendez-vous comme <strong>réalisé</strong> pour débloquer la décision sur le compte.
                </p>
            @elseif($cpPending)
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    La décision sera disponible après l’heure prévue du rendez-vous.
                </p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Téléphone</p>
                    <p class="font-medium text-gray-900 mt-0.5 truncate">{{ $cp->telephone ?: '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">NIF</p>
                    <p class="font-medium text-gray-900 mt-0.5 truncate">{{ $cp->nif ?: '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">E-mail</p>
                    <p class="font-medium text-gray-900 mt-0.5 truncate" title="{{ $cp->email }}">{{ $cp->email ?: '—' }}</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50/50 px-3 py-2.5">
                    <p class="text-[10px] uppercase tracking-wide text-gray-400 font-semibold">Dépôt</p>
                    <p class="font-medium text-gray-900 mt-0.5">{{ ($cp->soumise_at ?? $cp->created_at)?->format('d/m/Y') }}</p>
                </div>
            </div>

            @if(! empty($pieces))
                <div>
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Pièces jointes</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($pieces as $piece)
                            @php
                                $pieceMedia = $piece['media'];
                                $pieceViewUrl = $cp->agentPieceUrl($pieceMedia, auth()->user(), true) ?? $pieceMedia->getUrl();
                                $pieceDownloadUrl = $cp->agentPieceUrl($pieceMedia, auth()->user(), false) ?? $pieceMedia->getUrl();
                                $isImage = str_starts_with((string) $pieceMedia->mime_type, 'image/');
                            @endphp
                            <div class="flex items-center gap-3 rounded-xl border border-gray-200 p-2.5 hover:border-indigo-200 hover:bg-indigo-50/30 transition-colors">
                                @if($isImage)
                                    <a href="{{ $pieceViewUrl }}" target="_blank" rel="noopener" class="shrink-0">
                                        <img src="{{ $pieceViewUrl }}" alt="" class="w-14 h-14 object-cover rounded-lg border border-gray-200 bg-gray-100">
                                    </a>
                                @else
                                    <div class="w-14 h-14 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center shrink-0 text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9l-5-5H7a2 2 0 00-2 2v13a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $piece['label'] }}</p>
                                    <div class="flex gap-3 mt-1 text-xs font-semibold">
                                        <a href="{{ $pieceViewUrl }}" target="_blank" rel="noopener" class="text-indigo-700 hover:underline">Ouvrir</a>
                                        <a href="{{ $pieceDownloadUrl }}" class="text-indigo-700 hover:underline">Télécharger</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($estTraitable && $canDecideCompte)
                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-100">
                    <form method="POST" action="{{ route('formalites.comptes-demandes.accepter', $cp) }}"
                          onsubmit="return confirm('Accepter la création de compte pour ce demandeur ?');">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm">
                            Accepter le compte
                        </button>
                    </form>
                    <button type="button"
                            onclick="document.getElementById('refusComptePanel').classList.toggle('hidden')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-red-200 text-red-700 hover:bg-red-50 text-sm font-semibold rounded-xl">
                        Refuser
                    </button>
                </div>
                <div id="refusComptePanel" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                    <form method="POST" action="{{ route('formalites.comptes-demandes.refuser', $cp) }}"
                          onsubmit="return confirm('Refuser la création de compte ? Cette action est définitive.');">
                        @csrf
                        <label class="block text-xs font-semibold text-red-800 mb-1">Motif du refus <span class="text-red-500">*</span></label>
                        <textarea name="refusal_reason" rows="3" required minlength="5" maxlength="1000"
                                  class="w-full border border-red-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 mb-3"
                                  placeholder="Expliquez la raison du refus...">{{ old('refusal_reason') }}</textarea>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">Confirmer le refus</button>
                            <button type="button" onclick="document.getElementById('refusComptePanel').classList.add('hidden')"
                                    class="px-4 py-2 text-gray-600 hover:text-gray-800 text-sm">Annuler</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
