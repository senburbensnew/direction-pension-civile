@php
    $routePrefix = $routePrefix ?? 'formalites';
    $canDecide = $canDecide ?? false;
    $pieces = $demande->identityPieces();
    $ocrFields = $demande->ocr_fields ?? [];
    $ocrDocuments = $demande->ocr_documents ?? [];
    $rdvDecisionCompte = $rendezVousPourDecisionCompte
        ?? $rendezVousFinalisation
        ?? $demande->rendezVousPourDecisionCompte();
    $rdvRealise = $demande->hasRendezVousRealise();
    $rdvHeurePassee = $rdvDecisionCompte?->rencontreHeureEstPassee() ?? true;
    $peutDeciderDossier = ($canDecide ?? false) && $demande->canBeDecided();

    // Libellé « Compte » : Prénom Nom > Code pension > NIF/NINU > Email
    $fullName = trim(($demande->firstname ?? '') . ' ' . ($demande->lastname ?? ''));
    $compteLabel = $fullName !== ''
        ? $fullName
        : ($demande->pension_code
            ?: ($demande->nif
                ?: ($demande->ninu
                    ?: ($demande->email
                        ?: ($demande->user?->email ?: '—')))));

    /**
     * Associe à chaque pièce (par sa collection) les données OCR correspondantes.
     * On regarde d'abord $ocr_documents[$collection] (structure attendue :
     *   ['identite_cin' => ['nom' => '...', 'prenom' => '...'], ...])
     * puis, à défaut, on filtre les clés de $ocr_fields préfixées par la collection.
     */
    $ocrForPiece = function (string $collection) use ($ocrDocuments, $ocrFields) {
        // 1. Depuis ocr_documents
        if (isset($ocrDocuments[$collection]) && is_array($ocrDocuments[$collection])) {
            return array_filter($ocrDocuments[$collection], fn ($v) => ! is_array($v));
        }

        // 2. Fallback : clés préfixées (ex. "identite_cin.nom" ou "identite_cin_nom")
        $found = [];
        foreach ($ocrFields as $key => $value) {
            if (is_array($value)) {
                continue;
            }
            if (str_starts_with($key, $collection . '.')
                || str_starts_with($key, $collection . '_')
                || str_starts_with($key, $collection . '-')) {
                $clean = preg_replace('/^' . preg_quote($collection, '/') . '[._-]+/', '', $key);
                $found[$clean] = $value;
            }
        }

        return $found;
    };

    // Clés OCR déjà attribuées à une pièce (pour ne pas les répéter dans un bloc global)
    $attributedKeys = [];
    foreach ($pieces as $p) {
        if (isset($ocrDocuments[$p['collection']]) && is_array($ocrDocuments[$p['collection']])) {
            foreach (array_keys($ocrDocuments[$p['collection']]) as $k) {
                $attributedKeys[] = $k;
            }
        }
    }
    $remainingOcr = [];
    foreach ($ocrFields as $k => $v) {
        if (is_array($v) || in_array($k, $attributedKeys, true)) {
            continue;
        }
        // Retire aussi les clés déjà matchées par préfixe
        $matched = false;
        foreach ($pieces as $p) {
            if (str_starts_with($k, $p['collection'] . '.')
                || str_starts_with($k, $p['collection'] . '_')
                || str_starts_with($k, $p['collection'] . '-')) {
                $matched = true;
                break;
            }
        }
        if (! $matched) {
            $remainingOcr[$k] = $v;
        }
    }

    $identityOpenDefault = false;
    $piecesOpenDefault = false;
    $decisionOpenDefault = $peutDeciderDossier
        || filled(old('refusal_reason'))
        || filled(old('firstname'))
        || filled(old('email'));
@endphp

<header class="dossier-header px-5 py-5 sm:px-6 mb-6 border-l-4 border-l-[#173052]">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0 flex-1">
            <p class="dossier-kicker">Demande de compte · {{ $demande->code ?: '—' }}</p>
            <div class="mt-1 flex flex-wrap items-center gap-2">
                <h1 class="text-xl font-semibold text-[#173052] tracking-tight">{{ $demande->displayName() ?: $demande->libellePublic() }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $demande->statusBadgeClass() }}">
                    {{ $demande->statusLabel() }}
                </span>
            </div>
            <dl class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-sm text-gray-600">
                @if($demande->nif)
                    <div><span class="text-gray-500">NIF ·</span> <span class="font-medium font-mono">{{ $demande->nif }}</span></div>
                @endif
                @if($demande->telephone)
                    <div><span class="text-gray-500">Tél. ·</span> <span class="font-medium">{{ $demande->telephone }}</span></div>
                @endif
                @if($demande->email)
                    <div><span class="text-gray-500">Courriel ·</span> <span class="font-medium">{{ $demande->email }}</span></div>
                @endif
            </dl>
        </div>
        <a href="{{ route($routePrefix.'.comptes-demandes.index') }}" class="dossier-btn-secondary shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Liste
        </a>
    </div>
</header>

@if($errors->any())
    <div class="mb-4 dossier-notice border-l-red-500 bg-red-50 px-4 py-3 text-sm text-red-800">
        {{ $errors->first() }}
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
<div class="lg:col-span-2 space-y-4 order-2 lg:order-1">

@if($rdvDecisionCompte)
    @php
        $rdvData = $rdvDecisionCompte->data ?? [];
        $rdvStatutCompte = $rdvDecisionCompte->rencontreStatut();
    @endphp
    <section class="dossier-card p-5 space-y-3 border-l-4 border-l-indigo-500">
        <h2 class="text-sm font-semibold text-[#173052]">Rendez-vous Formalités lié</h2>
        <p class="text-sm text-gray-600">
            {{ $rdvDecisionCompte->libellePublic() }}
            · {{ $rdvStatutCompte->label() }}
            @if(!empty($rdvData['date_souhaitee']))
                · {{ \Carbon\Carbon::parse($rdvData['date_souhaitee'])->format('d/m/Y') }}
                @if(!empty($rdvData['heure_souhaitee'])) à {{ $rdvData['heure_souhaitee'] }}@endif
            @endif
        </p>
        <a href="{{ route('personal.request.show', $rdvDecisionCompte->id) }}" class="dossier-link">
            Ouvrir le dossier rendez-vous →
        </a>
    </section>
@elseif($demande->isPending())
    <div class="dossier-notice dossier-notice--amber px-4 py-3 text-sm text-amber-900 bg-amber-50/50">
        Aucun rendez-vous Formalités n’est encore enregistré pour ce demandeur.
        La décision sur le compte ne sera possible qu’après un rendez-vous réalisé (formalités, mandats, etc.).
    </div>
@endif

<x-dossier-collapsible title="Statut du dossier" :subtitle="'Dépôt · '.(($demande->soumise_at ?? $demande->created_at)?->format('d/m/Y') ?? '—')" :open="false">
        <div class="px-5 sm:px-6 py-5 text-sm space-y-4">
            @if($demande->is_mineur)
                <p class="text-amber-800 bg-amber-50 border border-amber-100 px-3 py-2">Pensionné mineur — lien : {{ $demande->representant_lien ?: '—' }}</p>
            @endif
            @if($demande->is_mandataire ?? false)
                <p class="text-gray-700 bg-gray-50 border border-gray-100 px-3 py-2">Mandataire — code pension : {{ $demande->pension_code ?: '—' }}</p>
            @endif
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div><dt class="text-xs text-gray-500">Déposée</dt><dd class="font-medium">{{ ($demande->soumise_at ?? $demande->created_at)?->format('d/m/Y à H:i') }}</dd></div>
                @if($demande->traiteeLe())
                    <div><dt class="text-xs text-gray-500">Traitée</dt><dd class="font-medium">{{ $demande->traiteeLe()->format('d/m/Y à H:i') }}@if($demande->traiteePar) · {{ $demande->traiteePar->displayName() }}@endif</dd></div>
                @endif
            </dl>
            @if($demande->user)
                <div class="border-t border-gray-100 pt-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Compte associé</p>
                    <p class="mt-1 font-medium">{{ $compteLabel }}</p>
                    <p class="text-gray-600 text-sm">Statut · {{ $demande->user->accountStatusLabel() }}</p>
                </div>
            @endif
        </div>
</x-dossier-collapsible>

<x-dossier-collapsible id="dcc-identite" title="Identité et coordonnées" :subtitle="trim(($demande->firstname ?? '').' '.($demande->lastname ?? ''))" :open="$identityOpenDefault" class="scroll-mt-24">
        <dl class="px-5 sm:px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 text-sm">
            <div><dt class="text-xs text-gray-500">Prénom</dt><dd class="font-medium mt-0.5">{{ $demande->firstname ?: '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Nom</dt><dd class="font-medium mt-0.5">{{ $demande->lastname ?: '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">E-mail</dt><dd class="font-medium mt-0.5 break-all">{{ $demande->email ?: '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Téléphone</dt><dd class="font-medium mt-0.5">{{ $demande->telephone ?: '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">NIF</dt><dd class="font-medium mt-0.5 font-mono">{{ $demande->nif ?: '—' }}</dd></div>
            <div><dt class="text-xs text-gray-500">Code pension</dt><dd class="font-medium mt-0.5 font-mono">{{ $demande->pension_code ?: '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-xs text-gray-500">Adresse</dt><dd class="font-medium mt-0.5">{{ $demande->adresse ?: '—' }}</dd></div>
        </dl>
</x-dossier-collapsible>

<x-dossier-collapsible
    id="dcc-pieces"
    title="Pièces jointes"
    :subtitle="count($pieces).' pièce(s)'"
    :open="$piecesOpenDefault"
    class="scroll-mt-24"
>
    <div class="px-5 sm:px-6 py-5 space-y-5">
        @if(count($pieces) === 0)
            <p class="text-sm text-gray-500">
                @if($demande->status === \App\Models\DemandeCreationCompte::STATUS_ACCEPTEE)
                    Pièces supprimées après acceptation pour libérer l’espace.
                @else
                    Aucune pièce disponible.
                @endif
            </p>
        @else
            <ul class="border border-gray-200 divide-y divide-gray-100">
                @foreach($pieces as $piece)
                    @php
                        $media = $piece['media'];
                        $pieceOcr = $ocrForPiece($piece['collection']);
                        $viewUrl = $demande->agentPieceUrl($media, auth()->user(), true) ?? $media->getUrl();
                        $downloadUrl = $demande->agentPieceUrl($media, auth()->user(), false) ?? $media->getUrl();
                    @endphp
                    <li class="px-3 py-2.5 text-sm bg-white">
                        <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                            <div class="min-w-0 flex-1">
                                <p class="font-medium text-gray-900">{{ $piece['label'] }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $media->file_name }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 text-xs font-medium text-[#173052]">
                                <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="hover:underline">Ouvrir</a>
                                <span class="text-gray-300" aria-hidden="true">·</span>
                                <a href="{{ $downloadUrl }}" class="hover:underline">Télécharger</a>
                            </div>
                        </div>
                        @if(! empty($pieceOcr))
                            <dl class="mt-2 pt-2 border-t border-gray-100 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs">
                                @foreach($pieceOcr as $key => $value)
                                    <div>
                                        <dt class="text-gray-500">{{ str_replace('_', ' ', $key) }}</dt>
                                        <dd class="font-medium text-gray-800">{{ $value !== '' && $value !== null ? $value : '—' }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if(! empty($remainingOcr))
            <div class="border-t border-gray-100 pt-4">
                <h3 class="text-xs font-medium uppercase tracking-wide text-gray-500 mb-2">Autres données OCR</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @foreach($remainingOcr as $key => $value)
                        <div>
                            <dt class="text-xs text-gray-500">{{ str_replace('_', ' ', $key) }}</dt>
                            <dd class="font-medium text-gray-900">{{ $value !== '' && $value !== null ? $value : '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif
    </div>
</x-dossier-collapsible>

@if($demande->status === \App\Models\DemandeCreationCompte::STATUS_REFUSEE && $demande->refusal_reason)
    <div class="dossier-notice border-l-red-500 bg-red-50 px-5 py-4 text-sm text-red-900">
        <p class="text-xs font-semibold uppercase tracking-wide text-red-800 mb-1">Motif du refus</p>
        <p>{{ $demande->refusal_reason }}</p>
    </div>
@endif

<x-dossier-collapsible id="dcc-journal" title="Journal d’activité" :open="false" class="scroll-mt-24">
    <div class="px-5 sm:px-6 py-4">
        @forelse($demande->histories as $history)
            <div class="flex gap-3 py-3 {{ ! $loop->last ? 'border-b border-gray-100' : '' }}">
                <div class="text-xs text-gray-400 w-36 shrink-0">{{ $history->created_at?->format('d/m/Y H:i') }}</div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $history->eventLabel() }}</p>
                    <p class="text-sm text-gray-600">{{ $history->commentaire }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $history->actor?->displayName() ?? 'Système' }}
                        @if($history->statut)
                            · {{ $history->statut }}
                        @endif
                    </p>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500">Aucune action enregistrée.</p>
        @endforelse
    </div>
</x-dossier-collapsible>

</div>{{-- /col principale --}}

<aside class="lg:col-span-1 space-y-4 lg:sticky lg:top-4 order-1 lg:order-2">

    @if($demande->isPending())
        @php
            $decisionSubtitle = $peutDeciderDossier
                ? 'Prêt à valider ou refuser'
                : (! $rdvRealise ? 'En attente du RDV réalisé' : 'Consultation');
            $decisionCollapsibleClass = 'scroll-mt-24'.($peutDeciderDossier ? ' border-l-4 border-l-emerald-600' : '');
        @endphp
        <x-dossier-collapsible
            id="dcc-decision"
            title="Décision"
            :subtitle="$decisionSubtitle"
            :open="$decisionOpenDefault"
            :class="$decisionCollapsibleClass"
        >
            <div class="px-5 sm:px-6 py-5 space-y-5">
                @if(! $rdvRealise)
                    <p class="text-sm text-amber-900 dossier-notice dossier-notice--amber px-3 py-2 bg-amber-50/50">
                        Accepter ou refuser n’est possible qu’après un rendez-vous <strong>réalisé</strong>.
                    </p>
                @elseif(! $rdvHeurePassee)
                    <p class="text-sm text-amber-900 dossier-notice dossier-notice--amber px-3 py-2 bg-amber-50/50">
                        @if($rdvDecisionCompte?->rencontreScheduledAt())
                            Décision après {{ $rdvDecisionCompte->rencontreScheduledAt()->format('d/m/Y à H:i') }}.
                        @else
                            Décision pas encore disponible.
                        @endif
                    </p>
                @elseif(! $canDecide)
                    <p class="text-sm text-gray-600">Consultation seule (réservé aux agents Formalités).</p>
                @elseif($peutDeciderDossier)
                    <form method="POST" action="{{ route($routePrefix.'.comptes-demandes.accepter', $demande) }}" class="space-y-3">
                        @csrf
                        <p class="text-sm text-gray-600">Corrigez si besoin, puis acceptez pour activer le compte.</p>
                        <label class="text-sm block">
                            <span class="text-gray-600">Prénom</span>
                            <input name="firstname" value="{{ old('firstname', $demande->firstname) }}" class="dossier-field mt-1">
                        </label>
                        <label class="text-sm block">
                            <span class="text-gray-600">Nom</span>
                            <input name="lastname" value="{{ old('lastname', $demande->lastname) }}" class="dossier-field mt-1">
                        </label>
                        <label class="text-sm block">
                            <span class="text-gray-600">E-mail</span>
                            <input type="email" name="email" value="{{ old('email', $demande->email ?? $demande->user?->email) }}" class="dossier-field mt-1">
                        </label>
                        <button type="submit" class="dossier-btn-success w-full justify-center">
                            Accepter le dossier
                        </button>
                    </form>

                    <form method="POST" action="{{ route($routePrefix.'.comptes-demandes.refuser', $demande) }}" class="space-y-3 pt-4 border-t border-gray-100">
                        @csrf
                        <label class="text-sm block">
                            <span class="text-gray-600">Motif du refus</span>
                            <textarea name="refusal_reason" rows="3" required minlength="5" class="dossier-field mt-1">{{ old('refusal_reason') }}</textarea>
                        </label>
                        <button type="submit" class="dossier-btn-danger-outline w-full justify-center">
                            Refuser le dossier
                        </button>
                    </form>
                @endif
            </div>
        </x-dossier-collapsible>
    @endif

    @if($demande->user)
        <div class="dossier-card p-4 text-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Compte lié</p>
            <p class="font-medium text-gray-900 mt-1">{{ $compteLabel }}</p>
            <p class="text-gray-600 mt-1">Statut · <strong>{{ $demande->user->accountStatusLabel() }}</strong></p>
        </div>
    @endif
</aside>

</div>{{-- /grid --}}