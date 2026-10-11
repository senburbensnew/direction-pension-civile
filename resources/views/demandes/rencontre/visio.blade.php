<x-app-layout>
    @php
        $data = $demande->data ?? [];
    @endphp

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-5">

        <nav class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500" aria-label="Fil d'Ariane">
            @if(auth()->id() === $demande->created_by)
                <a href="{{ route('demandes.rencontre.create') }}" class="text-navy font-medium hover:underline">Rendez-vous</a>
            @else
                <a href="{{ route('personal.cart') }}" class="text-navy font-medium hover:underline">Corbeille</a>
            @endif
            <span class="text-gray-300" aria-hidden="true">/</span>
            <a href="{{ $backUrl }}"
               class="text-navy font-medium hover:underline truncate max-w-[14rem] sm:max-w-none">
                {{ $demande->libellePublic() }}
            </a>
            <span class="text-gray-300" aria-hidden="true">/</span>
            <span class="text-gray-800 font-semibold">Visioconférence</span>
        </nav>

        <div>
            <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">Rendez-vous sécurisé</p>
            <h1 class="text-3xl font-bold text-navy mt-1">Visioconférence</h1>
            <p class="text-gray-600 mt-1">
                {{ $data['objet'] ?? $data['motif'] ?? 'Rendez-vous' }}
                @if($startsAt)
                    · {{ $startsAt->format('d/m/Y à H:i') }}
                @endif
            </p>
        </div>

        @if($status === 'en_attente')
            <section class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl font-bold text-navy">Lien pas encore actif</h2>
                <p class="text-gray-600 mt-2">
                    Cette salle s’ouvre {{ (int) config('rdv.visio.activate_minutes_before', 15) }} minutes avant l’heure du rendez-vous.
                </p>
                @if($opensAt)
                    <p class="mt-3 text-navy font-medium">Ouverture : {{ $opensAt->format('d/m/Y à H:i') }}</p>
                @endif
                <p class="mt-3 text-xs text-gray-500">Cette page se met à jour automatiquement dès l’ouverture de la salle.</p>
            </section>
        @elseif($status === 'termine')
            <section class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm">
                <h2 class="text-xl font-bold text-navy">Rendez-vous terminé</h2>
                <p class="text-gray-600 mt-2">Le lien sécurisé n’est plus actif.</p>
            </section>
        @else
            <section class="bg-white border border-gray-200 rounded-2xl p-6 sm:p-8 shadow-sm space-y-4">
                <p class="text-sm text-gray-600">
                    La salle est ouverte. Rejoignez la visioconférence sécurisée de la Direction de la Pension Civile.
                </p>
                <a href="{{ $embedUrl }}" target="_blank" rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-navy text-white text-sm font-semibold rounded-lg hover:opacity-90 transition">
                    <i class="fa-solid fa-video" aria-hidden="true"></i>
                    Ouvrir la salle dans un nouvel onglet
                </a>
            </section>
        @endif
    </div>

    @if($status === 'en_attente')
        <x-rencontre-visio-auto-refresh :token="$demande->visio_token" />
    @endif
</x-app-layout>
