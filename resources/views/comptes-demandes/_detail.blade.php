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

    $identityOpenDefault = $demande->isPending();
    $piecesOpenDefault = count($pieces) > 0;
    $decisionOpenDefault = $peutDeciderDossier
        || filled(old('refusal_reason'))
        || filled(old('firstname'))
        || filled(old('email'));
    $stepRdv = $rdvRealise ? 'done' : ($demande->isPending() ? 'current' : 'done');
    $stepPieces = count($pieces) > 0 ? 'done' : ($demande->isPending() ? 'current' : 'muted');
    $stepDecision = $peutDeciderDossier ? 'current' : (! $demande->isPending() ? 'done' : 'muted');
@endphp

<header class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
    <div class="h-1 w-full bg-gradient-to-r from-[#173052] via-[#1e3d6b] to-[#2a5085]"></div>
    <div class="px-5 py-5 sm:px-6 flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-start gap-4 min-w-0">
            <div class="w-12 h-12 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-[#173052]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h1 class="text-lg font-bold text-gray-900 tracking-tight">{{ $demande->libellePublic() }}</h1>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $demande->statusBadgeClass() }}">
                        {{ $demande->statusLabel() }}
                    </span>
                </div>
                <p class="text-sm text-gray-600">{{ $demande->displayName() }}</p>
                <div class="flex flex-wrap gap-2 mt-2 text-xs">
                    @if($demande->code)
                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 font-mono">{{ $demande->code }}</span>
                    @endif
                    @if($demande->nif)
                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">NIF {{ $demande->nif }}</span>
                    @endif
                    @if($demande->telephone)
                        <span class="px-2 py-0.5 rounded-md bg-gray-100 text-gray-600">{{ $demande->telephone }}</span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route($routePrefix.'.comptes-demandes.index') }}"
           class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour à la liste
        </a>
    </div>
    @if($demande->isPending())
        <div class="px-5 sm:px-6 pb-4">
            <ol class="flex flex-wrap items-center gap-2 text-xs font-medium">
                @foreach([
                    ['label' => 'Rendez-vous réalisé', 'state' => $stepRdv],
                    ['label' => 'Pièces vérifiées', 'state' => $stepPieces],
                    ['label' => 'Décision agent', 'state' => $stepDecision],
                ] as $step)
                    <li @class([
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full ring-1',
                        'bg-green-50 text-green-800 ring-green-200' => $step['state'] === 'done',
                        'bg-sky-50 text-[#173052] ring-sky-200' => $step['state'] === 'current',
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
    @endif
</header>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm space-y-1">
        <p>{{ session('success') }}</p>
        @if(session('created_password'))
            <p class="font-mono text-sm">Mot de passe provisoire : <strong>{{ session('created_password') }}</strong></p>
            <p class="text-xs text-green-700">Communiquez-le au demandeur (il n’apparaîtra plus après cette page).</p>
        @endif
    </div>
@endif
@if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
        {{ $errors->first() }}
    </div>
@endif

<nav class="lg:hidden flex flex-wrap gap-2 mb-4 text-xs font-semibold" aria-label="Sections du dossier">
    <a href="#dcc-identite" class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700">Identité</a>
    <a href="#dcc-pieces" class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700">Pièces</a>
    @if($demande->isPending())
        <a href="#dcc-decision" class="px-2.5 py-1 rounded-lg bg-sky-50 border border-sky-200 text-[#173052]">Décision</a>
    @endif
    <a href="#dcc-journal" class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700">Journal</a>
</nav>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
<div class="lg:col-span-2 space-y-4 order-2 lg:order-1">

@if($rdvDecisionCompte)
    @php
        $rdvData = $rdvDecisionCompte->data ?? [];
        $rdvStatutCompte = $rdvDecisionCompte->rencontreStatut();
    @endphp
    <section class="bg-white border border-sky-200 rounded-2xl p-5 space-y-3 shadow-sm">
        <h2 class="text-sm font-bold text-[#173052]">Rendez-vous Formalités lié</h2>
        @include('demandes.rencontre._workflow-banner', [
            'demande' => $rdvDecisionCompte,
            'rdvStatut' => $rdvStatutCompte,
            'compteCreation' => $demande,
        ])
        <p class="text-sm text-gray-600">
            {{ $rdvDecisionCompte->libellePublic() }}
            · {{ $rdvStatutCompte->label() }}
            @if(!empty($rdvData['date_souhaitee']))
                · {{ \Carbon\Carbon::parse($rdvData['date_souhaitee'])->format('d/m/Y') }}
                @if(!empty($rdvData['heure_souhaitee'])) à {{ $rdvData['heure_souhaitee'] }}@endif
            @endif
        </p>
        <a href="{{ route('personal.request.show', $rdvDecisionCompte->id) }}"
           class="inline-flex text-sm font-semibold text-navy hover:underline">
            Ouvrir le dossier rendez-vous →
        </a>
    </section>
@elseif($demande->isPending())
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
        Aucun rendez-vous Formalités n’est encore enregistré pour ce demandeur.
        La décision sur le compte ne sera possible qu’après un rendez-vous réalisé (formalités, mandats, etc.).
    </div>
@endif

{{-- Statut — résumé compact --}}
<section class="bg-white border border-gray-200 rounded-2xl shadow-sm" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left hover:bg-gray-50/50 rounded-2xl transition-colors">
        <div>
            <h2 class="text-base font-bold text-gray-800">Statut du dossier</h2>
            <p class="text-xs text-gray-500 mt-0.5" x-show="!open">Déposée le {{ ($demande->soumise_at ?? $demande->created_at)?->format('d/m/Y') }}</p>
        </div>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="px-6 pb-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-wide text-gray-400">Statut</p>
                <p class="inline-flex mt-1 text-sm font-semibold px-2.5 py-1 rounded-full {{ $demande->statusBadgeClass() }}">{{ $demande->statusLabel() }}</p>
                @if($demande->is_mineur)
                    <p class="text-xs text-amber-700 mt-2">Dossier pensionné mineur — lien : {{ $demande->representant_lien ?: '—' }}</p>
                @endif
                @if($demande->is_mandataire ?? false)
                    <p class="text-xs text-[#173052]/80 mt-2">Ouverture de compte mandataire — code pension : {{ $demande->pension_code ?: '—' }}</p>
                @endif
            </div>
            <div class="text-sm text-gray-600">
                <p>Déposée le {{ ($demande->soumise_at ?? $demande->created_at)?->format('d/m/Y à H:i') }}</p>
                @if($demande->traiteeLe())
                    <p>Traitée le {{ $demande->traiteeLe()->format('d/m/Y à H:i') }}
                        @if($demande->traiteePar)
                            par {{ $demande->traiteePar->displayName() }}
                        @endif
                    </p>
                @endif
                @if($demande->user)
                    <div class="mt-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
                        <p class="text-xs uppercase tracking-wide text-gray-500 font-semibold">Compte associé</p>
                        <p class="mt-1">Utilisateur #{{ $demande->user_id }} · {{ $compteLabel }}</p>
                        <p>Statut du compte : <strong>{{ $demande->user->accountStatusLabel() }}</strong></p>
                        @if($demande->user->nif)
                            <p class="text-gray-600">NIF {{ $demande->user->nif }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Identité et coordonnées --}}
<section id="dcc-identite" class="bg-white border border-gray-200 rounded-2xl shadow-sm scroll-mt-24" x-data="{ open: @js($identityOpenDefault) }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left hover:bg-gray-50/50 rounded-2xl transition-colors">
        <div>
            <h2 class="text-base font-bold text-gray-800">Identité et coordonnées</h2>
            <p class="text-xs text-gray-500 mt-0.5" x-show="!open">{{ $demande->firstname }} {{ $demande->lastname }} · {{ $demande->email ?: 'sans e-mail' }}</p>
        </div>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="px-6 pb-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div><dt class="text-gray-500">Prénom</dt><dd class="font-medium">{{ $demande->firstname ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">Nom</dt><dd class="font-medium">{{ $demande->lastname ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">E-mail</dt><dd class="font-medium">{{ $demande->email ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $demande->telephone ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">NIF</dt><dd class="font-medium">{{ $demande->nif ?: '—' }}</dd></div>
            <div><dt class="text-gray-500">Code pension</dt><dd class="font-medium">{{ $demande->pension_code ?: '—' }}</dd></div>
            <div class="md:col-span-2"><dt class="text-gray-500">Adresse</dt><dd class="font-medium">{{ $demande->adresse ?: '—' }}</dd></div>
        </dl>
    </div>
</section>

{{-- Pièces jointes + Données OCR --}}
<section id="dcc-pieces" class="bg-white border border-gray-200 rounded-2xl shadow-sm scroll-mt-24" x-data="{ open: @js($piecesOpenDefault) }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left hover:bg-gray-50/50 rounded-2xl transition-colors">
        <div>
            <h2 class="text-base font-bold text-gray-800">Pièces jointes et OCR</h2>
            <p class="text-xs text-gray-500 mt-0.5" x-show="!open">{{ count($pieces) }} pièce(s) · ouvrir pour prévisualiser et télécharger</p>
        </div>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="px-6 pb-6 space-y-5">

        @if(count($pieces) === 0)
            <p class="text-sm text-gray-500">
                @if($demande->status === \App\Models\DemandeCreationCompte::STATUS_ACCEPTEE)
                    Pièces supprimées après acceptation pour libérer l’espace.
                @else
                    Aucune pièce disponible.
                @endif
            </p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($pieces as $piece)
                    @php
                        $media   = $piece['media'];
                        $isImage = str_starts_with((string) $media->mime_type, 'image/');
                        $isPdf   = $media->mime_type === 'application/pdf';
                        $pieceOcr = $ocrForPiece($piece['collection']);
                        $viewUrl = $demande->agentPieceUrl($media, auth()->user(), true) ?? $media->getUrl();
                        $downloadUrl = $demande->agentPieceUrl($media, auth()->user(), false) ?? $media->getUrl();
                    @endphp
                    <article class="border border-gray-200 rounded-xl overflow-hidden">
                        {{-- En-tête : libellé + bouton ouvrir --}}
                        <div class="px-3 py-2 bg-gray-50 text-sm font-semibold text-gray-700 flex items-center justify-between gap-2">
                            <span>{{ $piece['label'] }}</span>
                            <span class="flex items-center gap-2 shrink-0">
                                <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="text-navy text-xs font-semibold hover:underline">Ouvrir</a>
                                <a href="{{ $downloadUrl }}" class="text-navy text-xs font-semibold hover:underline">Télécharger</a>
                            </span>
                        </div>

                        {{-- Aperçu du média --}}
                        @if($isImage)
                            <a href="{{ $viewUrl }}" target="_blank" rel="noopener">
                                <img src="{{ $viewUrl }}" alt="{{ $piece['label'] }}" class="w-full max-h-72 object-contain bg-gray-100">
                            </a>
                        @elseif($isPdf)
                            <div x-data="{ preview: false }">
                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-y border-gray-100">
                                    <svg class="w-8 h-8 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M7 21h10a2 2 0 002-2V9l-5-5H7a2 2 0 00-2 2v13a2 2 0 002 2z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M14 3v6h6M9 13h6M9 17h4"/>
                                    </svg>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-800 truncate">{{ $media->file_name }}</p>
                                        <p class="text-xs text-gray-500">Document PDF</p>
                                    </div>
                                    <button type="button" @click="preview = !preview"
                                            class="text-xs text-navy font-semibold hover:underline shrink-0">
                                        <span x-text="preview ? 'Masquer l’aperçu' : 'Aperçu'"></span>
                                    </button>
                                </div>
                                <div x-show="preview" x-collapse>
                                    <iframe src="{{ $viewUrl }}#toolbar=1&view=FitH"
                                            class="w-full h-96 bg-gray-100"
                                            title="Aperçu {{ $piece['label'] }}"></iframe>
                                    <p class="text-xs text-gray-400 px-3 py-2">
                                        Si l’aperçu ne s’affiche pas,
                                        <a href="{{ $viewUrl }}" target="_blank" rel="noopener" class="text-navy hover:underline">ouvrez le PDF dans un nouvel onglet</a>.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 flex items-center justify-between gap-3">
                                <p class="text-sm text-gray-600 truncate">{{ $media->file_name }}</p>
                                <a href="{{ $downloadUrl }}"
                                   class="text-xs text-navy font-semibold hover:underline shrink-0">Télécharger</a>
                            </div>
                        @endif

                        {{-- Données OCR attachées à cette pièce --}}
                        @if(!empty($pieceOcr))
                            <div class="border-t border-gray-100 bg-amber-50/40 px-4 py-3">
                                <p class="text-xs uppercase tracking-wide text-amber-700 font-semibold mb-2">
                                    Données lues (OCR)
                                </p>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                    @foreach($pieceOcr as $key => $value)
                                        <div>
                                            <dt class="text-gray-500">{{ str_replace('_', ' ', $key) }}</dt>
                                            <dd class="font-medium text-gray-800">{{ $value !== '' && $value !== null ? $value : '—' }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif

        {{-- Données OCR non rattachées à une pièce précise --}}
        @if(!empty($remainingOcr))
            <div class="border border-gray-200 rounded-xl overflow-hidden">
                <div class="px-4 py-2 bg-gray-50 text-sm font-semibold text-gray-700">
                    Autres données lues (OCR)
                </div>
                <div class="p-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        @foreach($remainingOcr as $key => $value)
                            <div>
                                <dt class="text-gray-500">{{ str_replace('_', ' ', $key) }}</dt>
                                <dd class="font-medium">{{ $value !== '' && $value !== null ? $value : '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- Motif du refus — REPLIÉ --}}
@if($demande->status === \App\Models\DemandeCreationCompte::STATUS_REFUSEE && $demande->refusal_reason)
    <section class="bg-red-50 border border-red-200 rounded-2xl text-red-800 shadow-sm" x-data="{ open: true }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
            <h2 class="font-bold">Motif du refus</h2>
            <svg class="w-5 h-5 text-red-400 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse class="px-6 pb-6 text-sm">
            <p>{{ $demande->refusal_reason }}</p>
        </div>
    </section>
@endif

{{-- Journal d'activité --}}
<section id="dcc-journal" class="bg-white border border-gray-200 rounded-2xl shadow-sm scroll-mt-24" x-data="{ open: false }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
        <h2 class="text-base font-bold text-gray-800">Journal d’activité</h2>
        <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-collapse class="px-6 pb-6">
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
</section>

</div>{{-- /col principale --}}

<aside class="lg:col-span-1 space-y-4 lg:sticky lg:top-4 order-1 lg:order-2">

    <div class="hidden lg:block bg-white border border-gray-200 rounded-2xl p-4 shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wide text-gray-500 mb-2">Aller à</p>
        <ul class="space-y-1 text-sm font-medium">
            <li><a href="#dcc-identite" class="text-[#173052] hover:underline">Identité</a></li>
            <li><a href="#dcc-pieces" class="text-[#173052] hover:underline">Pièces & OCR</a></li>
            @if($demande->isPending())
                <li><a href="#dcc-decision" class="text-[#173052] hover:underline">Décision</a></li>
            @endif
            <li><a href="#dcc-journal" class="text-[#173052] hover:underline">Journal</a></li>
        </ul>
    </div>

    @if($demande->isPending())
        <section id="dcc-decision" class="bg-white border-2 rounded-2xl shadow-sm scroll-mt-24 @if($peutDeciderDossier) border-green-200 @else border-gray-200 @endif" x-data="{ open: @js($decisionOpenDefault) }">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between gap-3 px-5 py-4 text-left lg:hidden">
                <div>
                    <h2 class="text-base font-bold text-gray-800">Décision</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        @if($peutDeciderDossier)
                            Prêt à valider ou refuser
                        @elseif(! $rdvRealise)
                            En attente du RDV réalisé
                        @else
                            Consultation
                        @endif
                    </p>
                </div>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="hidden lg:block px-5 pt-4 pb-1">
                <h2 class="text-base font-bold text-gray-800">Décision</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    @if($peutDeciderDossier)
                        Prêt à valider ou refuser
                    @elseif(! $rdvRealise)
                        En attente du RDV réalisé
                    @else
                        Consultation
                    @endif
                </p>
            </div>
            <div x-show="open" x-collapse class="px-5 pb-5 space-y-5 lg:!block">
                @if(! $rdvRealise)
                    <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                        Accepter ou refuser n’est possible qu’après un rendez-vous <strong>réalisé</strong>.
                    </p>
                @elseif(! $rdvHeurePassee)
                    <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
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
                            <input name="firstname" value="{{ old('firstname', $demande->firstname) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <label class="text-sm block">
                            <span class="text-gray-600">Nom</span>
                            <input name="lastname" value="{{ old('lastname', $demande->lastname) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <label class="text-sm block">
                            <span class="text-gray-600">E-mail</span>
                            <input type="email" name="email" value="{{ old('email', $demande->email ?? $demande->user?->email) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        </label>
                        <button type="submit" class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl shadow-sm">
                            Accepter le dossier
                        </button>
                    </form>

                    <form method="POST" action="{{ route($routePrefix.'.comptes-demandes.refuser', $demande) }}" class="space-y-3 pt-4 border-t border-gray-100">
                        @csrf
                        <label class="text-sm block">
                            <span class="text-gray-600">Motif du refus</span>
                            <textarea name="refusal_reason" rows="3" required minlength="5" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">{{ old('refusal_reason') }}</textarea>
                        </label>
                        <button type="submit" class="w-full px-4 py-2.5 bg-white border border-red-200 text-red-700 hover:bg-red-50 text-sm font-semibold rounded-xl">
                            Refuser le dossier
                        </button>
                    </form>
                @endif
            </div>
        </section>
    @endif

    @if($demande->user)
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-gray-500">Compte lié</p>
            <p class="font-medium text-gray-900 mt-1">{{ $compteLabel }}</p>
            <p class="text-gray-600 mt-1">Statut : <strong>{{ $demande->user->accountStatusLabel() }}</strong></p>
        </div>
    @endif
</aside>

</div>{{-- /grid --}}