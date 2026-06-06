@inject('storage', 'Illuminate\Support\Facades\Storage')

@php
    $editableTypes = [
        'DEMANDE_ETAT_CARRIERE'     => 'demandes.demande-etat-carriere.create',
        'DEMANDE_PENSION'           => 'demandes.demande-pension-standard.create',
        'DEMANDE_PENSION_REVERSION' => 'demandes.demande-pension-reversion.create',
        'DEMANDE_ADHESION'          => 'demandes.demande-adhesion.create',
        'DEMANDE_VIREMENT_BANCAIRE' => 'demandes.virements.create',
        'DEMANDE_ATTESTATION'       => 'demandes.attestations.create',
        'DEMANDE_TRANSFERT_CHEQUE'  => 'demandes.transfert-cheque.create',
        'DEMANDE_ARRET_PAIEMENT'    => 'demandes.arret-paiement.create',
        'DEMANDE_REINSERTION'       => 'demandes.demande-reinsertion.create',
        'DEMANDE_ARRET_VIREMENT'    => 'demandes.demande-arret-virement.create',
        'DEMANDE_PREUVE_EXISTENCE'  => 'demandes.preuve-existence.create',
    ];
    $editRoute = $editableTypes[$request->type] ?? null;
@endphp

<x-app-layout>
    {{-- ══ HEADER UNIFIÉ ══ --}}
    <div class="max-w-7xl mx-auto pt-5 sm:px-6 lg:px-8">

        {{-- Fil d'Ariane --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-3 px-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            @if($from === 'dashboard')
                <a href="{{ route('personal.dashboard') }}" class="hover:text-gray-600 transition-colors">Mes demandes</a>
            @elseif($from === 'cart')
                <a href="{{ route('personal.cart') }}" class="hover:text-gray-600 transition-colors">Corbeille</a>
            @endif
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-500 font-medium">Détail du dossier</span>
        </nav>

        {{-- Carte header principale --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- Bande colorée supérieure --}}
            <div class="h-1 w-full bg-gradient-to-r from-blue-600 via-indigo-500 to-purple-500"></div>

            <div class="px-6 py-5">
                <div class="flex flex-wrap justify-between items-start gap-4">

                    {{-- Identité du dossier --}}
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h1 class="text-lg font-bold text-gray-900 font-mono tracking-tight">#{{ $request->code }}</h1>
                                @if($request->status)
                                    @php
                                        $statusColors = [
                                            'BROUILLON'         => 'bg-gray-100 text-gray-600 ring-gray-200',
                                            'SOUMISE'           => 'bg-blue-50 text-blue-700 ring-blue-200',
                                            'TRANSFEREE'        => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
                                            'EN_COURS'          => 'bg-violet-50 text-violet-700 ring-violet-200',
                                            'COMPLEMENT_REQUIS' => 'bg-orange-50 text-orange-700 ring-orange-200',
                                            'APPROUVEE'         => 'bg-green-50 text-green-700 ring-green-200',
                                            'REJETEE'           => 'bg-red-50 text-red-700 ring-red-200',
                                            'CLOTUREE'          => 'bg-slate-100 text-slate-600 ring-slate-200',
                                        ];
                                        $sc = $statusColors[$request->status->code] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 {{ $sc }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
                                        {{ $request->status->label ?? $request->status->code }}
                                    </span>
                                @endif
                                @if($request->is_urgent ?? false)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700 ring-1 ring-red-200">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        URGENT
                                    </span>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500">{{ str_replace('_', ' ', $request->type ?? '—') }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Déposé le {{ $request->created_at->format('d/m/Y') }} à {{ $request->created_at->format('H:i') }}</p>
                        </div>
                    </div>

                    {{-- Toolbar d'actions --}}
                    <div class="flex flex-wrap items-center gap-2">

                        @if($from === 'cart')
                            @hasanyrole('secretariat|direction|service_liquidation|service_formalite|service_controle_placement|service_comptabilite|service_assurance|administration|admin')

                                @if(isset($isClosed) && $isClosed)
                                    {{-- Dossier clôturé — aucune action possible --}}
                                    <div class="flex items-center gap-2 bg-gray-50 border border-gray-300 rounded-xl px-3 py-2">
                                        <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-bold text-gray-700 leading-none">Dossier clôturé</p>
                                            <p class="text-xs text-gray-500 mt-0.5">Aucune action disponible</p>
                                        </div>
                                    </div>
                                    {{-- Imprimer / PDF toujours disponibles --}}
                                    @if($request->isAnnotated())
                                        <a href="{{ route('demande.print', $request->id) }}" target="_blank" title="Imprimer"
                                           class="inline-flex items-center justify-center w-9 h-9 bg-gray-700 hover:bg-gray-800 text-white rounded-xl transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                        </a>
                                        <a href="{{ route('demande.pdf', $request->id) }}" target="_blank" title="Télécharger PDF"
                                           class="inline-flex items-center justify-center w-9 h-9 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </a>
                                    @endif

                                @elseif(isset($pendingAffectation) && $pendingAffectation)
                                    {{-- Mode consultation — seul l'avis est autorisé --}}
                                    <div class="flex items-center gap-2 bg-indigo-50 border border-indigo-300 rounded-xl px-3 py-2">
                                        <svg class="w-4 h-4 text-indigo-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-bold text-indigo-800 leading-none">Mode consultation</p>
                                            <p class="text-xs text-indigo-600 mt-0.5">Soumettez votre avis ci-contre</p>
                                        </div>
                                    </div>

                                @elseif(isset($pendingWorkflow) && $pendingWorkflow)
                                    {{-- Verrou — réception en attente --}}
                                    <div class="flex items-center gap-2 bg-amber-50 border border-amber-300 rounded-xl px-3 py-2">
                                        <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        <div>
                                            <p class="text-xs font-bold text-amber-800 leading-none">Actions bloquées</p>
                                            <p class="text-xs text-amber-600 mt-0.5">Confirmez d'abord la réception</p>
                                        </div>
                                    </div>

                                @else
                                    {{-- Transférer --}}
                                    @if($request->isAnnotated())
                                        <button onclick="document.getElementById('transferModal').classList.remove('hidden')"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            Transférer
                                        </button>
                                    @else
                                        <button disabled title="Annoter le dossier d'abord"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400 text-sm font-medium rounded-xl cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                            Transférer
                                        </button>
                                    @endif

                                    {{-- Complément --}}
                                    @if($request->status->code !== 'COMPLEMENT_REQUIS')
                                        <button onclick="document.getElementById('complementModal').classList.remove('hidden'); document.getElementById('complementModal').closest('.bg-white').classList.remove('hidden')"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Complément
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-2 bg-orange-50 text-orange-700 text-xs font-semibold rounded-xl ring-1 ring-orange-200">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            En attente complément
                                        </span>
                                    @endif

                                    {{-- Séparateur --}}
                                    <div class="w-px h-6 bg-gray-200"></div>

                                    {{-- Imprimer --}}
                                    @if($request->isAnnotated())
                                        <a href="{{ route('demande.print', $request->id) }}" target="_blank"
                                           title="Imprimer"
                                           class="inline-flex items-center justify-center w-9 h-9 bg-gray-700 hover:bg-gray-800 text-white rounded-xl transition-all shadow-sm hover:shadow">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('demande.pdf', $request->id) }}" target="_blank"
                                           title="Télécharger PDF"
                                           class="inline-flex items-center justify-center w-9 h-9 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all shadow-sm hover:shadow">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </a>
                                    @else
                                        <button disabled title="Annoter d'abord pour imprimer"
                                                class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-300 rounded-xl cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                            </svg>
                                        </button>
                                        <button disabled title="Annoter d'abord pour télécharger"
                                                class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 text-gray-300 rounded-xl cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </button>
                                    @endif
                                @endif

                            @endhasanyrole
                        @endif

                        @if($from === 'dashboard')
                            @if($editRoute && $request->canBeEditedByUser())
                                <a href="{{ route($editRoute, $request->id) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifier
                                </a>
                            @endif
                            @if($request->isDraft())
                                <button type="button" onclick="document.getElementById('deleteConfirmModal').classList.remove('hidden')"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-all shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Supprimer
                                </button>
                            @endif
                        @endif

                        {{-- Bouton retour --}}
                        @if($from === 'dashboard')
                            <a href="{{ route('personal.dashboard') }}"
                               title="{{ __('messages.back_to_dashboard') }}"
                               class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                        @elseif($from === 'cart')
                            <a href="{{ route('personal.cart') }}"
                               title="{{ __('messages.back_to_basket') }}"
                               class="inline-flex items-center justify-center w-9 h-9 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                            </a>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════ LAYOUT DEUX COLONNES ══════════════ --}}
    <div class="max-w-7xl mx-auto mt-5 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-6 gap-5 items-start">

            {{-- ─── COLONNE DROITE (visuellement) : réception, annotation, messages ─── --}}
            <div class="lg:col-span-2 lg:order-2 lg:sticky lg:top-4 space-y-4">

    {{-- ====================== ALERTE RÉCEPTION EN ATTENTE ====================== --}}
    @if($from === 'cart' && isset($pendingWorkflow) && $pendingWorkflow)
        <div class="bg-white rounded-2xl border-2 border-amber-400 shadow-sm overflow-hidden" x-data="{ showRefus: false }">
            <div class="bg-amber-400 px-4 py-2.5 flex items-center gap-2">
                <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span class="text-white font-bold text-sm tracking-wide">RÉCEPTION REQUISE</span>
            </div>
            <div class="p-4">
                <p class="text-sm text-gray-700 mb-1">
                    Dossier transféré
                    @if($pendingWorkflow->fromService)
                        depuis <strong class="text-gray-900">{{ $pendingWorkflow->fromService->nom }}</strong>
                    @endif
                    vers <strong class="text-gray-900">{{ $pendingWorkflow->toService?->nom ?? 'votre service' }}</strong>.
                </p>
                <p class="text-xs text-gray-400 mb-1">{{ $pendingWorkflow->created_at->diffForHumans() }}</p>
                @if($pendingWorkflow->commentaire)
                    <p class="text-xs text-gray-500 italic bg-gray-50 rounded-lg px-3 py-2 mb-3 border border-gray-100">"{{ $pendingWorkflow->commentaire }}"</p>
                @endif
                <p class="text-xs text-amber-800 font-semibold bg-amber-50 rounded-lg px-3 py-2 border border-amber-100 mb-4">
                    Vous devez confirmer ou refuser la réception avant toute autre action sur ce dossier.
                </p>

                <div class="space-y-2">
                    <form method="POST" action="{{ route('admin.workflows.accepter', $pendingWorkflow) }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-sm hover:shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Confirmer la réception
                        </button>
                    </form>
                    <button type="button" @click="showRefus = !showRefus"
                            class="w-full flex items-center justify-center gap-2 bg-white hover:bg-red-50 text-red-600 border border-red-200 hover:border-red-300 text-sm font-semibold py-2.5 rounded-xl transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Refuser la réception
                    </button>
                </div>

                <div x-show="showRefus" x-cloak class="mt-3 bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="text-xs font-semibold text-red-700 mb-2">
                        Le dossier sera retourné à {{ $pendingWorkflow->fromService->nom ?? "l'expéditeur" }}.
                    </p>
                    <form method="POST" action="{{ route('admin.workflows.refuser', $pendingWorkflow) }}">
                        @csrf
                        <textarea name="motif" rows="2" placeholder="Motif du refus (optionnel)"
                                  class="w-full border border-red-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 bg-white mb-2 resize-none"></textarea>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold py-2 rounded-lg transition-colors">
                                Confirmer
                            </button>
                            <button type="button" @click="showRefus = false"
                                    class="px-4 text-gray-500 hover:text-gray-700 text-sm py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
    {{-- ================================================================ --}}

    {{-- ====================== LOCALISATION DU DOSSIER (vue usager) ====================== --}}
    @if($from === 'dashboard' && !$request->isDraft())
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4">
            <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-blue-800 mb-1">{{ __('messages.file_location') }}</p>
                        <p class="text-sm text-blue-700">
                            {{ __('messages.file_currently_at') }}
                            <span class="font-semibold">{{ $request->service?->nom ?? 'Direction des Pensions Civiles' }}</span>
                        </p>

                        {{-- Parcours / historique des transferts --}}
                        @if($request->workflows->isNotEmpty())
                            <div class="mt-3">
                                <p class="text-xs text-blue-600 font-medium mb-2 uppercase tracking-wide">{{ __('messages.file_circuit') }}</p>
                                <div class="flex flex-wrap items-center gap-1">
                                    @foreach($request->workflows as $wf)
                                        @if($loop->first && $wf->fromService === null)
                                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded">{{ __('messages.submission') }}</span>
                                            <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        @endif
                                        <span class="text-xs {{ $loop->last ? 'bg-blue-600 text-white font-semibold' : 'bg-white border border-blue-200 text-blue-700' }} px-2 py-0.5 rounded">
                                            {{ $wf->toService?->nom ?? '—' }}
                                        </span>
                                        @if(!$loop->last)
                                            <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
    @endif
    {{-- ================================================================ --}}

    {{-- ====================== PROVENANCE (Direction) ====================== --}}
    @if($from === 'cart')
        @role('direction')
        @php
            $lastIncoming = $request->workflows()
                ->with(['fromService', 'user'])
                ->where('reception_status', 'accepted')
                ->whereNotNull('from_service_id')
                ->latest()
                ->first();
        @endphp
        @if($lastIncoming)
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-sm leading-relaxed">
                    <p class="font-semibold text-amber-900 text-xs uppercase tracking-wide mb-1">Transmis par</p>
                    <p class="font-bold text-amber-800">{{ $lastIncoming->fromService->nom }}</p>
                    <p class="text-amber-600 text-xs mt-1">
                        Via <span class="font-medium">{{ $lastIncoming->user?->name ?? '—' }}</span>
                        · {{ $lastIncoming->created_at->format('d/m/Y à H:i') }}
                    </p>
                    @if($lastIncoming->commentaire)
                        <p class="text-amber-700 italic text-xs mt-1 bg-amber-100 rounded px-2 py-1">"{{ $lastIncoming->commentaire }}"</p>
                    @endif
                </div>
            </div>
        @endif
        @endrole
    @endif

    {{-- ====================== PANNEAU AVIS (mode consultation) ====================== --}}
    @if($from === 'cart' && isset($pendingAffectation) && $pendingAffectation)
        <div class="bg-white border-2 border-indigo-300 rounded-2xl shadow-sm overflow-hidden">
            <div class="bg-indigo-500 px-4 py-2.5 flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-white font-bold text-sm tracking-wide">VOTRE AVIS EST REQUIS</span>
            </div>
            <div class="p-4">
                <p class="text-xs text-gray-500 mb-4">
                    Ce dossier vous a été affecté pour consultation le
                    <strong>{{ $pendingAffectation->date_affectation->format('d/m/Y à H:i') }}</strong>.
                    Soumettez votre avis — c'est la seule action disponible pour votre service sur ce dossier.
                </p>
                <form method="POST" action="{{ route('admin.affectations.repondre', $pendingAffectation->id) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Décision <span class="text-red-500">*</span></label>
                        <select name="statut" required
                                class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                            <option value="">Choisir...</option>
                            <option value="TERMINE">Favorable</option>
                            <option value="REJETE">Défavorable</option>
                            <option value="EN_COURS">En cours d'examen</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Commentaire <span class="text-gray-400 font-normal">(optionnel)</span></label>
                        <textarea name="avis" rows="4" maxlength="3000"
                                  class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 resize-none"
                                  placeholder="Détaillez votre avis, observations ou recommandations..."></textarea>
                    </div>
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-all shadow-sm hover:shadow">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Soumettre mon avis
                    </button>
                </form>
            </div>
        </div>
    @endif
    {{-- ================================================================ --}}

    {{-- ====================== PANNEAU ANNOTATION ====================== --}}
    @if($from === 'cart' && (!isset($pendingWorkflow) || !$pendingWorkflow) && (!isset($pendingAffectation) || !$pendingAffectation))
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                <span class="text-sm font-semibold text-gray-700">Annotation</span>
            </div>
            <div class="p-4">

            {{-- Annotation existante --}}
            @if($request->isAnnotated())
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-4">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-amber-800 mb-1">
                                {{ __('messages.annotation_direction') }}
                                — <span class="badge badge-sm {{ $request->categorieEnum()?->badgeClass() ?? 'badge-ghost' }}">{{ $request->categorieLabel() }}</span>
                                @if($request->isUrgent())
                                    <span class="badge badge-sm badge-error ml-1">Urgent</span>
                                @endif
                            </p>
                            <p class="text-sm text-amber-900">{{ $request->annotation }}</p>
                            <p class="text-xs text-amber-600 mt-1">
                                Par {{ $request->annotatedBy?->name ?? 'Direction' }}
                                le {{ $request->annotated_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        @role('direction')
                            @if(!isset($isClosed) || !$isClosed)
                                <button onclick="document.getElementById('annotationModal').classList.remove('hidden')"
                                        class="text-xs text-amber-700 underline hover:text-amber-900">
                                    Modifier
                                </button>
                            @endif
                        @endrole
                    </div>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-3 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <p class="text-sm text-yellow-800">{{ __('messages.not_annotated_msg') }}</p>
                </div>
            @endif

            {{-- Formulaire annotation (Direction uniquement, dossier non clôturé) --}}
            @if(!isset($isClosed) || !$isClosed)
            @role('direction')
                <div id="annotationModal"
                     class="{{ $request->isAnnotated() ? 'hidden' : '' }} bg-white border border-gray-200 rounded-lg shadow p-5 mb-4">
                    <h3 class="text-base font-semibold text-gray-800 mb-3">
                        {{ $request->isAnnotated() ? __('messages.modify_annotation') : __('messages.annotate_file') }}
                    </h3>
                    <form method="POST" action="{{ route('demande.annotate', $request->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Annotation <span class="text-red-500">*</span></label>
                            <textarea name="annotation" rows="3"
                                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Orientations, instructions pour le Secrétariat..."
                                      required>{{ old('annotation', $request->annotation) }}</textarea>
                            @error('annotation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        {{-- Catégorie auto-classifiée (lecture seule) --}}
                        <div class="mb-4 p-3 bg-gray-50 rounded border border-gray-200">
                            <p class="text-xs text-gray-500 mb-1">Catégorie (classifiée automatiquement)</p>
                            <span class="badge {{ $request->categorieEnum()?->badgeClass() ?? 'badge-ghost' }}">
                                {{ $request->categorieLabel() }}
                            </span>
                            @if($request->isUrgent())
                                <span class="badge badge-error ml-2">Urgent</span>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                                {{ __('messages.save_annotation') }}
                            </button>
                            @if($request->isAnnotated())
                                <button type="button"
                                        onclick="document.getElementById('annotationModal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded">
                                    Annuler
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            @endrole
            @endif
            </div>{{-- end p-4 --}}
        </div>{{-- end card --}}
    @endif
    {{-- ================================================================ --}}

    {{-- ====================== COMMUNICATION / COMPLÉMENTS ====================== --}}
    @php
        $messages = $messages ?? collect();
    @endphp


    {{-- Service panel: Demander un complément (cart view, non COMPLEMENT_REQUIS, hors mode consultation) --}}
    @if($from === 'cart' && (!isset($isClosed) || !$isClosed) && (!isset($pendingWorkflow) || !$pendingWorkflow) && (!isset($pendingAffectation) || !$pendingAffectation) && $request->status->code !== 'COMPLEMENT_REQUIS')
        @hasanyrole('secretariat|direction|service_liquidation|service_formalite|service_controle_placement|service_comptabilite|service_assurance|administration|admin')
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">Demander un complément</span>
                    </div>
                    <button onclick="document.getElementById('complementModal').classList.toggle('hidden')"
                            class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Ouvrir
                    </button>
                </div>
                <div class="p-4">
                    <div id="complementModal" class="hidden">
                        <form method="POST" action="{{ route('demande.complement', $request->id) }}">
                            @csrf
                            <div class="mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Message à l'usager <span class="text-red-500">*</span>
                                </label>
                                <textarea name="message" rows="4" required maxlength="3000"
                                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-orange-500 focus:border-orange-500"
                                          placeholder="Décrivez précisément les informations ou documents manquants...">{{ old('message') }}</textarea>
                                @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded">
                                    {{ __('messages.send_complement') }}
                                </button>
                                <button type="button"
                                        onclick="document.getElementById('complementModal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded">
                                    {{ __('Cancel') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endhasanyrole
    @endif

    {{-- User alert + response form (dashboard view, COMPLEMENT_REQUIS) --}}
    @if($from === 'dashboard' && $request->needsComplement())
        <div class="bg-orange-50 border-2 border-orange-300 rounded-2xl overflow-hidden">
            <div class="bg-orange-400 px-4 py-2.5 flex items-center gap-2">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span class="text-white font-bold text-sm">COMPLÉMENT REQUIS</span>
            </div>
            <div class="p-4">
                <p class="text-sm text-orange-800 mb-4">{{ __('messages.complement_info') }}</p>

                {{-- Action buttons --}}
                <div class="flex flex-wrap gap-3 mb-4">
                    @if($editRoute)
                        <a href="{{ route($editRoute, $request->id) }}"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            {{ __('messages.edit_my_request') }}
                        </a>
                    @endif
                    <button onclick="document.getElementById('responseForm').classList.toggle('hidden')"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 10h10M3 14h10m5-4v8m-9-6h10M3 10l5 5m0 0l5-5"/>
                        </svg>
                        {{ __('messages.reply') }}
                    </button>
                </div>

                {{-- Response form --}}
                <div id="responseForm" class="hidden bg-white rounded-lg border border-orange-200 p-4">
                    <form method="POST" action="{{ route('demande.repondre-complement', $request->id) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Votre réponse <span class="text-red-500">*</span>
                            </label>
                            <textarea name="message" rows="4" required maxlength="3000"
                                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Expliquez les changements apportés ou fournissez les informations demandées...">{{ old('message') }}</textarea>
                            @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Documents joints <span class="text-xs text-gray-400">(optionnel)</span>
                            </label>
                            <x-file-input name="documents[]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple
                                hint="PDF, JPG, PNG, Word — max 5 Mo par fichier" />
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded">
                                {{ __('messages.send_reply') }}
                            </button>
                            <button type="button"
                                    onclick="document.getElementById('responseForm').classList.add('hidden')"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- Message thread (visible in both views when there are messages) --}}
    @if($messages->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="w-full px-4 py-3 flex items-center justify-between border-b border-gray-100">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="text-sm font-semibold text-gray-700">{{ __('messages.messages_thread') }}</span>
                    <span class="text-xs font-normal text-gray-400 bg-gray-100 rounded-full px-2 py-0.5">{{ $messages->count() }}</span>
                </span>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition class="p-4 space-y-3">
                    @foreach($messages as $msg)
                        @php $isService = $msg->isFromService(); @endphp
                        <div class="flex {{ $isService ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-2xl w-full">
                                <div class="{{ $isService ? 'bg-orange-50 border border-orange-200' : 'bg-blue-50 border border-blue-200' }} rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-semibold {{ $isService ? 'text-orange-700' : 'text-blue-700' }}">
                                            {{ $isService ? '🏛 Service — ' . ($msg->sender?->name ?? 'Agent') : '👤 ' . ($msg->sender?->name ?? 'Usager') }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $msg->created_at->format('d/m/Y à H:i') }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-800 whitespace-pre-wrap">{{ $msg->body }}</p>
                                    @if($msg->read_at && !$isService)
                                        <p class="text-xs text-gray-400 mt-1 text-right">Lu ✓</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>{{-- end space-y-4 --}}

                {{-- Service can also reply when in COMPLEMENT_REQUIS state (follow-up) --}}
                @if($from === 'cart')
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <button onclick="document.getElementById('serviceReplyForm').classList.toggle('hidden')"
                                class="text-sm text-orange-600 hover:underline">
                            + {{ __('messages.add_followup') }}
                        </button>
                        <div id="serviceReplyForm" class="hidden mt-3">
                            <form method="POST" action="{{ route('demande.complement', $request->id) }}">
                                @csrf
                                <textarea name="message" rows="3" required maxlength="3000"
                                          class="w-full border border-gray-300 rounded px-3 py-2 text-sm mb-2"
                                          placeholder="Message de suivi..."></textarea>
                                <button type="submit"
                                        class="px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-sm rounded">
                                    {{ __('Send') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
        </div>
    @endif
    {{-- ================================================================ --}}

    {{-- ── Affectations pour avis (sidebar) ──────────────────────── --}}
    @if($from === 'cart')
        <div class="bg-white border border-indigo-200 rounded-2xl overflow-hidden">
            <div class="bg-indigo-50 px-4 py-3 flex items-center justify-between border-b border-indigo-100">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="font-semibold text-indigo-800 text-sm">Affectations pour avis</h3>
                    @if(isset($affectations) && $affectations->isNotEmpty())
                        <span class="text-xs bg-indigo-200 text-indigo-800 font-semibold px-2 py-0.5 rounded-full">{{ $affectations->count() }}</span>
                    @endif
                </div>
                @role('direction')
                    @if(!isset($isClosed) || !$isClosed)
                        <button type="button"
                                onclick="document.getElementById('affectationPanel').classList.toggle('hidden')"
                                class="text-xs font-medium text-indigo-600 hover:text-indigo-800 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Affecter
                        </button>
                    @endif
                @endrole
            </div>

            @if(!isset($isClosed) || !$isClosed)
            @role('direction')
                <div id="affectationPanel" class="hidden border-b border-indigo-100 px-4 py-4 bg-indigo-50/30">
                    <form method="POST" action="{{ route('admin.demandes.affecter', $request->id) }}">
                        @csrf
                        <p class="text-xs text-gray-500 mb-3">Sélectionnez les services à consulter simultanément :</p>
                        <div class="grid grid-cols-2 gap-2 mb-4">
                            @foreach(\App\Models\Service::all() as $svc)
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" name="service_ids[]" value="{{ $svc->id }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                                    {{ $svc->nom }}
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Affecter
                            </button>
                        </div>
                    </form>
                </div>
            @endrole
            @endif

            @if(isset($affectations) && $affectations->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($affectations as $aff)
                        @php
                            $statusMap = [
                                'EN_ATTENTE' => ['label' => 'En attente',   'class' => 'bg-yellow-100 text-yellow-800'],
                                'EN_COURS'   => ['label' => 'En cours',     'class' => 'bg-blue-100 text-blue-800'],
                                'TERMINE'    => ['label' => 'Favorable',    'class' => 'bg-green-100 text-green-800'],
                                'REJETE'     => ['label' => 'Défavorable',  'class' => 'bg-red-100 text-red-800'],
                            ];
                            $st = $statusMap[$aff->statut] ?? ['label' => $aff->statut, 'class' => 'bg-gray-100 text-gray-700'];
                            $isMyService = auth()->user()->service_id === $aff->service_id;
                            $canRespond  = $isMyService && !in_array($aff->statut, ['TERMINE', 'REJETE']);
                        @endphp
                        <div class="px-4 py-3 flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-medium text-sm text-gray-800">{{ $aff->service->nom }}</p>
                                    @if($isMyService)
                                        <span class="text-xs bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded font-medium">Votre service</span>
                                    @endif
                                </div>
                                @if($aff->avis)
                                    <p class="text-xs text-gray-500 mt-1 italic">"{{ $aff->avis }}"</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $aff->date_affectation->format('d/m/Y') }}
                                    @if($aff->date_reponse)
                                        · répondu {{ $aff->date_reponse->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex-shrink-0 flex flex-col items-end gap-1.5">
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $st['class'] }}">{{ $st['label'] }}</span>
                                @if($canRespond && !auth()->user()->hasRole('direction'))
                                    <button type="button"
                                            onclick="document.getElementById('avisModal{{ $aff->id }}').classList.remove('hidden')"
                                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium hover:underline">
                                        Mettre à jour
                                    </button>
                                    <div id="avisModal{{ $aff->id }}"
                                         class="hidden fixed inset-0 z-[99999] flex items-center justify-center bg-black/50">
                                        <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
                                            <h3 class="font-semibold text-gray-800 mb-4">Avis — {{ $aff->service->nom }}</h3>
                                            <form method="POST" action="{{ route('admin.affectations.repondre', $aff->id) }}">
                                                @csrf
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Décision <span class="text-red-500">*</span></label>
                                                    <select name="statut" required class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300">
                                                        <option value="EN_COURS" {{ $aff->statut === 'EN_COURS' ? 'selected' : '' }}>En cours d'examen</option>
                                                        <option value="TERMINE">Favorable</option>
                                                        <option value="REJETE">Défavorable</option>
                                                    </select>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Commentaire</label>
                                                    <textarea name="avis" rows="3"
                                                              class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-300 resize-none"
                                                              placeholder="Observations, recommandations...">{{ $aff->avis }}</textarea>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button"
                                                            onclick="document.getElementById('avisModal{{ $aff->id }}').classList.add('hidden')"
                                                            class="px-4 py-2 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
                                                        Annuler
                                                    </button>
                                                    <button type="submit"
                                                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-xl">
                                                        Enregistrer
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="px-4 py-4 text-sm text-gray-400 italic text-center">Aucune affectation pour ce dossier.</p>
            @endif
        </div>
    @endif

    {{-- ── Rouvrir (sidebar, Direction, dossier clôturé) ─────────── --}}
    @if($from === 'cart' && isset($isClosed) && $isClosed)
        @role('direction')
            <div class="bg-white rounded-2xl border-2 border-gray-300 shadow-sm overflow-hidden" x-data="{ open: false }">
                <div class="bg-gray-100 px-4 py-2.5 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="text-gray-700 font-bold text-sm tracking-wide">DOSSIER CLÔTURÉ</span>
                </div>
                <div class="p-4">
                    <p class="text-sm text-gray-500 mb-3">Ce dossier a reçu une décision finale. Vous pouvez le réouvrir si nécessaire.</p>
                    <button type="button" @click="open = !open"
                            class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium rounded-xl flex items-center justify-center gap-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réouvrir le dossier
                    </button>
                    <div x-show="open" x-transition class="mt-3">
                        <form method="POST" action="{{ route('admin.demandes.rouvrir', $request->id) }}"
                              onsubmit="return confirm('Réouvrir ce dossier et annuler la décision finale ?')">
                            @csrf
                            <textarea name="motif" rows="2" placeholder="Motif de réouverture (optionnel)"
                                      class="w-full border border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-gray-300 resize-none mb-2"></textarea>
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition-colors">
                                Confirmer la réouverture
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endrole
    @endif

    {{-- ── Décision finale Direction (sidebar) ────────────────────── --}}
    @if($from === 'cart')
        @role('direction')
            @php
                $directionServiceId = \App\Models\Service::where('code', \App\Models\Service::DIRECTION)->value('id');
                $isAtDirection = $request->current_service_id === $directionServiceId;
                $isClosed = in_array($request->status->code, ['APPROUVEE', 'FINALISEE', 'REJETEE', 'ANNULEE']);
                $canFinalize = $request->workflows()
                    ->where('reception_status', 'accepted')
                    ->whereNotNull('from_service_id')
                    ->exists();
            @endphp
            @if($isAtDirection && !$isClosed)
                <div class="bg-white rounded-2xl border-2 border-blue-300 shadow-sm overflow-hidden">
                    <div class="bg-blue-600 px-4 py-2.5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-white font-bold text-sm tracking-wide">DÉCISION FINALE</span>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-500 mb-3">Le dossier est revenu à la Direction pour décision finale.</p>

                        @if(!$canFinalize)
                            <div class="flex items-start gap-2 bg-yellow-50 border border-yellow-200 rounded-xl px-3 py-2 mb-3">
                                <svg class="w-4 h-4 text-yellow-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <p class="text-xs text-yellow-800">Ce dossier doit d'abord être traité et acheminé par un autre service.</p>
                            </div>
                        @endif

                        <div class="flex flex-col gap-2">
                            <form method="POST" action="{{ route('admin.demandes.approuver', $request->id) }}"
                                  onsubmit="return confirm('Approuver définitivement ce dossier ?')">
                                @csrf
                                <button type="submit" @disabled(!$canFinalize)
                                        class="w-full {{ $canFinalize ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }} px-4 py-2 text-white text-sm font-medium rounded-xl flex items-center justify-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Approuver
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.demandes.cloturer', $request->id) }}"
                                  onsubmit="return confirm('Clôturer définitivement ce dossier ?')">
                                @csrf
                                <button type="submit" @disabled(!$canFinalize)
                                        class="w-full {{ $canFinalize ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-200 text-gray-400 cursor-not-allowed' }} px-4 py-2 text-white text-sm font-medium rounded-xl flex items-center justify-center gap-2 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Clôturer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endrole
    @endif

            </div>{{-- end sidebar --}}

            {{-- ─── COLONNE GAUCHE (visuellement) : données du dossier ─── --}}
            <div class="lg:col-span-4 lg:order-1 min-w-0">

    @switch($request->type)
        {{-- Pensionnaire --}}
        @case('DEMANDE_VIREMENT_BANCAIRE')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- ========================= -->
                            <!-- Status Banner -->
                            <!-- ========================= -->
                            {{-- Status shown in page header --}}

                            <!-- ========================= -->
                            <!-- Main Grid -->
                            <!-- ========================= -->
                            <div class="space-y-5">

                                <!-- ========================= -->
                                <!-- Left Column -->
                                <!-- ========================= -->
                                <div class="space-y-5">

                                    <!-- État civil -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">État civil</h3>

                                        <dl class="space-y-3">

                                            <div class="flex items-center">
                                                @if(!empty(($request->data['profile_photo'] ?? '')))
                                                    <img
                                                        src="{{ asset('storage/' . ($request->data['profile_photo'] ?? '')) }}"
                                                        class="w-20 h-20 rounded-full object-cover mr-4"
                                                        alt="Photo de profil">
                                                @endif

                                                <div>
                                                    <dt class="text-sm text-gray-500">Nom complet</dt>
                                                    <dd class="font-medium">
                                                        {{ ($request->data['nom_complet'] ?? '') ?? '-' }}
                                                    </dd>
                                                </div>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nif'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de naissance</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse(($request->data['date_naissance'] ?? ''))->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">État civil</dt>
                                                <dd class="font-medium">
                                                    {{ ucfirst($request->civilStatus('statut_civil_id')->name) }}
                                                </dd>
                                            </div>

                                            <div>

                                                <dt class="text-sm text-gray-500">Genre</dt>
                                                <dd class="font-medium">
                                                    {{ optional($request->gender(($request->data['sexe_id'] ?? '')))->name ?? '—' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de la mère</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_mere'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                        </dl>
                                    </div>

                                    <!-- Coordonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées</h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['adresse'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Ville</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['ville'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['telephone'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- ========================= -->
                                <!-- Right Column -->
                                <!-- ========================= -->
                                <div class="space-y-5">

                                    <!-- Allocation -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant de l'allocation</dt>
                                            <dd class="text-2xl font-bold text-blue-600">
                                                {{ number_format(($request->data['montant_allocation'] ?? ''), 2, ',', ' ') }} HTG
                                            </dd>
                                        </div>

                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Type de pension</dt>
                                            <dd class="font-medium">
                                                {{ ucfirst($request->pensionType('type_pension_id')->name) }}
                                            </dd>
                                        </div>
                                    </div>

                                    <!-- Détails pension -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Détails de pension</h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Catégorie de pension</dt>
                                                <dd class="font-medium">
                                                    {{ ucfirst($request->pensionCategory('categorie_pension_id')->name) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['code_pension'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Coordonnées bancaires -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées bancaires</h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de la banque</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_banque'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro de compte</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['numero_compte'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div class="md:col-span-2">
                                                <dt class="text-sm text-gray-500">Titulaire du compte</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_compte'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Métadonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Métadonnées</h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de création</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('DEMANDE_ATTESTATION')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- Status Banner -->
                            {{-- Status shown in page header --}}

                            <!-- Main Content -->
                            <div class="space-y-5">

                                <!-- Left Column -->
                                <div class="space-y-5">
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du pensionnaire
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['prenom'] ?? '') }}
                                                    {{ ($request->data['nom'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nif'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['code_pension'] ?? '') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="space-y-5">

                                    <!-- Request Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('DEMANDE_TRANSFERT_CHEQUE')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- Status Banner -->
                            {{-- Status shown in page header --}}

                            <div class="space-y-5">

                                <!-- COLONNE GAUCHE -->
                                <div class="space-y-5">

                                    <!-- Identité -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identité</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom'] ?? '') }} {{ ($request->data['prenom'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de jeune fille</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_jeune_fille'] ?? '') ?? '—' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Identifiants -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Identifiants officiels
                                        </h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ ($request->data['nif'] ?? '') }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">{{ ($request->data['ninu'] ?? '') }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">{{ ($request->data['code_pension'] ?? '') }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Coordonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Coordonnées
                                        </h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">{{ ($request->data['adresse'] ?? '') }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ ($request->data['telephone'] ?? '') }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Email</dt>
                                                <dd class="font-medium">{{ ($request->data['email'] ?? '') }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                <!-- COLONNE DROITE -->
                                <div class="space-y-5">

                                    <!-- Résumé -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant du transfert</dt>
                                            <dd class="text-2xl font-bold text-blue-600">
                                                {{ number_format(($request->data['montant'] ?? ''), 0, ',', ' ') }} HTG
                                            </dd>
                                        </div>

                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Date de la demande</dt>
                                            <dd class="font-medium">
                                                {{ \Carbon\Carbon::parse(($request->data['date_demande'] ?? ''))->format('d/m/Y') }}
                                            </dd>
                                        </div>
                                    </div>

                                    <!-- Calendrier fiscal -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Calendrier fiscal
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Année fiscale</dt>
                                                <dd class="font-medium">{{ ($request->data['annee_fiscale'] ?? '') }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Mois de début</dt>
                                                <dd class="font-medium">{{ ($request->data['mois_debut'] ?? '') }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Catégorie de pension</dt>
                                                <dd class="font-medium">
                                                    {{ $request->pensionCategory('categorie_pension_id')->name }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Période -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Période du transfert
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Du</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse(($request->data['de'] ?? ''))->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Au</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse(($request->data['a'] ?? ''))->format('d/m/Y') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Contexte -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Contexte du transfert
                                        </h3>
                                        <p class="text-gray-700 leading-relaxed">
                                            {{ ($request->data['raison_transfert'] ?? '') }}
                                        </p>
                                    </div>

                                    <!-- Métadonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('DEMANDE_ARRET_PAIEMENT')
            <div>
                <div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            {{-- ================= STATUS BANNER ================= --}}
                            {{-- Status shown in page header --}}

                            {{-- ================= MAIN GRID ================= --}}
                            <div class="space-y-5">

                                {{-- ================= LEFT COLUMN ================= --}}
                                <div class="space-y-5">

                                    {{-- Pensionnaire --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du pensionnaire
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['prenom'] ?? '') }} {{ ($request->data['nom'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de jeune fille</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_jeune_fille'] ?? '') ?? '—' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['code_pension'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Régime de pension</dt>
                                                <dd class="font-medium capitalize">
                                                    {{ ($request->data['regime_pension'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nif'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['ninu'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['telephone'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Email</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['email'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['adresse'] ?? '') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                {{-- ================= RIGHT COLUMN ================= --}}
                                <div class="space-y-5">

                                    {{-- Détails de la demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Exercice</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['exercice'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Mois de début</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse(($request->data['mois_debut'] ?? ''))->format('m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Période demandée</dt>
                                                <dd class="font-medium">
                                                    Du {{ \Carbon\Carbon::parse(($request->data['periode_debut'] ?? ''))->format('d/m/Y') }}
                                                    au {{ \Carbon\Carbon::parse(($request->data['periode_fin'] ?? ''))->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Montant</dt>
                                                <dd class="font-medium">
                                                    {{ number_format(($request->data['montant'] ?? ''), 0, ',', ' ') }} GDES
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de la demande</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->date_demande)->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                        </dl>
                                    </div>

                                    {{-- Pièces jointes --}}
                                    @if(!empty(($request->data['pieces'] ?? '')))
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Pièces justificatives
                                        </h3>

                                        <ul class="list-disc list-inside space-y-2">
                                            @foreach(($request->data['pieces'] ?? '') as $piece)
                                                <li>
                                                    <a href="{{ Storage::url($piece) }}"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline">
                                                        {{ basename($piece) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    {{-- Métadonnées --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            @break
        @case('DEMANDE_REINSERTION')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- Status Banner -->
                            {{-- Status shown in page header --}}

                            <!-- Main Grid -->
                            <div class="space-y-5">

                                <!-- LEFT COLUMN -->
                                <div class="space-y-5">

                                    <!-- Identity -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Identité
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Prénom</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['prenom'] ?? '') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom'] ?? '') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="space-y-5">

                                    <!-- Request Info -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Reason -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Motif de la réinsertion
                                        </h3>

                                        <p class="text-gray-700 leading-relaxed">
                                            {{ ($request->data['raison'] ?? '') }}
                                        </p>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('DEMANDE_ARRET_VIREMENT')
            <div>
                <div>

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            {{-- ===================== --}}
                            {{-- BANNIÈRE DE STATUT --}}
                            {{-- ===================== --}}
                            {{-- Status shown in page header --}}

                            {{-- ===================== --}}
                            {{-- GRILLE PRINCIPALE --}}
                            {{-- ===================== --}}
                            <div class="space-y-5">

                                {{-- ===================== --}}
                                {{-- COLONNE GAUCHE --}}
                                {{-- ===================== --}}
                                <div class="space-y-5">

                                    {{-- Type de demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                    {{-- Métadonnées --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                {{-- ===================== --}}
                                {{-- COLONNE DROITE --}}
                                {{-- ===================== --}}
                                <div class="space-y-5">

                                    {{-- Code --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de la demande</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['date'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Informations du demandeur --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du demandeur
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom</dt>
                                                <dd class="font-medium">{{ ($request->data['nom'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Prénom</dt>
                                                <dd class="font-medium">{{ ($request->data['prenom'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ ($request->data['telephone'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Courriel</dt>
                                                <dd class="font-medium">{{ ($request->data['courriel'] ?? '') ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Détails arrêt de virement --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Arrêt de virement
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Mois non reçu</dt>
                                                <dd class="font-medium">{{ ($request->data['mois_non_recu'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nouveau numéro</dt>
                                                <dd class="font-medium">{{ ($request->data['nouveau_numero'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom du compte</dt>
                                                <dd class="font-medium">{{ ($request->data['nom_du_compte'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Chèques</dt>
                                                <dd class="font-medium">{{ ($request->data['cheques'] ?? '') ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Informations complémentaires --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations complémentaires
                                        </h3>

                                        <p class="text-gray-700">
                                            {{ ($request->data['informations'] ?? '') ?? 'Aucune information fournie' }}
                                        </p>
                                    </div>

                                    {{-- Motifs --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Motifs
                                        </h3>

                                        @if(!empty(($request->data['motifs'] ?? '')))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach(($request->data['motifs'] ?? '') as $motif)
                                                    <li class="font-medium">{{ $motif }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-500">Aucun motif renseigné</p>
                                        @endif
                                        </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            @break
        @case('DEMANDE_PREUVE_EXISTENCE')
            <div class="py-6">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white shadow rounded-lg p-6 space-y-8">

                        {{-- ================= STATUT ================= --}}
                        <div class="p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->code) }}">
                            <div class="flex justify-between items-center">
                                <div>
                                    <strong>Statut :</strong> {{ $request->status->code }}
                                </div>
                                <div class="text-sm">
                                    Mis à jour le {{ $request->updated_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>

                        {{-- ================= MÉTADONNÉES RACINE ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Informations système</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <dt class="text-sm text-gray-500">Code</dt>
                                    <dd class="font-medium">{{ $request->code }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-500">Type</dt>
                                    <dd class="font-medium">{{ $request->type }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-500">Créée le</dt>
                                    <dd class="font-medium">{{ $request->created_at->format('d/m/Y H:i') }}</dd>
                                </div>

                                <div>
                                    <dt class="text-sm text-gray-500">Créée par (ID)</dt>
                                    <dd class="font-medium">{{ $request->user->name }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- ================= IDENTITÉ ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Identité du pensionné</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div><dt class="text-sm text-gray-500">Numéro d’identité</dt><dd class="font-medium">{{ ($request->data['numero_identite'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Nom</dt><dd class="font-medium">{{ ($request->data['nom'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Prénom</dt><dd class="font-medium">{{ ($request->data['prenom'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Date de naissance</dt><dd class="font-medium">{{ ($request->data['date_naissance'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Sexe (ID)</dt><dd class="font-medium">{{ optional($request->gender(($request->data['sexe_id'] ?? '')))->name ?? '—' }}</dd></div>
                                <div><dt class="text-sm text-gray-500">État civil (ID)</dt><dd class="font-medium">{{ $request->civilStatus('etat_civil_id')->name}}</dd></div>
                            </dl>
                        </div>

                        {{-- ================= COORDONNÉES ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Coordonnées</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><dt class="text-sm text-gray-500">Adresse</dt><dd class="font-medium">{{ ($request->data['adresse'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Adresse postale</dt><dd class="font-medium">{{ ($request->data['adresse_postale'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Localisation</dt><dd class="font-medium">{{ ($request->data['localisation'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Téléphone</dt><dd class="font-medium">{{ ($request->data['telephone'] ?? '') }}</dd></div>
                            </dl>
                        </div>

                        {{-- ================= DONNÉES FISCALES ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Données fiscales</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div><dt class="text-sm text-gray-500">Année fiscale</dt><dd class="font-medium">{{ ($request->data['annee_fiscale'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">NIF</dt><dd class="font-medium">{{ ($request->data['nif'] ?? '') }}</dd></div>
                            </dl>
                        </div>

                        {{-- ================= PENSION ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Pension</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div><dt class="text-sm text-gray-500">Catégorie (ID)</dt><dd class="font-medium">{{ $request->pensionCategory('categorie_pension_id')->name }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Montant</dt><dd class="font-medium">{{ number_format(($request->data['montant_pension'] ?? ''), 0, ',', ' ') }} HTG</dd></div>
                                <div><dt class="text-sm text-gray-500">Début</dt><dd class="font-medium">{{ ($request->data['debut_pension'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Fin</dt><dd class="font-medium">{{ ($request->data['fin_pension'] ?? '') }}</dd></div>
                            </dl>
                        </div>

                        {{-- ================= MONITEUR ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Moniteur</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div><dt class="text-sm text-gray-500">Numéro</dt><dd class="font-medium">{{ ($request->data['no_moniteur'] ?? '') }}</dd></div>
                                <div><dt class="text-sm text-gray-500">Date</dt><dd class="font-medium">{{ ($request->data['date_moniteur'] ?? '') }}</dd></div>
                            </dl>
                        </div>

                        {{-- ================= DÉPENDANTS ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Dépendants</h3>

                            <table class="w-full border text-sm">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="border p-2">Nom</th>
                                        <th class="border p-2">Relation</th>
                                        <th class="border p-2">Date de naissance</th>
                                        <th class="border p-2">Sexe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($request->data['dependants'] ?? '') as $dep)
                                        <tr>
                                            <td class="border p-2">{{ $dep['nom'] }}</td>
                                            <td class="border p-2">{{ $dep['relation'] }}</td>
                                            <td class="border p-2">{{ $dep['date_naissance'] }}</td>
                                            <td class="border p-2">
                                                {{ optional($request->gender($dep['sexe_id']))->name ?? '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- ================= DOCUMENT ================= --}}
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Photo</h3>

                            <img
                                src="{{ asset('storage/' . ($request->data['documents'] ?? '')['profile_photo']) }}"
                                class="w-48 rounded border"
                                alt="Photo de profil"
                            >
                        </div>

                        {{-- ================= DONNÉES TECHNIQUES DANS DATA ================= --}}
                        <div class="bg-gray-100 rounded-lg p-4">
                            <h3 class="text-lg font-semibold mb-4 text-gray-700">Données techniques (data)</h3>

                            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                <div><dt class="text-gray-500">Code</dt><dd>{{ $request->code }}</dd></div>
                                <div><dt class="text-gray-500">Type</dt><dd>{{ $request->type }}</dd></div>
                                <div><dt class="text-gray-500">Created by</dt><dd>{{ $request->user->name }}</dd></div>
                            </dl>
                        </div>

                    </div>
                </div>
            </div>
            @break
        {{-- Fonctionnaire --}}
        @case('DEMANDE_ETAT_CARRIERE')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            {{-- ================= STATUS BANNER ================= --}}
                            {{-- Status shown in page header --}}

                            {{-- ================= MAIN GRID ================= --}}
                            <div class="space-y-5">

                                {{-- ========== LEFT COLUMN ========== --}}
                                <div class="space-y-5">

                                    {{-- Type de demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                </div>

                                {{-- ========== RIGHT COLUMN ========== --}}
                                <div class="space-y-5">

                                    {{-- Détails de la demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Métadonnées --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- ================= INFORMATIONS PERSONNELLES ================= --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations personnelles
                                        </h3>

                                        @php
                                            $data = $request->data ?? [];
                                        @endphp

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach([
                                                'nom' => 'Nom',
                                                'prenom' => 'Prénom',
                                                'nom_jeune_fille' => 'Nom de jeune fille',
                                                'date_naissance' => 'Date de naissance',
                                                'lieu_naissance' => 'Lieu de naissance',
                                                'etat_civil' => 'État civil',
                                                'nif_ninu' => 'NIF / NINU',
                                                'cin' => 'CIN',
                                                'statut' => 'Statut',
                                                'employeur' => 'Employeur',
                                                'fonction' => 'Fonction',
                                                'date_debut_service' => 'Début de service',
                                                'date_fin_service' => 'Fin de service',
                                                'numero_dossier' => 'Numéro de dossier',
                                                'adresse' => 'Adresse',
                                                'telephone' => 'Téléphone',
                                                'email' => 'Email',
                                                'raison' => 'Raison de la demande',
                                            ] as $key => $label)
                                                @if(!empty($data[$key]))
                                                    <div>
                                                        <dt class="text-sm text-gray-500">{{ $label }}</dt>
                                                        <dd class="font-medium">
                                                            {{ in_array($key, ['date_naissance','date_debut_service','date_fin_service'])
                                                                ? \Carbon\Carbon::parse($data[$key])->format('d/m/Y')
                                                                : $data[$key]
                                                            }}
                                                        </dd>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </dl>
                                    </div>

                                    {{-- ================= DOCUMENTS ================= --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Documents fournis
                                        </h3>

                                        @php
                                            $documents = ($request->data['documents'] ?? '') ?? [];
                                        @endphp

                                        <div class="space-y-4">
                                            @foreach($documents as $label => $files)
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                                                        {{ ucwords(str_replace('_', ' ', $label)) }}
                                                    </h4>

                                                    <ul class="space-y-1">
                                                        @foreach((array) $files as $file)
                                                            <li>
                                                                <a href="{{ Storage::url($file) }}"
                                                                target="_blank"
                                                                class="text-blue-600 hover:underline text-sm">
                                                                    📄 {{ basename($file) }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break

        {{-- Institution --}}
        @case('DEMANDE_ADHESION')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- ========================= -->
                            <!-- STATUS BANNER -->
                            <!-- ========================= -->
                            {{-- Status shown in page header --}}

                            <!-- ========================= -->
                            <!-- MAIN GRID -->
                            <!-- ========================= -->
                            <div class="space-y-5">

                                <!-- ========================= -->
                                <!-- LEFT COLUMN -->
                                <!-- ========================= -->
                                <div class="space-y-5">

                                    <!-- TYPE -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                    <!-- METADATA -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- PHOTO -->
                                    @if(!empty(($request->data['profile_picture'] ?? '')))
                                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Photo de profil
                                        </h3>

                                        <img
                                            src="{{ Storage::url(($request->data['profile_picture'] ?? '')) }}"
                                            class="mx-auto w-32 h-32 rounded-full object-cover border"
                                            alt="Photo de profil"
                                        >
                                    </div>
                                    @endif

                                </div>

                                <!-- ========================= -->
                                <!-- RIGHT COLUMN -->
                                <!-- ========================= -->
                                <div class="space-y-5">

                                    <!-- INFORMATIONS PERSONNELLES -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations personnelles
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Institution</dt>
                                                <dd class="font-medium">{{ ($request->data['institution'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['firstname'] ?? '') ?? '' }}
                                                    {{ ($request->data['lastname'] ?? '') ?? '' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Lieu de naissance</dt>
                                                <dd class="font-medium">{{ ($request->data['birth_place'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de naissance</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse(($request->data['birth_date'] ?? ''))->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ ($request->data['nif'] ?? '') ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">{{ ($request->data['ninu'] ?? '') ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- SITUATION FAMILIALE -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Situation familiale
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Mère</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['mother_firstname'] ?? '') ?? '' }}
                                                    {{ ($request->data['mother_lastname'] ?? '') ?? '' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Conjoint(e)</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['spouse_firstname'] ?? '') ?? '-' }}
                                                    {{ ($request->data['spouse_lastname'] ?? '') ?? '' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- INFOS PROFESSIONNELLES -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations professionnelles
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Date d’entrée</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse(($request->data['entry_date'] ?? ''))->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Salaire actuel</dt>
                                                <dd class="font-medium">
                                                    {{ number_format(($request->data['current_salary'] ?? ''), 0, ',', ' ') }} HTG
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- PERSONNES À CHARGE -->
                                    @if(!empty(($request->data['dependents'] ?? '')))
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Personnes à charge
                                        </h3>

                                        <div class="space-y-3">
                                            @foreach(($request->data['dependents'] ?? '') as $dependent)
                                                <div class="p-3 bg-white rounded border">
                                                    <p class="font-medium">
                                                        {{ $dependent['firstname'] }} {{ $dependent['lastname'] }}
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ ucfirst($dependent['relation']) }} —
                                                        {{ \Carbon\Carbon::parse($dependent['birthdate'])->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- EXPÉRIENCES -->
                                    @if(!empty(($request->data['previous_jobs'] ?? '')))
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Expériences professionnelles
                                        </h3>

                                        <div class="space-y-3">
                                            @foreach(($request->data['previous_jobs'] ?? '') as $job)
                                                <div class="p-3 bg-white rounded border">
                                                    <p class="font-medium">{{ $job['institution'] }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        Du {{ \Carbon\Carbon::parse($job['start_date'])->format('d/m/Y') }}
                                                        au {{ \Carbon\Carbon::parse($job['end_date'])->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('DEMANDE_PENSION')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- Status Banner -->
                            {{-- Status shown in page header --}}

                            <!-- Main Grid -->
                            <div class="space-y-5">

                                <!-- LEFT COLUMN -->
                                <div class="space-y-5">
                                    <!-- Type -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="space-y-5">

                                    <!-- Request Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Titre de la demande</dt>
                                                <dd class="font-medium">{{ $request->title }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Documents -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-700">
                                            Pièces jointes
                                        </h3>

                                        <div class="space-y-4">

                                            @php
                                                $documents = ($request->data['documents'] ?? '') ?? [];
                                            @endphp

                                            @foreach($documents as $label => $files)
                                                @php
                                                    $labels = [
                                                        'career_certificates'   => 'Certificat de carrière',
                                                        'birth_certificates'    => 'Acte de naissance',
                                                        'marriage_certificates' => 'Acte de mariage',
                                                        'divorce_certificate'   => 'Jugement de divorce',
                                                        'medical_certificate'   => 'Certificat médical',
                                                        'tax_id_numbers' => 'matricule fiscal et carte d’identification nationale',
                                                        'check_stub' => 'Souche de chèque ou preuve de paiement',
                                                        'monitor_copy' => 'Copie du Moniteur',
                                                        'photos' => 'photos'
                                                    ];

                                                    $displayLabel = $labels[$label]
                                                        ?? ucwords(str_replace('_', ' ', $label));
                                                @endphp

                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                                                        {{ ucwords(str_replace('_', ' ', $displayLabel)) }}
                                                    </h4>

                                                    <div class="space-y-2">
                                                        @foreach((array) $files as $file)
                                                            <a href="{{ asset('storage/' . $file) }}"
                                                            target="_blank"
                                                            class="flex items-center justify-between bg-white border border-gray-200 rounded-md px-4 py-2 text-sm hover:bg-gray-50">
                                                                <span class="truncate">
                                                                    {{ basename($file) }}
                                                                </span>
                                                                <span class="text-blue-600 font-medium">
                                                                    Voir
                                                                </span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(empty($documents))
                                                <p class="text-gray-500 text-sm">
                                                    Aucun document joint
                                                </p>
                                            @endif

                                        </div>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('DEMANDE_PENSION_REVERSION')
            <div>
                <div>
                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mb-4">
                        <div class="p-6">

                            <!-- ===================== -->
                            <!-- STATUS BANNER -->
                            <!-- ===================== -->
                            {{-- Status shown in page header --}}

                            <!-- ===================== -->
                            <!-- MAIN GRID -->
                            <!-- ===================== -->
                            <div class="space-y-5">

                                <!-- ===================== -->
                                <!-- LEFT COLUMN -->
                                <!-- ===================== -->
                                <div class="space-y-5">

                                    <!-- TYPE -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                    <!-- METADATA -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="space-y-2">                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                <!-- ===================== -->
                                <!-- RIGHT COLUMN -->
                                <!-- ===================== -->
                                <div class="space-y-5">

                                    <!-- REQUEST DETAILS -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- ===================== -->
                                    <!-- DOSSIER INFORMATION -->
                                    <!-- ===================== -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du dossier
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet du défunt</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_complet_defunt'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro de pension</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['numero_pension'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom du bénéficiaire</dt>
                                                <dd class="font-medium">
                                                    {{ ($request->data['nom_beneficiaire'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Lien avec le défunt</dt>
                                                <dd class="font-medium capitalize">
                                                    {{ ($request->data['relation_defunt'] ?? '') ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- ===================== -->
                                    <!-- DOCUMENTS -->
                                    <!-- ===================== -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Documents fournis
                                        </h3>

                                        @php
                                            $documentLabels = [
                                                'acte_deces' => 'Acte de décès',
                                                'photos_identite' => 'Photos d’identité',
                                                'attestation_scolaires' => 'Attestations scolaires',
                                                'certificat_carriere' => 'Certificat de carrière',
                                                'certificat_non_dissolution' => 'Certificat de non dissolution',
                                                'carte_pension' => 'Carte de pension',
                                                'souche_cheque' => 'Souche de chèque',
                                                'extrait_acte_mariage' => 'Extrait acte de mariage',
                                                'extrait_acte_naissance' => 'Extrait acte de naissance',
                                                'matricule_fiscal' => 'Matricule fiscal',
                                                'carte_electorale' => 'Carte électorale',
                                                'pv_tutelle' => 'PV de tutelle',
                                                'certificat_medical' => 'Certificat médical',
                                                'copie_moniteur' => 'Copie du moniteur',
                                            ];
                                        @endphp

                                        <div class="space-y-4">
                                            @forelse((($request->data['documents'] ?? '') ?? []) as $key => $files)
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                                                        {{ $documentLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                                    </h4>

                                                    <ul class="space-y-1">
                                                        @foreach((array) $files as $file)
                                                            <li class="flex items-center justify-between text-sm">
                                                                <span class="truncate">
                                                                    {{ basename($file) }}
                                                                </span>

                                                                <a
                                                                    href="{{ \Illuminate\Support\Facades\Storage::url($file) }}"
                                                                    target="_blank"
                                                                    class="text-blue-600 hover:underline"
                                                                >
                                                                    Voir
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500">
                                                    Aucun document joint.
                                                </p>
                                            @endforelse
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @default
    @endswitch

    {{-- ====================== DOCUMENTS SUPPLÉMENTAIRES ====================== --}}
    @php
        $supplementalDocs = $request->documents()->where('type', 'supplemental')->get();
        $canAddDocs = auth()->id() === $request->created_by && $request->canBeEditedByUser();
    @endphp

    @if($supplementalDocs->isNotEmpty() || $canAddDocs)
        <div class="max-w-7xl mx-auto pb-5 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg px-6 py-5">

                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">Documents supplémentaires</h3>
                    @if($canAddDocs)
                        <button
                            x-data
                            @click="$dispatch('toggle-supp-upload')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Ajouter un document
                        </button>
                    @endif
                </div>

                {{-- Upload form (collapsible) --}}
                @if($canAddDocs)
                    <div
                        x-data="{ open: false }"
                        @toggle-supp-upload.window="open = !open"
                        x-show="open"
                        x-transition
                        class="mb-5"
                    >
                        @if(session('success') && str_contains(session('success'), 'ajouté'))
                            <div class="mb-3 p-3 bg-green-50 border border-green-200 text-green-700 rounded text-sm">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form
                            method="POST"
                            action="{{ route('demandedocument.store', $request->id) }}"
                            enctype="multipart/form-data"
                            class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3"
                        >
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Description / label <span class="text-gray-400 font-normal">(optionnel)</span>
                                </label>
                                <input
                                    type="text"
                                    name="label"
                                    value="{{ old('label') }}"
                                    placeholder="ex : Pièce d'identité, Justificatif…"
                                    class="block w-full rounded border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Fichier(s) <span class="text-gray-500 font-normal">(PDF, JPG, PNG — max 5 Mo chacun)</span>
                                </label>
                                <input
                                    type="file"
                                    name="files[]"
                                    multiple
                                    accept=".pdf,.jpg,.jpeg,.png,.webp"
                                    class="block w-full text-sm text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                                >
                                @error('files')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                @error('files.*')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end gap-2">
                                <button
                                    type="button"
                                    @click="open = false"
                                    class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-100"
                                >
                                    Annuler
                                </button>
                                <button
                                    type="submit"
                                    class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded"
                                >
                                    Envoyer
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                {{-- Existing supplemental documents --}}
                @if($supplementalDocs->isNotEmpty())
                    <div class="space-y-2">
                        @foreach($supplementalDocs->groupBy('label') as $label => $docs)
                            <div>
                                @if($label)
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ $label }}</p>
                                @endif
                                @foreach($docs as $doc)
                                    <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded px-3 py-2 text-sm">
                                        <a href="{{ $doc->url() }}" target="_blank" class="flex items-center gap-2 text-blue-700 hover:underline truncate">
                                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="truncate">{{ $doc->original_name }}</span>
                                        </a>
                                        <div class="flex items-center gap-3 ml-3 flex-shrink-0">
                                            <span class="text-gray-400 text-xs">{{ $doc->sizeInKo() }} Ko</span>
                                            @if($canAddDocs)
                                                <form method="POST" action="{{ route('demandedocument.destroy', $doc->id) }}"
                                                      onsubmit="return confirm('Supprimer ce document ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Supprimer</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500">Aucun document supplémentaire ajouté pour le moment.</p>
                @endif

            </div>
        </div>
    @endif

    <!-- Request History -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mt-4 px-6" x-data="{ open: false }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between py-4">
            <h3 class="text-base font-semibold text-gray-800">{{ __('messages.history_label') }}</h3>
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-transition>
            <div class="rounded-xl border border-gray-100 mb-4">
                @forelse ($requestHistories as $history)
                    <div class="p-4 border-b border-gray-100 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $history->statut }}</span>
                                    <span class="text-xs text-gray-400">{{ $history->created_at->format('d/m/Y à H:i') }}</span>
                                </div>
                                @if($history->commentaire)
                                    <p class="text-sm text-gray-600 italic">{{ $history->commentaire }}</p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-xs text-gray-500">
                                    @if ($history->creator()) Par {{ $history->creator()->name }} @else Système @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-400 text-sm">{{ __('messages.no_history') }}</div>
                @endforelse
            </div>
            <div class="pb-4">{{ $requestHistories->links() }}</div>
        </div>
    </div>

    {{-- ====================== JOURNAL D'ACTIVITÉ ====================== --}}
    @if($from === 'cart' && isset($activityLogs) && $activityLogs->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm mt-4 px-6" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between py-4">
                <h3 class="text-base font-semibold text-gray-800">{{ __('messages.activity_log') }}</h3>
                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition>
                <div class="rounded-xl border border-gray-100 divide-y divide-gray-100 mb-4">
                    @php
                        $actionLabels = [
                            'viewed'               => ['label' => 'Consulté',           'class' => 'bg-blue-100 text-blue-700'],
                            'transferred'          => ['label' => 'Transféré',          'class' => 'bg-purple-100 text-purple-700'],
                            'printed'              => ['label' => 'Imprimé',            'class' => 'bg-yellow-100 text-yellow-700'],
                            'downloaded'           => ['label' => 'Téléchargé',         'class' => 'bg-green-100 text-green-700'],
                            'reception_accepted'   => ['label' => 'Réception confirmée','class' => 'bg-teal-100 text-teal-700'],
                            'reception_refused'    => ['label' => 'Réception refusée',  'class' => 'bg-red-100 text-red-700'],
                            'affectation_created'  => ['label' => 'Affecté pour avis',  'class' => 'bg-indigo-100 text-indigo-700'],
                            'affectation_responded'=> ['label' => 'Avis soumis',        'class' => 'bg-indigo-100 text-indigo-700'],
                            'updated'              => ['label' => 'Modifié',            'class' => 'bg-gray-100 text-gray-700'],
                            'created'              => ['label' => 'Créé',               'class' => 'bg-gray-100 text-gray-700'],
                        ];
                    @endphp
                    @foreach($activityLogs as $log)
                        @php
                            $al = $actionLabels[$log->description] ?? ['label' => $log->description, 'class' => 'bg-gray-100 text-gray-700'];
                        @endphp
                        <div class="p-3 flex justify-between items-center">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $al['class'] }}">
                                    {{ $al['label'] }}
                                </span>
                                @if($log->properties->isNotEmpty())
                                    @foreach($log->properties as $key => $val)
                                        @if($val && is_string($val) && $key !== 'attributes' && $key !== 'old')
                                            <span class="text-xs text-gray-500">{{ $val }}</span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="text-right text-xs text-gray-500 flex-shrink-0">
                                <p>{{ $log->causer?->name ?? 'Système' }}</p>
                                <p>{{ $log->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
    {{-- ================================================================ --}}

    @if($from === 'cart')


        {{-- ── Transfert modal ─────────────────────────────────────────── --}}
        <div id="transferModal" class="absolute inset-0 z-[99999] flex items-center justify-center bg-black/50
            {{ $errors->any() ? '' : 'hidden' }}">

            <div class="bg-white w-full max-w-md rounded shadow p-6">

                <h2 class="text-lg font-semibold mb-4">Transférer le dossier</h2>

                <form method="POST" action="{{ route('demande.transfert') }}">
                    @csrf
                    <input type="hidden" name="demande_id" value="{{ $request->id }}">

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Service de destination</label>
                        @if($allowedServices->isEmpty())
                            <p class="text-sm text-gray-500 italic">Aucun transfert possible depuis ce service selon le circuit défini.</p>
                        @else
                            <select name="service_id" class="w-full border rounded px-3 py-2">
                                <option value="">-- Choisir un service --</option>
                                @foreach($allowedServices as $service)
                                    <option value="{{ $service->id }}">{{ $service->nom }}</option>
                                @endforeach
                            </select>
                        @endif
                        @error('service_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Commentaire (optionnel)</label>
                        <textarea name="commentaire" rows="3"
                                  class="w-full border rounded px-3 py-2"
                                  placeholder="Instructions, observations..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button"
                                onclick="document.getElementById('transferModal').classList.add('hidden')"
                                class="px-4 py-2 border rounded">
                            Annuler
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded"
                                @if($allowedServices->isEmpty()) disabled @endif>
                            Transférer
                        </button>
                    </div>
                </form>

            </div>
        </div>

    @endif

    @if($from === 'dashboard' && $request->isDraft())
        {{-- Delete Confirmation Modal --}}
        <div id="deleteConfirmModal" class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 hidden">
            <div class="bg-white w-full max-w-sm rounded-lg shadow-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Supprimer la demande</h3>
                </div>
                <p class="text-sm text-gray-600 mb-6">
                    Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est <strong>irréversible</strong> et supprimera tous les fichiers associés.
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="document.getElementById('deleteConfirmModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm transition-colors">
                        Annuler
                    </button>
                    <form action="{{ route('demandes.destroy', $request->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm transition-colors">
                            Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

            </div>{{-- end right column --}}
        </div>{{-- end grid --}}
    </div>{{-- end layout --}}
</x-app-layout>