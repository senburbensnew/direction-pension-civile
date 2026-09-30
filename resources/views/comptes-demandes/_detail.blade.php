@php
    $routePrefix = $routePrefix ?? 'formalites';
    $canDecide = $canDecide ?? false;
    $pieces = $demande->identityPieces();
    $ocrFields = $demande->ocr_fields ?? [];
    $ocrDocuments = $demande->ocr_documents ?? [];
    $rdvRealise = $demande->hasRendezVousRealise();

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
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Demande {{ $demande->code }}</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $demande->displayName() }}@if($demande->nif) · {{ $demande->nif }}@endif</p>
    </div>
    <a href="{{ route($routePrefix.'.comptes-demandes.index') }}" class="text-sm text-navy font-semibold hover:underline">Retour à la liste</a>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm space-y-1">
        <p>{{ session('success') }}</p>
        @if(session('created_password'))
            <p class="font-mono text-sm">Mot de passe provisoire : <strong>{{ session('created_password') }}</strong></p>
            <p class="text-xs text-green-700">Communiquez-le au demandeur (il n’apparaîtra plus après cette page).</p>
        @endif
    </div>
@endif
@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        {{ $errors->first() }}
    </div>
@endif

{{-- Statut — OUVERT PAR DÉFAUT --}}
<section class="bg-white border border-gray-200 rounded-2xl" x-data="{ open: true }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
        <h2 class="text-base font-bold text-gray-800">Statut</h2>
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
            </div>
            <div class="text-sm text-gray-600">
                <p>Déposée le {{ $demande->created_at?->format('d/m/Y à H:i') }}</p>
                @if($demande->reviewed_at)
                    <p>Traitée le {{ $demande->reviewed_at->format('d/m/Y à H:i') }}
                        @if($demande->reviewer)
                            par {{ $demande->reviewer->displayName() }}
                        @endif
                    </p>
                @endif
                @if($demande->user)
                    <p>Compte : <strong>{{ $compteLabel }}</strong>@if($demande->user->nif) · NIF {{ $demande->user->nif }}@endif</p>
                    <p>Statut du compte : <strong>{{ $demande->user->accountStatusLabel() }}</strong>@unless($demande->user->is_active) · désactivé@endunless</p>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- Identité et coordonnées — REPLIÉ --}}
<section class="bg-white border border-gray-200 rounded-2xl" x-data="{ open: false }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
        <h2 class="text-base font-bold text-gray-800">Identité et coordonnées</h2>
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

{{-- Pièces jointes + Données OCR — REPLIÉ --}}
<section class="bg-white border border-gray-200 rounded-2xl" x-data="{ open: false }">
    <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
        <h2 class="text-base font-bold text-gray-800">Pièces jointes et données lues (OCR)</h2>
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
                    @endphp
                    <article class="border border-gray-200 rounded-xl overflow-hidden">
                        {{-- En-tête : libellé + bouton ouvrir --}}
                        <div class="px-3 py-2 bg-gray-50 text-sm font-semibold text-gray-700 flex items-center justify-between">
                            <span>{{ $piece['label'] }}</span>
                            <a href="{{ $media->getUrl() }}" target="_blank" class="text-navy text-xs font-semibold hover:underline">Ouvrir</a>
                        </div>

                        {{-- Aperçu du média --}}
                        @if($isImage)
                            <a href="{{ $media->getUrl() }}" target="_blank">
                                <img src="{{ $media->getUrl() }}" alt="{{ $piece['label'] }}" class="w-full max-h-72 object-contain bg-gray-100">
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
                                    <iframe src="{{ $media->getUrl() }}#toolbar=1&view=FitH"
                                            class="w-full h-96 bg-gray-100"
                                            title="Aperçu {{ $piece['label'] }}"></iframe>
                                    <p class="text-xs text-gray-400 px-3 py-2">
                                        Si l’aperçu ne s’affiche pas,
                                        <a href="{{ $media->getUrl() }}" target="_blank" class="text-navy hover:underline">ouvrez le PDF dans un nouvel onglet</a>.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="p-4 bg-gray-50 flex items-center justify-between gap-3">
                                <p class="text-sm text-gray-600 truncate">{{ $media->file_name }}</p>
                                <a href="{{ $media->getUrl() }}" target="_blank"
                                   class="text-xs text-navy font-semibold hover:underline shrink-0">Ouvrir</a>
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
    <section class="bg-red-50 border border-red-200 rounded-2xl text-red-800" x-data="{ open: false }">
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

{{-- Décision — REPLIÉ --}}
@if($demande->isPending())
    <section class="bg-white border border-gray-200 rounded-2xl" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-3 px-6 py-4 text-left">
            <h2 class="text-base font-bold text-gray-800">Décision</h2>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse class="px-6 pb-6 space-y-6">
            @if(! $rdvRealise)
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                    La décision (accepter ou refuser) n’est possible qu’après un rendez-vous réalisé.
                </p>
            @elseif(! $canDecide)
                <p class="text-sm text-gray-600">Consultation uniquement. Seuls les agents Formalités peuvent accepter ou refuser après le rendez-vous.</p>
            @else
                <form method="POST" action="{{ route($routePrefix.'.comptes-demandes.accepter', $demande) }}" class="space-y-4">
                    @csrf
                    <p class="text-sm text-gray-600">Le rendez-vous a été réalisé. L’acceptation active le compte.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="text-sm">
                            <span class="text-gray-600">Prénom</span>
                            <input name="firstname" value="{{ old('firstname', $demande->firstname) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">
                        </label>
                        <label class="text-sm">
                            <span class="text-gray-600">Nom</span>
                            <input name="lastname" value="{{ old('lastname', $demande->lastname) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">
                        </label>
                        <label class="text-sm md:col-span-2">
                            <span class="text-gray-600">E-mail</span>
                            <input type="email" name="email" value="{{ old('email', $demande->email ?? $demande->user?->email) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">
                        </label>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                        Accepter le dossier
                    </button>
                </form>

                <form method="POST" action="{{ route($routePrefix.'.comptes-demandes.refuser', $demande) }}" class="space-y-3 border-t border-gray-100 pt-5">
                    @csrf
                    <label class="text-sm block">
                        <span class="text-gray-600">Motif du refus</span>
                        <textarea name="refusal_reason" rows="3" required minlength="5" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('refusal_reason') }}</textarea>
                    </label>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg">
                        Refuser
                    </button>
                </form>
            @endif
        </div>
    </section>
@endif

{{-- Journal d'activité — REPLIÉ --}}
<section class="bg-white border border-gray-200 rounded-2xl" x-data="{ open: false }">
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