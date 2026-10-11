@php
    $stats = collect($folderStats ?? []);
    $totalActifs = $stats->where('key', '!=', 'clotures')->sum('count');
    $totalClotures = (int) ($stats->firstWhere('key', 'clotures')['count'] ?? 0);
    $urgentCount = (int) ($stats->firstWhere('key', 'urgent')['count'] ?? 0);
    $rencontreCount = (int) ($stats->firstWhere('key', 'rencontre')['count'] ?? 0);

    $comptesCount = isset($demandesComptePending) ? $demandesComptePending->count() : 0;
    $comptesRoute = auth()->user()->hasRole('admin')
        ? route('admin.comptes-demandes.index')
        : route('formalites.comptes-demandes.index');

    $palette = [
        'blue'   => ['stripe' => 'border-l-blue-600',   'icon' => 'bg-blue-100 text-blue-700',   'badge' => 'bg-blue-600',   'link' => 'hover:border-blue-300 hover:shadow-blue-100'],
        'red'    => ['stripe' => 'border-l-red-600',    'icon' => 'bg-red-100 text-red-700',     'badge' => 'bg-red-600',    'link' => 'hover:border-red-300 hover:shadow-red-100'],
        'yellow' => ['stripe' => 'border-l-amber-500',  'icon' => 'bg-amber-100 text-amber-800', 'badge' => 'bg-amber-500',  'link' => 'hover:border-amber-300 hover:shadow-amber-100'],
        'indigo' => ['stripe' => 'border-l-indigo-600', 'icon' => 'bg-indigo-100 text-indigo-700','badge' => 'bg-indigo-600', 'link' => 'hover:border-indigo-300 hover:shadow-indigo-100'],
        'purple' => ['stripe' => 'border-l-purple-600', 'icon' => 'bg-purple-100 text-purple-700','badge' => 'bg-purple-600', 'link' => 'hover:border-purple-300 hover:shadow-purple-100'],
        'green'  => ['stripe' => 'border-l-emerald-600','icon' => 'bg-emerald-100 text-emerald-700','badge' => 'bg-emerald-600','link' => 'hover:border-emerald-300 hover:shadow-emerald-100'],
        'gray'   => ['stripe' => 'border-l-slate-400',  'icon' => 'bg-slate-100 text-slate-600', 'badge' => 'bg-slate-500',  'link' => 'hover:border-slate-300'],
        'teal'   => ['stripe' => 'border-l-teal-600',   'icon' => 'bg-teal-100 text-teal-700',   'badge' => 'bg-teal-600',   'link' => 'hover:border-teal-300 hover:shadow-teal-100'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Corbeille de traitement
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

        @if (session('success'))
            <div class="flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                <i class="fas fa-check-circle mt-0.5 text-green-600" aria-hidden="true"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <i class="fas fa-exclamation-circle mt-0.5 text-red-500" aria-hidden="true"></i>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- En-tête --}}
        <header class="relative overflow-hidden rounded bg-gradient-to-br from-[#173052] via-[#1e3d6b] to-[#2a5085] text-white shadow-md">
            <div class="absolute inset-0 opacity-[0.08] pointer-events-none" aria-hidden="true">
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white"></div>
                <div class="absolute -left-6 bottom-0 h-32 w-32 rounded-full bg-orange-400"></div>
            </div>
            <div class="relative px-6 py-7 sm:px-8 sm:py-8">
                <p class="text-xs font-semibold uppercase tracking-widest text-white/70">Espace agent</p>
                <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight">Corbeille de traitement</h1>
                <p class="mt-2 text-sm text-white/85 max-w-2xl leading-relaxed">
                    Dossiers présents dans votre service, classés par nature. Ouvrez un répertoire pour consulter et faire avancer les demandes selon le circuit.
                </p>
                @if(auth()->user()?->service)
                    <p class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-white/90 ring-1 ring-white/20">
                        <i class="fas fa-building" aria-hidden="true"></i>
                        {{ auth()->user()->service->nom }}
                    </p>
                @endif
            </div>
        </header>

        {{-- Indicateurs --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">En cours</p>
                <p class="mt-1 text-2xl font-bold text-[#173052] tabular-nums">{{ $totalActifs }}</p>
                <p class="text-xs text-gray-500 mt-1">Dossiers actifs dans les répertoires</p>
            </div>
            <div class="rounded-xl border border-red-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-red-600/80">Urgents / retards</p>
                <p class="mt-1 text-2xl font-bold text-red-700 tabular-nums">{{ $urgentCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Priorité ou délai dépassé</p>
            </div>
            <div class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-emerald-700/80">Rendez-vous</p>
                <p class="mt-1 text-2xl font-bold text-emerald-800 tabular-nums">{{ $rencontreCount }}</p>
                <p class="text-xs text-gray-500 mt-1">Attribution et suivi RDV</p>
            </div>
            <div class="rounded-xl border border-teal-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-medium uppercase tracking-wide text-teal-700/80">Clôturés</p>
                <p class="mt-1 text-2xl font-bold text-teal-800 tabular-nums">{{ $totalClotures }}</p>
                <p class="text-xs text-gray-500 mt-1">Archives du service</p>
            </div>
        </div>

        {{-- Appels veille --}}
        @if(isset($appelsVeille) && $appelsVeille->isNotEmpty())
            <section class="rounded-2xl border border-sky-200 bg-white shadow-sm overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-sky-100 bg-sky-50/80 px-5 py-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-600 text-white">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        </span>
                        <div>
                            <h2 class="text-sm font-bold text-sky-950">Appels de rappel — veille des rencontres</h2>
                            <p class="text-xs text-sky-800/70 mt-0.5">Contacter l’usager la veille du rendez-vous planifié.</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center rounded-full bg-sky-600 px-3 py-1 text-xs font-bold text-white tabular-nums">
                        {{ $appelsVeille->count() }}
                    </span>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($appelsVeille as $rdv)
                        @php
                            $data = $rdv->data ?? [];
                            $usager = $rdv->user;
                            $nomUsager = $usager?->displayName()
                                ?: trim(($data['prenom'] ?? '').' '.($data['nom'] ?? ''))
                                ?: '—';
                            $telephone = $data['telephone'] ?? $usager?->telephone ?? $usager?->phone ?? '—';
                            $heure = $data['heure_souhaitee']
                                ? \App\Models\Demande::normalizeRencontreTime($data['heure_souhaitee'])
                                : '';
                            $modalite = $data['modalite'] ?? 'physique';
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 hover:bg-sky-50/40 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $rdv->libellePublic() }}</p>
                                <p class="text-sm text-gray-700 mt-0.5">{{ $nomUsager }}</p>
                                <p class="text-xs text-gray-500 mt-1">
                                    <i class="fas fa-phone-alt text-sky-500 mr-1" aria-hidden="true"></i>{{ $telephone }}
                                    · RDV demain{{ $heure ? ' à '.$heure : '' }}
                                    · {{ $modalite === 'physique' ? ($data['lieu_rdv'] ?? 'Présentiel') : 'Visioconférence' }}
                                </p>
                            </div>
                            <a href="{{ route('personal.request.show', $rdv->id) }}"
                               class="inline-flex items-center gap-2 rounded-lg bg-[#173052] px-4 py-2 text-xs font-semibold text-white hover:bg-orange-600 transition-colors">
                                Ouvrir le dossier
                                <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Répertoires --}}
        <section>
            <div class="flex flex-wrap items-end justify-between gap-3 mb-5">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Répertoires de dossiers</h2>
                    <p class="text-sm text-gray-600 mt-1">Sélectionnez une catégorie pour afficher la liste détaillée.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

                @if(auth()->user()->hasAnyRole(['admin', \App\Models\User::ROLE_AGENT_FORMALITES]))
                    @php $p = $palette['indigo']; @endphp
                    @if($comptesCount > 0)
                        <a href="{{ $comptesRoute }}"
                           class="group relative flex flex-col rounded-xl border border-gray-200 border-l-4 {{ $p['stripe'] }} bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $p['link'] }}">
                    @else
                        <div class="relative flex flex-col rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-5 opacity-75">
                    @endif
                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $comptesCount > 0 ? $p['icon'] : 'bg-gray-100 text-gray-400' }}">
                                <i class="fas fa-user-plus" aria-hidden="true"></i>
                            </span>
                            <span class="text-2xl font-bold tabular-nums {{ $comptesCount > 0 ? 'text-gray-900' : 'text-gray-400' }}">
                                {{ $comptesCount }}
                            </span>
                        </div>
                        <p class="mt-4 text-sm font-semibold {{ $comptesCount > 0 ? 'text-gray-900' : 'text-gray-500' }}">
                            Création de compte
                        </p>
                        <p class="mt-1 text-xs text-gray-500">Demandes en attente de validation</p>
                        @if($comptesCount > 0)
                            <span class="mt-4 inline-flex items-center text-xs font-semibold text-indigo-700 opacity-0 group-hover:opacity-100 transition-opacity">
                                Accéder <i class="fas fa-chevron-right ml-1 text-[10px]" aria-hidden="true"></i>
                            </span>
                        @endif
                    @if($comptesCount > 0)
                        </a>
                    @else
                        </div>
                    @endif
                @endif

                @foreach($folderStats as $folder)
                    @php
                        $p = $palette[$folder['color']] ?? $palette['gray'];
                        $active = ($folder['count'] ?? 0) > 0;
                    @endphp

                    @if($active)
                        <a href="{{ route('personal.cart.folder', ['folder' => $folder['key']]) }}"
                           class="group relative flex flex-col rounded-xl border border-gray-200 border-l-4 {{ $p['stripe'] }} bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md {{ $p['link'] }}">
                    @else
                        <div class="relative flex flex-col rounded-xl border border-dashed border-gray-200 bg-gray-50/80 p-5">
                    @endif

                        <div class="flex items-start justify-between gap-3">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl {{ $active ? $p['icon'] : 'bg-gray-100 text-gray-400' }}">
                                <i class="fas {{ $folder['icon'] }}" aria-hidden="true"></i>
                            </span>
                            <span class="inline-flex min-w-[2rem] justify-center rounded-full px-2 py-0.5 text-sm font-bold tabular-nums {{ $active ? $p['badge'].' text-white' : 'text-gray-400 bg-gray-100' }}">
                                {{ $folder['count'] }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm font-semibold leading-snug {{ $active ? 'text-gray-900' : 'text-gray-500' }}">
                            {{ $folder['label'] }}
                        </p>

                        @if($active)
                            <span class="mt-4 inline-flex items-center text-xs font-semibold text-[#173052] opacity-0 group-hover:opacity-100 transition-opacity">
                                Voir les dossiers
                                <i class="fas fa-chevron-right ml-1 text-[10px]" aria-hidden="true"></i>
                            </span>
                        @else
                            <p class="mt-2 text-xs text-gray-400">Aucun dossier pour l’instant</p>
                        @endif

                    @if($active)
                        </a>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    </div>
</x-app-layout>
