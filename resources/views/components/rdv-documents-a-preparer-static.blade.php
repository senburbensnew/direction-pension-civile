@props([
    'motifKey' => 'demande_formalites_mandats',
    'modalite' => 'visio',
])

@php
    $hints = config("rencontre.motifs.{$motifKey}.prepare.{$modalite}", []);
    $hints = is_array($hints) ? array_values($hints) : [];
    $motifLabel = config("rencontre.motifs.{$motifKey}.label", $motifKey);
@endphp

@if($hints !== [])
    <div {{ $attributes->merge(['class' => 'rounded-xl border border-indigo-100 bg-indigo-50/70 p-4']) }}>
        <h4 class="text-sm font-bold text-gray-900">
            Documents à préparer pour le rendez-vous
        </h4>
        <p class="text-xs text-gray-600 mt-1">
            Motif : <strong>{{ $motifLabel }}</strong>
            — pensez à ces pièces avant votre passage ou votre visioconférence.
        </p>
        <ul class="mt-3 space-y-1.5 text-sm text-gray-800 list-disc list-inside">
            @foreach($hints as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ul>
    </div>
@endif
