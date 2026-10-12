<x-app-layout>
    @php
        $data = $demande->data ?? [];
        $minutesBefore = (int) config('rdv.visio.activate_minutes_before', 15);
    @endphp

    <x-portal-page bottom="pb-8" class="space-y-5 max-w-[1600px]">
        <div class="sticky top-0 z-20 -mx-4 px-4 sm:-mx-6 sm:px-6 py-2 bg-gray-100/95 backdrop-blur-sm border-b border-gray-200">
            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-[#173052] hover:text-orange-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                Retour à la page du rendez-vous
            </a>
        </div>

        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-[#173052]">
            <p class="dossier-kicker">Rendez-vous sécurisé</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Visioconférence</h1>
            <p class="text-sm text-gray-600 mt-2">
                {{ $data['objet'] ?? $data['motif'] ?? 'Rendez-vous' }}
                @if($startsAt)
                    · {{ $startsAt->format('d/m/Y à H:i') }}
                @endif
            </p>
        </header>

        @if($status === 'indisponible')
            <section class="dossier-card p-5 sm:p-8 max-w-3xl">
                <h2 class="text-base font-semibold text-[#173052]">Lien indisponible</h2>
                <p class="text-sm text-gray-600 mt-2">
                    La date ou l’heure du rendez-vous n’est pas enregistrée correctement. Contactez la Direction ou consultez votre dossier.
                </p>
            </section>
        @elseif($status === 'en_attente')
            <section class="dossier-card p-5 sm:p-8 max-w-3xl"
                     x-data="{
                         opensAt: @js($opensAt?->toIso8601String()),
                         label: '',
                         tick() {
                             if (!this.opensAt) return;
                             const ms = new Date(this.opensAt).getTime() - Date.now();
                             if (ms <= 0) { this.label = ''; return; }
                             const s = Math.floor(ms / 1000);
                             const h = Math.floor(s / 3600);
                             const m = Math.floor((s % 3600) / 60);
                             const sec = s % 60;
                             this.label = h > 0
                                 ? `${h} h ${String(m).padStart(2,'0')} min`
                                 : `${m} min ${String(sec).padStart(2,'0')} s`;
                         }
                     }"
                     x-init="tick(); setInterval(() => tick(), 1000)">
                <h2 class="text-base font-semibold text-[#173052]">Lien pas encore actif</h2>
                <p class="text-sm text-gray-600 mt-2">
                    Cette salle s’ouvre {{ $minutesBefore }} minutes avant l’heure du rendez-vous.
                </p>
                @if($opensAt)
                    <p class="mt-3 text-sm font-medium text-[#173052]">
                        Ouverture : {{ $opensAt->format('d/m/Y à H:i') }}
                    </p>
                    <p class="mt-2 text-sm text-emerald-800" x-show="label" x-cloak>
                        Ouverture dans <span x-text="label"></span>
                    </p>
                @endif
                <p class="mt-4 text-xs text-gray-500">Cette page se met à jour automatiquement dès l’ouverture de la salle.</p>
            </section>
        @elseif($status === 'termine')
            <section class="dossier-card p-5 sm:p-8 max-w-3xl">
                <h2 class="text-base font-semibold text-[#173052]">Rendez-vous terminé</h2>
                <p class="text-sm text-gray-600 mt-2">
                    Le lien sécurisé n’est plus actif
                    @if($closesAt)
                        (fermeture le {{ $closesAt->format('d/m/Y à H:i') }}).
                    @else
                        .
                    @endif
                </p>
            </section>
        @else
            <section class="dossier-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3 bg-[#173052] text-white text-sm">
                    <div class="flex flex-wrap items-center gap-3 min-w-0">
                        <a href="{{ $backUrl }}"
                           class="inline-flex items-center gap-1.5 shrink-0 px-2.5 py-1.5 rounded border border-white/30 bg-white/10 hover:bg-white/20 text-xs font-semibold">
                            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                            Retour
                        </a>
                        <p class="font-medium min-w-0">
                            Salle ouverte — visioconférence sécurisée de la Direction de la Pension Civile.
                        </p>
                    </div>
                    @if($embedUrl)
                        <a href="{{ $embedUrl }}" target="_blank" rel="noopener noreferrer"
                           class="dossier-btn-secondary !bg-white/10 !border-white/30 !text-white hover:!bg-white/20 text-xs shrink-0">
                            Nouvel onglet
                        </a>
                    @endif
                </div>
                @if($embedUrl)
                    <iframe
                        src="{{ $embedUrl }}"
                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                        class="w-full min-h-[calc(100dvh-12rem)] sm:min-h-[78vh] border-0 bg-gray-900"
                        title="Salle de visioconférence DPC"></iframe>
                    <p class="px-4 py-3 text-xs text-gray-500 border-t border-gray-100">
                        Si la salle ne s’affiche pas (bloqueur, réseau), utilisez
                        <a href="{{ $embedUrl }}" target="_blank" rel="noopener noreferrer" class="dossier-link">ouvrir kMeet dans un nouvel onglet</a>.
                    </p>
                @else
                    <p class="p-6 text-sm text-gray-600">Impossible de charger la salle pour le moment (token ou configuration kMeet).</p>
                @endif
            </section>
        @endif
    </x-portal-page>

    @if($status === 'en_attente')
        <x-rencontre-visio-auto-refresh :token="$demande->visio_token" />
    @endif
</x-app-layout>
