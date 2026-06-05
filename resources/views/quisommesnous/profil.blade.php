@extends('layouts.main')

@section('title', 'Profil — '.$official->nom)

@section('content')
<section class="py-10 md:py-16 bg-gray-50">
    <div class="container mx-auto px-6 md:px-10 flex flex-col md:flex-row items-start gap-8">

        {{-- Sidebar: photo + links --}}
        <div class="md:w-1/3 lg:w-1/4 shrink-0">
            <x-presentation slug="{{ $official->slug }}" />
        </div>

        {{-- Main content --}}
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-200 p-8 md:p-12">

            <div class="mb-8 pb-6 border-b border-gray-200">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 font-serif mb-2">
                    Présentation — {{ $official->role }}
                </h1>
                <p class="text-gray-500">{{ $official->nom }}</p>
            </div>

            @if($official->hasBiographie())
                <div class="prose prose-lg max-w-none text-gray-700">
                    {!! $official->biographieHtml() !!}
                </div>

                <div class="mt-10 pt-6 border-t border-gray-200">
                    <p class="text-base font-semibold text-gray-900">
                        {{ $official->sexe === 'F' ? 'Mme' : 'M.' }} {{ $official->nom }}
                    </p>
                    <p class="text-gray-500 text-sm mt-0.5">{{ $official->role }}</p>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center text-gray-400">
                    <i class="fas fa-file-alt text-4xl mb-4 opacity-30"></i>
                    <p class="font-medium">Notice biographique à venir.</p>
                    <p class="text-sm mt-1">Ce contenu sera disponible prochainement.</p>
                </div>
            @endif

        </div>
    </div>
</section>
@endsection
