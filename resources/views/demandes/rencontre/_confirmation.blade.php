@php
    $confirmation = $confirmation ?? [];
@endphp
<section class="bg-white border border-gray-200 {{ $cardClass ?? 'rounded-2xl' }} p-6">
    <h3 class="text-base font-bold text-navy mb-1">{{ $title ?? 'Confirmation de rendez-vous' }}</h3>
    <p class="text-sm text-gray-500 mb-4">Document généré à la validation par le service des Formalités.</p>
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500">Numéro du rendez-vous</dt>
            <dd class="font-semibold text-navy">{{ $confirmation['numero'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Date et heure</dt>
            <dd class="font-medium">
                {{ !empty($confirmation['date']) ? \Carbon\Carbon::parse($confirmation['date'])->format('d/m/Y') : '—' }}
                @if(!empty($confirmation['heure'])) à {{ $confirmation['heure'] }}@endif
            </dd>
        </div>
        <div>
            <dt class="text-gray-500">Service responsable</dt>
            <dd class="font-medium">{{ $confirmation['service'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Mode de rendez-vous</dt>
            <dd class="font-medium">{{ $confirmation['mode'] ?? '—' }}</dd>
        </div>
        @if(($confirmation['mode'] ?? '') === 'Présentiel')
            <div class="md:col-span-2">
                <dt class="text-gray-500">Lieu</dt>
                <dd class="font-medium">{{ $confirmation['lieu'] ?? '—' }}</dd>
            </div>
        @else
            <div class="md:col-span-2">
                <dt class="text-gray-500">Lien sécurisé</dt>
                <dd class="font-medium break-all">{{ $confirmation['lien'] ?? '—' }}</dd>
            </div>
        @endif
        <div class="md:col-span-2">
            <dt class="text-gray-500 mb-1">Pièces à apporter ou à préparer</dt>
            <dd>
                <ul class="list-disc list-inside text-gray-800 space-y-1">
                    @foreach(($confirmation['pieces'] ?? []) as $piece)
                        <li>{{ $piece }}</li>
                    @endforeach
                </ul>
            </dd>
        </div>
    </dl>
</section>
