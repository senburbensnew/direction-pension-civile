@php
    $rencontreCount = (int) (collect($folderStats ?? [])->firstWhere('key', 'rencontre')['count'] ?? 0);
    $cloturesCount = (int) (collect($folderStats ?? [])->firstWhere('key', 'clotures')['count'] ?? 0);
    $showComptesCard = auth()->user()?->hasAnyRole(['admin', \App\Models\User::ROLE_AGENT_FORMALITES]);
    $comptesCount = isset($demandesComptePending) ? $demandesComptePending->count() : 0;
    $comptesRoute = auth()->user()?->hasRole('admin')
        ? route('admin.comptes-demandes.index')
        : route('formalites.comptes-demandes.index');

    $tones = [
        'rencontre' => [
            'stripe' => 'border-l-emerald-600',
            'kpi_bg' => 'bg-emerald-50/80',
            'kpi_label' => 'text-emerald-800',
            'kpi_value' => 'text-emerald-900',
            'icon' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'badge' => 'bg-emerald-600 text-white',
            'hover' => 'hover:bg-emerald-50/60',
            'btn' => 'border-emerald-300 text-emerald-900 hover:bg-emerald-50',
        ],
        'clotures' => [
            'stripe' => 'border-l-slate-500',
            'kpi_bg' => 'bg-slate-50/80',
            'kpi_label' => 'text-slate-700',
            'kpi_value' => 'text-slate-900',
            'icon' => 'bg-slate-100 text-slate-700 border-slate-200',
            'badge' => 'bg-slate-600 text-white',
            'hover' => 'hover:bg-slate-50/80',
            'btn' => 'border-slate-300 text-slate-800 hover:bg-slate-50',
        ],
        'compte' => [
            'stripe' => 'border-l-indigo-600',
            'kpi_bg' => 'bg-indigo-50/80',
            'kpi_label' => 'text-indigo-800',
            'kpi_value' => 'text-indigo-900',
            'icon' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'badge' => 'bg-indigo-600 text-white',
            'hover' => 'hover:bg-indigo-50/60',
            'btn' => 'border-indigo-300 text-indigo-900 hover:bg-indigo-50',
        ],
    ];
    $rdvTone = $tones['rencontre'];
    $cloturesTone = $tones['clotures'];
    $compteTone = $tones['compte'];
@endphp

<x-app-layout>
    <x-portal-page class="dossier-page space-y-6">

        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-[#173052] bg-gradient-to-r from-slate-50 to-white">
            <p class="dossier-kicker">Espace agent</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Corbeille de traitement</h1>
            <p class="mt-2 text-sm text-gray-600 max-w-2xl">
                Rendez-vous en cours, dossiers clôturés et création de compte pour votre service.
            </p>
            @if(auth()->user()?->service)
                <p class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-[#173052] bg-[#173052]/5 border border-[#173052]/15 px-2.5 py-1">
                    <i class="fas fa-building text-[#173052]/70" aria-hidden="true"></i>
                    {{ auth()->user()->service->nom }}
                </p>
            @endif
        </header>

        <section class="dossier-card overflow-hidden">
            <div class="dossier-card-head bg-slate-50/90">
                <h2>Synthèse</h2>
            </div>
            <dl class="grid grid-cols-1 sm:grid-cols-{{ $showComptesCard ? '3' : '2' }} divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
                <div class="px-5 py-4 sm:py-5 {{ $rdvTone['kpi_bg'] }} border-l-4 {{ $rdvTone['stripe'] }}">
                    <dt class="text-xs font-semibold uppercase tracking-wide {{ $rdvTone['kpi_label'] }}">Attribution de rendez-vous</dt>
                    <dd class="mt-1 text-2xl font-bold tabular-nums {{ $rdvTone['kpi_value'] }}">{{ $rencontreCount }}</dd>
                    <dd class="text-xs text-emerald-800/70 mt-1">RDV actifs dans votre corbeille</dd>
                </div>
                <div class="px-5 py-4 sm:py-5 {{ $cloturesTone['kpi_bg'] }} border-l-4 {{ $cloturesTone['stripe'] }}">
                    <dt class="text-xs font-semibold uppercase tracking-wide {{ $cloturesTone['kpi_label'] }}">Dossiers clôturés</dt>
                    <dd class="mt-1 text-2xl font-bold tabular-nums {{ $cloturesTone['kpi_value'] }}">{{ $cloturesCount }}</dd>
                    <dd class="text-xs text-slate-600/80 mt-1">Réalisés, annulés, refusés ou non honorés</dd>
                </div>
                @if($showComptesCard)
                    <div class="px-5 py-4 sm:py-5 {{ $compteTone['kpi_bg'] }} border-l-4 {{ $compteTone['stripe'] }}">
                        <dt class="text-xs font-semibold uppercase tracking-wide {{ $compteTone['kpi_label'] }}">Création de compte</dt>
                        <dd class="mt-1 text-2xl font-bold tabular-nums {{ $compteTone['kpi_value'] }}">{{ $comptesCount }}</dd>
                        <dd class="text-xs text-indigo-800/70 mt-1">Demandes en attente de validation</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if(isset($appelsVeille) && $appelsVeille->isNotEmpty())
            <x-rencontre-appels-veille-panel :appels-veille="$appelsVeille" />
        @endif

        <section class="dossier-card overflow-hidden">
            <div class="dossier-card-head bg-slate-50/90">
                <div>
                    <h2>Répertoires</h2>
                    <p class="text-xs text-gray-500 mt-0.5 font-normal">Ouvrir un dossier pour le traiter</p>
                </div>
            </div>

            <ul class="divide-y divide-gray-100">
                @foreach($folderStats as $folder)
                    @php
                        $active = ($folder['count'] ?? 0) > 0;
                        $tone = match ($folder['key'] ?? '') {
                            'rencontre' => $rdvTone,
                            'clotures' => $cloturesTone,
                            default => $tones[$folder['color'] ?? ''] ?? $rdvTone,
                        };
                    @endphp
                    <li>
                        @if($active)
                            <a href="{{ route('personal.cart.folder', ['folder' => $folder['key']]) }}"
                               class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-l-4 {{ $tone['stripe'] }} {{ $tone['hover'] }} transition-colors group">
                        @else
                            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-gray-50/60 border-l-4 border-l-gray-200 text-gray-500">
                        @endif
                            <div class="flex items-center gap-3 min-w-0">
                                <span @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center border',
                                    $active ? $tone['icon'] : 'bg-gray-100 text-gray-400 border-gray-200',
                                ])>
                                    <i class="fas {{ $folder['icon'] }} text-sm" aria-hidden="true"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold {{ $active ? 'text-gray-900' : 'text-gray-500' }}">
                                        {{ $folder['label'] }}
                                    </p>
                                    @unless($active)
                                        <p class="text-xs text-gray-400 mt-0.5">Aucun dossier pour l’instant</p>
                                    @endunless
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span @class([
                                    'inline-flex min-w-[2rem] justify-center px-2 py-0.5 text-sm font-bold tabular-nums',
                                    $active ? $tone['badge'] : 'bg-gray-200 text-gray-500',
                                ])>
                                    {{ $folder['count'] }}
                                </span>
                                @if($active)
                                    <span @class([
                                        'inline-flex items-center gap-1 text-xs font-semibold border px-3 py-1.5 bg-white transition-colors',
                                        $tone['btn'],
                                    ])>
                                        Ouvrir
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        @if($active)
                            </a>
                        @else
                            </div>
                        @endif
                    </li>
                @endforeach

                @if($showComptesCard)
                    @php $activeCompte = $comptesCount > 0; @endphp
                    <li>
                        @if($activeCompte)
                            <a href="{{ $comptesRoute }}"
                               class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-l-4 {{ $compteTone['stripe'] }} {{ $compteTone['hover'] }} transition-colors group">
                        @else
                            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 bg-gray-50/60 border-l-4 border-l-gray-200 text-gray-500">
                        @endif
                            <div class="flex items-center gap-3 min-w-0">
                                <span @class([
                                    'flex h-10 w-10 shrink-0 items-center justify-center border',
                                    $activeCompte ? $compteTone['icon'] : 'bg-gray-100 text-gray-400 border-gray-200',
                                ])>
                                    <i class="fas fa-user-plus text-sm" aria-hidden="true"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold {{ $activeCompte ? 'text-gray-900' : 'text-gray-500' }}">
                                        Création de compte
                                    </p>
                                    <p class="text-xs {{ $activeCompte ? 'text-gray-500' : 'text-gray-400' }} mt-0.5">
                                        Demandes en attente de validation
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span @class([
                                    'inline-flex min-w-[2rem] justify-center px-2 py-0.5 text-sm font-bold tabular-nums',
                                    $activeCompte ? $compteTone['badge'] : 'bg-gray-200 text-gray-500',
                                ])>
                                    {{ $comptesCount }}
                                </span>
                                @if($activeCompte)
                                    <span @class([
                                        'inline-flex items-center gap-1 text-xs font-semibold border px-3 py-1.5 bg-white transition-colors',
                                        $compteTone['btn'],
                                    ])>
                                        Ouvrir
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        @if($activeCompte)
                            </a>
                        @else
                            </div>
                        @endif
                    </li>
                @endif
            </ul>
        </section>

    </x-portal-page>
</x-app-layout>
