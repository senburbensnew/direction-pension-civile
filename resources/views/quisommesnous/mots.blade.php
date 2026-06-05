@extends('layouts.main')

@section('title', 'Mots — '.$official->nom)

@section('content')
<div class="flex flex-col md:flex-row items-start gap-8 md:gap-12 p-6 md:p-8 py-10 md:py-16">

    {{-- Sidebar: photo + links --}}
    <div class="shrink-0">
        <x-presentation slug="{{ $official->slug }}" />
    </div>

    {{-- Main content --}}
    <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-200 p-8 md:p-12">

        <div class="mb-8 pb-6 border-b border-gray-200">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 font-serif tracking-tight mb-3">
                Mots {{ $official->sexe === 'F' ? 'de la' : 'du' }} {{ $official->role }}
            </h2>
            @if($official->citation)
                <p class="text-lg text-gray-500 italic">
                    "{{ $official->citation }}"
                </p>
            @endif
        </div>

        @if($official->hasDiscours())
            <div class="prose prose-lg max-w-none text-gray-700">
                {!! $official->discoursHtml() !!}
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center text-gray-400">
                <i class="fas fa-microphone-slash text-4xl mb-4 opacity-30"></i>
                <p class="font-medium">Aucun discours disponible pour le moment.</p>
                <p class="text-sm mt-1">Ce contenu sera disponible prochainement.</p>
            </div>
        @endif

    </div>
</div>
@endsection
