@extends('layouts.main')

@section('title', 'Visioconférence — '.$demande->code)

@section('content')
@php
    $data = $demande->data ?? [];
@endphp
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-5">
        <div>
            <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">Rendez-vous sécurisé</p>
            <h1 class="text-3xl font-bold text-navy mt-1">Visioconférence {{ $demande->code }}</h1>
            <p class="text-gray-600 mt-1">
                {{ $data['objet'] ?? 'Rendez-vous' }}
                @if($startsAt)
                    · {{ $startsAt->format('d/m/Y à H:i') }}
                @endif
            </p>
        </div>

        @if($status === 'en_attente')
            <section class="bg-white border border-gray-200 p-6 sm:p-8">
                <h2 class="text-xl font-bold text-navy">Lien pas encore actif</h2>
                <p class="text-gray-600 mt-2">
                    Cette salle s’ouvre {{ (int) config('rdv.visio.activate_minutes_before', 15) }} minutes avant l’heure du rendez-vous.
                </p>
                @if($opensAt)
                    <p class="mt-3 text-navy font-medium">Ouverture : {{ $opensAt->format('d/m/Y à H:i') }}</p>
                @endif
            </section>
        @elseif($status === 'termine')
            <section class="bg-white border border-gray-200 p-6 sm:p-8">
                <h2 class="text-xl font-bold text-navy">Rendez-vous terminé</h2>
                <p class="text-gray-600 mt-2">Le lien sécurisé n’est plus actif.</p>
            </section>
        @else
            <section class="bg-white border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 bg-navy text-white text-sm">
                    Salle réservée à l’usager et à l’agent désigné.
                </div>
                @if($embedUrl)
                    <iframe
                        src="{{ $embedUrl }}"
                        allow="camera; microphone; fullscreen; display-capture; autoplay"
                        class="w-full h-[70vh] border-0"
                        title="Salle de visioconférence DPC"></iframe>
                @endif
            </section>
        @endif
    </div>
</div>
@endsection
