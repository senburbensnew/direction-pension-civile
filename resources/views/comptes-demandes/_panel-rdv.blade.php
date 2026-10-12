{{--
    Panneau compact « création de compte » affiché sur le dossier rendez-vous (personal/request).
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
@endphp

<x-dossier-collapsible
    id="compte-creation"
    title="Création de compte (provisoire)"
    :subtitle="($cp->displayName() ?: '—').' · '.$cp->code"
    :open="false"
>
    <x-slot:actions>
        <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $cp->statusBadgeClass() }}">
            {{ $cp->statusLabel() }}
        </span>
        <a href="{{ $compteShowRoute }}" class="dossier-btn-secondary shrink-0">
            Dossier complet
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </x-slot:actions>

    <div class="px-5 sm:px-6 py-5 space-y-5">
        @if($estTraitable)
            <p class="text-sm text-gray-700 dossier-notice border-l-emerald-600 bg-gray-50 px-4 py-3">
                Le rendez-vous est <strong class="font-semibold text-gray-900">réalisé</strong>. Vous pouvez accepter ou refuser la création du compte après vérification des pièces.
            </p>
        @elseif($cpPending && ! $rdvRealise)
            <p class="text-sm text-gray-700 dossier-notice dossier-notice--amber px-4 py-3 bg-amber-50/40">
                Marquez d’abord ce rendez-vous comme <strong class="font-semibold text-gray-900">réalisé</strong> pour débloquer la décision sur le compte.
            </p>
        @elseif($cpPending)
            <p class="text-sm text-gray-700 dossier-notice dossier-notice--amber px-4 py-3 bg-amber-50/40">
                La décision sera disponible après l’heure prévue du rendez-vous.
            </p>
        @endif

        <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4 text-sm">
            <div>
                <dt class="text-xs font-medium text-gray-500">Téléphone</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ $cp->telephone ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">NIF</dt>
                <dd class="mt-1 font-medium text-gray-900 font-mono">{{ $cp->nif ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Courriel</dt>
                <dd class="mt-1 font-medium text-gray-800 break-all">
                    @if(filled($cp->email))
                        <a href="mailto:{{ $cp->email }}" class="text-[#173052] hover:underline">{{ $cp->email }}</a>
                    @else
                        —
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Dépôt</dt>
                <dd class="mt-1 font-medium text-gray-900">{{ ($cp->soumise_at ?? $cp->created_at)?->format('d/m/Y') }}</dd>
            </div>
        </dl>

        @if(! empty($pieces))
            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Pièces jointes</h3>
                <ul class="border border-gray-200 divide-y divide-gray-100">
                    @foreach($pieces as $piece)
                        @php
                            $pieceMedia = $piece['media'];
                            $pieceViewUrl = $cp->agentPieceUrl($pieceMedia, auth()->user(), true) ?? $pieceMedia->getUrl();
                            $pieceDownloadUrl = $cp->agentPieceUrl($pieceMedia, auth()->user(), false) ?? $pieceMedia->getUrl();
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-3 py-2.5 text-sm bg-white">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900">{{ $piece['label'] }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $pieceMedia->file_name }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 text-xs font-medium text-[#173052]">
                                <a href="{{ $pieceViewUrl }}" target="_blank" rel="noopener" class="hover:underline">Ouvrir</a>
                                <span class="text-gray-300" aria-hidden="true">·</span>
                                <a href="{{ $pieceDownloadUrl }}" class="hover:underline">Télécharger</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($estTraitable && $canDecideCompte)
            <div class="flex flex-wrap gap-2 pt-4 border-t border-gray-100">
                <form method="POST" action="{{ route('formalites.comptes-demandes.accepter', $cp) }}"
                      onsubmit="return confirm('Accepter la création de compte pour ce demandeur ?');">
                    @csrf
                    <button type="submit" class="dossier-btn-success">Accepter le compte</button>
                </form>
                <button type="button"
                        onclick="document.getElementById('refusComptePanel').classList.toggle('hidden')"
                        class="dossier-btn-danger-outline">
                    Refuser
                </button>
            </div>
            <div id="refusComptePanel" class="hidden border border-gray-200 bg-gray-50 p-4">
                <form method="POST" action="{{ route('formalites.comptes-demandes.refuser', $cp) }}"
                      onsubmit="return confirm('Refuser la création de compte ? Cette action est définitive.');">
                    @csrf
                    <label class="block text-xs font-medium text-gray-700 mb-1">Motif du refus <span class="text-red-500">*</span></label>
                    <textarea name="refusal_reason" rows="3" required minlength="5" maxlength="1000"
                              class="dossier-field mb-3 bg-white"
                              placeholder="Expliquez la raison du refus...">{{ old('refusal_reason') }}</textarea>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="dossier-btn-danger">Confirmer le refus</button>
                        <button type="button" onclick="document.getElementById('refusComptePanel').classList.add('hidden')" class="dossier-btn-secondary">Annuler</button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</x-dossier-collapsible>
