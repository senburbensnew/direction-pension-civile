@if(session('rdv_recap'))
    @php $recap = session('rdv_recap'); @endphp
    <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8 space-y-5">
        <div>
        <h2 class="text-xl font-bold text-navy mb-1">Récapitulatif de votre rendez-vous</h2>
        <p class="text-sm text-gray-500 mb-5">Référence {{ $recap['code'] ?? '' }}</p>
        <dl class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3 bg-gray-50">
                <dt class="text-sm font-medium text-gray-500">Type de rendez-vous</dt>
                <dd class="sm:col-span-2 text-base text-navy font-semibold">{{ $recap['type'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3">
                <dt class="text-sm font-medium text-gray-500">Date et heure</dt>
                <dd class="sm:col-span-2 text-base text-gray-800">
                    {{ !empty($recap['date']) ? \Carbon\Carbon::parse($recap['date'])->format('d/m/Y') : '—' }}
                    @if(!empty($recap['heure'])) à {{ $recap['heure'] }}@endif
                </dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3 bg-gray-50">
                <dt class="text-sm font-medium text-gray-500">Lieu ou lien</dt>
                <dd class="sm:col-span-2 text-base text-gray-800">{{ $recap['lieu_ou_lien'] ?? '—' }}</dd>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3 bg-gray-50">
                <dt class="text-sm font-medium text-gray-500">Motif</dt>
                <dd class="sm:col-span-2 text-base text-gray-800">{{ $recap['motif'] ?? '—' }}</dd>
            </div>
        </dl>

        <div class="mt-5 space-y-4">
            @include('demandes.rencontre._documents-preparer-panel', [
                'documents' => $recap['documents'] ?? [],
                'modeLabel' => $recap['type'] ?? null,
                'panelId' => 'success',
                'showEnvoiHint' => true,
            ])
            @if(session('demande_creee_id'))
                @php $demandeRecap = \App\Models\Demande::find(session('demande_creee_id')); @endphp
                @if($demandeRecap)
                    @include('demandes.rencontre._documents-envoi-panel', [
                        'demande' => $demandeRecap,
                        'panelId' => 'success-envoi',
                    ])
                @endif
            @endif
        </div>
        </div>
    </section>
@endif
