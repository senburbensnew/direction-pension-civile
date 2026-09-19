@extends('layouts.main')

@section('title', 'Profil — '.$official->nom)

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

@php
    $role = trim($official->role);
    $roleLower = mb_strtolower($role);
    if (str_starts_with($roleLower, 'le ')) {
        $pageTitle = 'Profil du '.mb_substr($role, 3);
    } elseif (str_starts_with($roleLower, 'la ')) {
        $pageTitle = 'Profil de la '.mb_substr($role, 3);
    } elseif (str_starts_with($roleLower, 'l’') || str_starts_with($roleLower, "l'")) {
        $pageTitle = 'Profil de '.$role;
    } else {
        $pageTitle = 'Profil '.($official->sexe === 'F' ? 'de la' : 'du').' '.$role;
    }
    $avatar = 'https://ui-avatars.com/api/?name='.urlencode($official->nom).'&background=173052&color=fff';
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Qui sommes-nous</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">{{ $pageTitle }}</h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] gap-6 items-start">
            <aside class="bg-white border border-gray-200 card-shadow p-6 text-center">
                <h2 class="text-lg font-bold text-navy mb-4">{{ $official->role }}</h2>
                <img src="{{ $official->photoUrl() }}"
                     alt="{{ $official->nom }}"
                     class="w-full h-auto max-h-96 object-contain bg-white mb-4"
                     onerror="this.onerror=null; this.src='{{ $avatar }}';">
                <p class="font-semibold text-navy">{{ $official->nom }}</p>
                <a href="{{ route('quisommesnous.mots', ['role' => $official->slug]) }}"
                   class="inline-flex items-center gap-1.5 mt-3 text-base font-medium text-navy hover:text-orange-500">
                    {{ __('messages.speech') }}
                    <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                </a>
            </aside>

            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                    </span>
                    <h2 class="text-xl font-bold text-navy">{{ $pageTitle }}</h2>
                </div>

                @if($official->hasBiographie())
                    <div class="prose max-w-none text-gray-700 leading-relaxed">
                        {!! $official->biographieHtml() !!}
                    </div>
                    <p class="mt-8 text-base font-semibold text-navy">
                        {{ $official->sexe === 'F' ? 'Mme' : 'M.' }} {{ $official->nom }}
                    </p>
                    <p class="text-gray-600 text-base mt-0.5">{{ $official->role }}</p>
                @else
                    <div class="py-10 text-center text-gray-500">
                        <i class="fa-solid fa-file-lines text-navy mb-3" aria-hidden="true"></i>
                        <p class="font-medium text-navy">Notice biographique à venir.</p>
                        <p class="text-base mt-1">Ce contenu sera disponible prochainement.</p>
                    </div>
                @endif
            </section>
        </div>

    </div>
</div>
@endsection
