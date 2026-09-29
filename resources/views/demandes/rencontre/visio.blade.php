@extends('layouts.main')

@section('title', 'Visioconférence — '.$demande->code)

@section('content')
@php
    $data = $demande->data ?? [];
@endphp
<div class="py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto space-y-5">

        {{-- Fil d'Ariane --}}
        <nav class="flex items-center gap-1.5 text-xs text-gray-400 px-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            @if(auth()->id() === $demande->created_by)
                <a href="{{ route('personal.dashboard') }}" class="hover:text-gray-600 transition-colors">Mes demandes</a>
            @else
                <a href="{{ route('personal.cart') }}" class="hover:text-gray-600 transition-colors">Corbeille</a>
            @endif
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-500 font-medium">Dossier #{{ $demande->code }}</span>
            <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
            <span class="text-gray-500 font-medium">Visioconférence</span>
        </nav>

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
            <a href="{{ $embedUrl }}" target="_blank"
                class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg">
                    Ouvrir la salle dans un nouvel onglet
                </a>
        @endif
    </div>
</div>

{{-- Rafraîchissement automatique tant que le lien n'est pas actif --}}
@if($status === 'en_attente')
    <script>
        setTimeout(() => window.location.reload(), 30000);
    </script>
@endif

@endsection