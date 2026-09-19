@extends('layouts.main')

@section('title', $actu->title)

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <nav class="text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-navy">Accueil</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('actualites.index') }}" class="hover:text-navy">Actualités</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ Str::limit($actu->title, 55) }}</span>
        </nav>

        <article class="bg-white border border-gray-200 card-shadow overflow-hidden">
            <div class="w-full overflow-hidden bg-gray-50" style="aspect-ratio: 16/7;">
                <img src="{{ $actu->coverUrl() }}"
                     alt="{{ $actu->title }}"
                     class="w-full h-full object-cover">
            </div>

            <div class="p-6 sm:p-8">
                <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">
                    {{ $actu->category ?: 'Actualité' }}
                </span>
                <h1 class="text-2xl md:text-3xl font-bold text-navy mt-1 mb-4">{{ $actu->title }}</h1>

                <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-6">
                    <span>
                        <i class="fa-regular fa-calendar text-navy mr-1" aria-hidden="true"></i>
                        <time datetime="{{ $actu->created_at->toIso8601String() }}">
                            {{ $actu->created_at->translatedFormat('d F Y') }}
                        </time>
                    </span>
                    @if($actu->posted_in)
                        <span>
                            <i class="fa-solid fa-location-dot text-navy mr-1" aria-hidden="true"></i>
                            {{ $actu->posted_in }}
                        </span>
                    @endif
                    <span>
                        <i class="fa-regular fa-clock text-navy mr-1" aria-hidden="true"></i>
                        Mise à jour le {{ $actu->updated_at->translatedFormat('d F Y') }}
                    </span>
                </div>

                @if($actu->description)
                    <p class="text-gray-700 leading-relaxed border-l-4 border-orange-500 pl-4 mb-6">
                        {{ $actu->description }}
                    </p>
                @endif

                @if($actu->images->count() > 1)
                    <div class="mb-7 overflow-hidden border border-gray-200">
                        <x-carousel>
                            @foreach($actu->images as $image)
                                <div class="swiper-slide">
                                    <img src="{{ $image->url() }}"
                                         class="w-full h-64 md:h-80 object-cover"
                                         alt="Photo — {{ $actu->title }}" />
                                </div>
                            @endforeach
                        </x-carousel>
                    </div>
                @endif

                <div class="text-gray-700 leading-relaxed">
                    @if($actu->content_text)
                        {!! nl2br(e($actu->content_text)) !!}
                    @elseif(!$actu->description)
                        <p class="text-gray-400 italic">Aucun contenu disponible.</p>
                    @endif
                </div>
            </div>

            <div class="px-6 sm:px-8 py-4 border-t border-gray-200 flex items-center justify-between">
                <a href="{{ route('actualites.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 border border-gray-200 text-navy font-medium hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Retour aux actualités
                </a>
            </div>
        </article>

    </div>
</div>
@endsection
