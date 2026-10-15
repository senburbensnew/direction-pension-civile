@php
    $confirmation = $confirmation ?? [];
    $compact = (bool) ($compact ?? false);
    $visioLien = $confirmation['lien'] ?? null;
    $visioLienIsUrl = is_string($visioLien)
        && $visioLien !== ''
        && (str_starts_with($visioLien, 'http') || str_starts_with($visioLien, '/'));
    $visioDemande = $demande ?? null;
    $visioServiceConf = $visioDemande
        ? app(\App\Services\RencontreVisioService::class)
        : null;
    $visioStatusConf = ($visioServiceConf && filled($visioDemande->visio_token))
        ? $visioServiceConf->status($visioDemande)
        : null;
    $visioPageUrlConf = $visioDemande && filled($visioDemande->visio_token)
        ? ($visioDemande->visioPageUrl($visioBack ?? 'dossier'))
        : ($visioLienIsUrl ? $visioLien : null);
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
                    @if($visioStatusConf === 'actif' && $visioPageUrlConf)
                        <a href="{{ $visioPageUrlConf }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-sm font-semibold hover:bg-orange-600">
                            Rejoindre la visioconférence
                        </a>
                        <p class="text-xs text-gray-500 mt-2 font-normal">
                            Lien actif jusqu’à
                            {{ $visioServiceConf->slotEndsAt($visioDemande)?->format('d/m/Y à H:i') ?? '—' }}.
                        </p>
                    @elseif($visioStatusConf === 'en_attente' && $visioPageUrlConf)
                        <p class="text-sm text-amber-800">
                            Salle disponible à partir du
                            {{ $visioServiceConf->opensAt($visioDemande)?->format('d/m/Y à H:i') ?? '—' }}.
                        </p>
                        <a href="{{ $visioPageUrlConf }}"
                           class="mt-2 inline-block text-sm font-semibold text-navy hover:underline">
                            Voir la page visio
                        </a>
                    @elseif($visioStatusConf === 'termine' || ($visioDemande && $visioServiceConf?->isVisioLinkExpired($visioDemande)))
                        <span class="text-sm text-gray-500">Lien de visioconférence expiré.</span>
                    @elseif($visioLienIsUrl)
                        <span class="text-sm text-gray-500">Lien de visioconférence indisponible pour le moment.</span>
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
