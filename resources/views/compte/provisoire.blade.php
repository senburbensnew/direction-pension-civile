<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Espace provisoire
            </h2>
            @if($demande)
                <p class="text-sm text-gray-500">
                    {{ $demande->libellePublic() }}
                </p>
            @endif
        </div>
    </x-slot>

    <x-portal-page class="space-y-6">

        @if (session('warning'))
            <div class="flex gap-3 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 shadow-sm">
                <i class="fas fa-exclamation-triangle mt-0.5 text-amber-600 shrink-0" aria-hidden="true"></i>
                <p>{{ session('warning') }}</p>
            </div>
        @endif

        {{-- En-tête --}}
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#173052] via-[#1e3d6b] to-[#2a5085] text-white shadow-md">
            <div class="absolute inset-0 opacity-[0.07] pointer-events-none" aria-hidden="true">
                <div class="absolute -right-8 -top-8 h-40 w-40 rounded-full bg-white"></div>
                <div class="absolute -left-4 bottom-0 h-28 w-28 rounded-full bg-orange-400"></div>
            </div>
            <div class="relative p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                        <i class="fas fa-hourglass-half text-lg" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-white/80">Bonjour, {{ $user->displayName() }}</p>
                        <h1 class="mt-1 text-xl sm:text-2xl font-bold tracking-tight">
                            @if($isRefused)
                                Votre demande n’a pas été retenue
                            @elseif($awaitingDecision)
                                Dossier en cours d’examen
                            @elseif($rdvRealise)
                                Rendez-vous terminé — validation en cours
                            @elseif($rdv)
                                Rendez-vous planifié
                            @else
                                Finalisez l’ouverture de votre compte
                            @endif
                        </h1>
                        <p class="mt-2 text-sm text-white/85 leading-relaxed max-w-xl">
                            @if($isRefused)
                                Votre accès en ligne est limité. Consultez le motif du refus et les coordonnées de la Direction si vous avez des questions.
                            @else
                                Votre compte est <strong class="font-semibold text-white">provisoire</strong>.
                                Les services complets seront disponibles après validation par le Service Formalités.
                            @endif
                        </p>
                    </div>
                </div>

                @if($needsRdv && $canBookRdv)
                    <div class="mt-6 flex flex-wrap gap-3">
                        <a
                            href="{{ route('demandes.rencontre.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-orange-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-300 focus:ring-offset-2 focus:ring-offset-[#173052] transition-colors"
                        >
                            <i class="fas fa-calendar-check" aria-hidden="true"></i>
                            Prendre rendez-vous
                        </a>
                        @if($demande)
                            <a
                                href="{{ route('compte.demande.show') }}"
                                class="inline-flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2.5 text-sm font-medium text-white ring-1 ring-white/25 hover:bg-white/15 transition-colors"
                            >
                                Voir ma demande
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        @if($isRefused && $demande?->refusal_reason)
            <section class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-950">
                <p class="font-semibold flex items-center gap-2">
                    <i class="fas fa-times-circle text-red-600" aria-hidden="true"></i>
                    Motif du refus
                </p>
                <p class="mt-2 leading-relaxed">{{ $demande->refusal_reason }}</p>
            </section>
        @endif

        {{-- Parcours --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-bold text-gray-900">Où en suis-je ?</h2>
            <p class="mt-1 text-sm text-gray-600">Les étapes de validation de votre compte pensionné en ligne.</p>

            <ol class="mt-6 space-y-0">
                @foreach($steps as $index => $step)
                    @php
                        $isLast = $index === count($steps) - 1;
                        $state = $step['state'];
                        $dotClass = match ($state) {
                            'done' => 'bg-emerald-500 text-white ring-emerald-100',
                            'current' => 'bg-[#173052] text-white ring-[#173052]/20',
                            default => 'bg-gray-100 text-gray-400 ring-gray-100',
                        };
                        $lineClass = $state === 'done' ? 'bg-emerald-300' : 'bg-gray-200';
                    @endphp
                    <li class="relative flex gap-4 pb-8 {{ $isLast ? 'pb-0' : '' }}">
                        @if(! $isLast)
                            <span
                                class="absolute left-[1.125rem] top-10 bottom-0 w-0.5 {{ $lineClass }}"
                                aria-hidden="true"
                            ></span>
                        @endif
                        <div class="relative z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full ring-4 {{ $dotClass }}">
                            @if($state === 'done')
                                <i class="fas fa-check text-xs" aria-hidden="true"></i>
                            @else
                                <span class="text-xs font-bold">{{ $index + 1 }}</span>
                            @endif
                        </div>
                        <div class="min-w-0 pt-0.5">
                            <p class="text-sm font-semibold text-gray-900">{{ $step['title'] }}</p>
                            <p class="mt-0.5 text-sm text-gray-600">{{ $step['hint'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- Détail RDV --}}
        @if($rdv && ! $isRefused)
            <section class="rounded-2xl border border-indigo-200 bg-indigo-50/80 p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-indigo-800/70">Prochain rendez-vous</p>
                        <p class="mt-1 text-lg font-bold text-indigo-950">
                            {{ $rdvSummary['date'] ?? 'Date à confirmer' }}
                            @if($rdvSummary['heure'])
                                <span class="font-semibold text-indigo-800">· {{ $rdvSummary['heure'] }}</span>
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-indigo-900/80">
                            @if($rdvSummary['modalite'])
                                {{ $rdvSummary['modalite'] }}
                                ·
                            @endif
                            Service Formalités
                            @if($rdvSummary['label'])
                                · <span class="font-medium">{{ $rdvSummary['label'] }}</span>
                            @endif
                        </p>
                    </div>
                    <a
                        href="{{ route('personal.request.authenticated-user-request.show', $rdv->id) }}"
                        class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-800 hover:text-indigo-950 hover:underline"
                    >
                        Détails du rendez-vous
                        <i class="fas fa-arrow-right text-xs" aria-hidden="true"></i>
                    </a>
                </div>
            </section>
        @endif

        {{-- Mes demandes de rencontre --}}
        @if($rendezVousDemandes->isNotEmpty())
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Mes rendez-vous</h2>
                        <p class="text-sm text-gray-600 mt-0.5">Consultez le détail et le suivi de chaque demande de rencontre.</p>
                    </div>
                    @if($canBookRdv && ! $isRefused)
                        <a
                            href="{{ route('demandes.rencontre.create') }}"
                            class="inline-flex items-center gap-2 text-sm font-semibold text-[#173052] hover:text-orange-600"
                        >
                            <i class="fas fa-plus-circle" aria-hidden="true"></i>
                            Nouveau rendez-vous
                        </a>
                    @endif
                </div>

                <ul class="divide-y divide-gray-100 rounded-xl border border-gray-100 overflow-hidden">
                    @foreach($rendezVousDemandes as $rdvDemande)
                        @php
                            $data = is_array($rdvDemande->data) ? $rdvDemande->data : [];
                            $statut = $rdvDemande->rencontreStatut();
                            $motif = $data['motif'] ?? $data['objet'] ?? 'Rendez-vous';
                            $dateLine = filled($data['date_souhaitee'] ?? null)
                                ? \Illuminate\Support\Carbon::parse($data['date_souhaitee'])->locale('fr')->isoFormat('D MMM YYYY')
                                : null;
                            $heure = $data['heure_souhaitee'] ?? null;
                        @endphp
                        <li class="flex flex-wrap items-center justify-between gap-3 bg-gray-50/50 px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $motif }}</p>
                                <p class="text-xs text-gray-600 mt-0.5">
                                    @if($dateLine)
                                        {{ $dateLine }}@if($heure) · {{ $heure }}@endif
                                    @else
                                        Date à confirmer
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $statut->badgeClass() }}">
                                    {{ $statut->label() }}
                                </span>
                                <a
                                    href="{{ route('personal.request.authenticated-user-request.show', $rdvDemande->id) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-[#173052] px-3 py-1.5 text-xs font-medium text-white hover:bg-orange-600 transition-colors"
                                >
                                    Consulter
                                    <i class="fas fa-arrow-right text-[10px]" aria-hidden="true"></i>
                                </a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Accès --}}
        <div class="grid sm:grid-cols-2 gap-4">
            <section class="rounded-2xl border border-emerald-200/80 bg-emerald-50/50 p-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-emerald-950">
                    <i class="fas fa-circle-check text-emerald-600" aria-hidden="true"></i>
                    Disponible maintenant
                </h3>
                <ul class="mt-3 space-y-2 text-sm text-emerald-950/90">
                    <li class="flex gap-2">
                        <i class="fas fa-angle-right mt-0.5 text-emerald-600/80 text-xs" aria-hidden="true"></i>
                        Consulter l’état de vos demandes
                    </li>
                    <li class="flex gap-2">
                        <i class="fas fa-angle-right mt-0.5 text-emerald-600/80 text-xs" aria-hidden="true"></i>
                        Lire vos notifications
                    </li>
                    <li class="flex gap-2">
                        <i class="fas fa-angle-right mt-0.5 text-emerald-600/80 text-xs" aria-hidden="true"></i>
                        Répondre à une demande de complément
                    </li>
                    @if($canBookRdv && ! $isRefused)
                        <li class="flex gap-2">
                            <i class="fas fa-angle-right mt-0.5 text-emerald-600/80 text-xs" aria-hidden="true"></i>
                            Prendre ou gérer un rendez-vous Formalités
                        </li>
                    @endif
                </ul>
            </section>

            <section class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-5">
                <h3 class="flex items-center gap-2 text-sm font-bold text-amber-950">
                    <i class="fas fa-lock text-amber-600" aria-hidden="true"></i>
                    Après activation
                </h3>
                <ul class="mt-3 space-y-2 text-sm text-amber-950/90">
                    <li class="flex gap-2">
                        <i class="fas fa-minus mt-1 text-amber-500/80 text-xs" aria-hidden="true"></i>
                        Démarches pension (virements, attestations…)
                    </li>
                    <li class="flex gap-2">
                        <i class="fas fa-minus mt-1 text-amber-500/80 text-xs" aria-hidden="true"></i>
                        Modification du profil et de l’identité
                    </li>
                    <li class="flex gap-2">
                        <i class="fas fa-minus mt-1 text-amber-500/80 text-xs" aria-hidden="true"></i>
                        Téléchargement de documents officiels annotés
                    </li>
                </ul>
            </section>
        </div>

        {{-- Actions --}}
        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <h3 class="text-sm font-bold text-gray-900">Raccourcis</h3>
            <div class="mt-4 flex flex-wrap gap-3">
                @if($demande)
                    <a
                        href="{{ route('compte.demande.show') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-[#173052] px-4 py-2.5 text-sm font-medium text-white hover:bg-orange-600 transition-colors"
                    >
                        <i class="fas fa-id-card" aria-hidden="true"></i>
                        Ma demande de compte
                    </a>
                @endif
                <a
                    href="{{ route('personal.dashboard') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50 transition-colors"
                >
                    <i class="fas fa-folder-open" aria-hidden="true"></i>
                    Mes demandes
                </a>
                <a
                    href="{{ route('notifications.index') }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-800 hover:bg-gray-50 transition-colors"
                >
                    <i class="fas fa-bell" aria-hidden="true"></i>
                    Notifications
                </a>
                @if($canBookRdv && ! $needsRdv && ! $isRefused)
                    <a
                        href="{{ route('demandes.rencontre.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-[#173052] text-[#173052] px-4 py-2.5 text-sm font-medium hover:bg-[#173052]/5 transition-colors"
                    >
                        <i class="fas fa-calendar-plus" aria-hidden="true"></i>
                        Rendez-vous
                    </a>
                @endif
            </div>
        </section>

        <p class="text-center text-xs text-gray-500 pb-4">
            Direction de la Pension Civile — assistance :
            <a href="{{ route('contact') }}" class="text-[#173052] font-medium hover:underline">nous contacter</a>
        </p>
    </x-portal-page>
</x-app-layout>
