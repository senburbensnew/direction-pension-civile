@php
    $confirmation = $confirmation ?? [];
    $compact = (bool) ($compact ?? false);
    $visioLien = $confirmation['lien'] ?? null;
    $visioLienIsUrl = is_string($visioLien)
        && $visioLien !== ''
        && (str_starts_with($visioLien, 'http') || str_starts_with($visioLien, '/'));
@endphp
<section class="bg-white border border-gray-200 {{ $cardClass ?? '' }} p-6">
    <h3 class="text-base font-bold text-navy mb-1">{{ $title ?? 'Confirmation de rendez-vous' }}</h3>
    <p class="text-sm text-gray-500 mb-4">
        @if($compact)
            Numéro officiel, lieu le cas échéant, et pièces à préparer.
        @else
            Document généré à la validation par le service des Formalités.
        @endif
    </p>
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="text-gray-500">Numéro du rendez-vous</dt>
            <dd class="font-semibold text-navy">{{ $confirmation['numero'] ?? '—' }}</dd>
        </div>
        @unless($compact)
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
        @endunless
        @if(($confirmation['mode'] ?? '') === 'Présentiel')
            <div @class(['md:col-span-2' => ! $compact])>
                <dt class="text-gray-500">Lieu</dt>
                <dd class="font-medium">{{ $confirmation['lieu'] ?? '—' }}</dd>
            </div>
        @elseif(! $compact)
            <div class="md:col-span-2">
                <dt class="text-gray-500">Visioconférence</dt>
                <dd class="font-medium">
                    @if($visioLienIsUrl)
                        <a href="{{ $visioLien }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-sm font-semibold hover:bg-orange-600">
                            Rejoindre la visioconférence
                        </a>
                        <p class="text-xs text-gray-500 mt-2 font-normal">
                            Lien personnel — actif 15 minutes avant l’heure prévue du rendez-vous.
                        </p>
                    @else
                        {{ is_string($visioLien) && $visioLien !== '' ? $visioLien : 'Lien disponible après validation du rendez-vous.' }}
                    @endif
                </dd>
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
