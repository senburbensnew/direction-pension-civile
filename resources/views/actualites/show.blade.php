@extends('layouts.main')

@section('title', $actu->title)

@section('content')

{{-- Breadcrumb --}}
<div class="bg-white border-b border-gray-100">
    <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-blue-600 transition-colors">Accueil</a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <a href="{{ route('actualites.index') }}" class="hover:text-blue-600 transition-colors">Actualités</a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium truncate max-w-xs">{{ Str::limit($actu->title, 55) }}</span>
        </nav>
    </div>
</div>

<div class="bg-gray-50 min-h-screen py-10">
    <div class="container mx-auto px-4 max-w-4xl">

        <article class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">

            {{-- Hero image --}}
            <div class="relative w-full bg-gray-100 overflow-hidden" style="aspect-ratio: 16/7;">
                <img src="{{ $actu->images->isNotEmpty() ? Storage::url($actu->images->first()->image_path) : asset('images/image_placeholder.png') }}"
                     alt="{{ $actu->title }}"
                     class="w-full h-full object-cover">
                {{-- Gradient overlay for meta readability --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
                @if($actu->category)
                    <span class="absolute top-4 left-4 px-3 py-1 bg-blue-600 text-white text-sm font-semibold rounded-full shadow">
                        {{ $actu->category }}
                    </span>
                @endif
            </div>

            {{-- Article header --}}
            <div class="px-8 pt-8 pb-6 border-b border-gray-100">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 leading-tight mb-5">
                    {{ $actu->title }}
                </h1>
                <div class="flex flex-wrap items-center gap-5 text-sm text-gray-500">
                    <span class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-blue-50 flex items-center justify-center">
                            <i class="far fa-calendar-alt text-blue-500 text-xs"></i>
                        </span>
                        <time datetime="{{ $actu->created_at->toIso8601String() }}">
                            {{ $actu->created_at->translatedFormat('d F Y') }}
                        </time>
                    </span>
                    @if($actu->posted_in)
                        <span class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-full bg-blue-50 flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-blue-500 text-xs"></i>
                            </span>
                            {{ $actu->posted_in }}
                        </span>
                    @endif
                    <span class="flex items-center gap-2">
                        <span class="w-7 h-7 rounded-full bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-clock text-blue-500 text-xs"></i>
                        </span>
                        Mise à jour le {{ $actu->updated_at->translatedFormat('d F Y') }}
                    </span>
                </div>
            </div>

            {{-- Lead paragraph --}}
            @if($actu->description)
                <div class="px-8 pt-7 pb-0">
                    <p class="text-base md:text-lg text-gray-600 leading-relaxed font-medium border-l-4 border-blue-500 pl-5 italic">
                        {{ $actu->description }}
                    </p>
                </div>
            @endif

            {{-- Additional images (if more than one) --}}
            @if($actu->images->count() > 1)
                <div class="mx-8 my-7 rounded-xl overflow-hidden border border-gray-100">
                    <x-carousel>
                        @foreach($actu->images as $image)
                            <div class="swiper-slide">
                                <img src="{{ Storage::url($image->image_path) }}"
                                     class="w-full h-64 md:h-80 object-cover"
                                     alt="Photo — {{ $actu->title }}" />
                            </div>
                        @endforeach
                    </x-carousel>
                </div>
            @endif

            {{-- Main content --}}
            <div class="px-8 py-7">
                <div class="prose prose-gray prose-base max-w-none text-gray-700 leading-relaxed">
                    @if($actu->content_text)
                        {!! nl2br(e($actu->content_text)) !!}
                    @elseif(!$actu->description)
                        <p class="text-gray-400 italic">Aucun contenu disponible.</p>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <a href="{{ route('actualites.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-arrow-left text-xs"></i>
                    Retour aux actualités
                </a>
                <div class="flex items-center gap-2 text-xs text-gray-400">
                    <i class="fas fa-share-alt"></i>
                    <span>Partager</span>
                </div>
            </div>

        </article>

    </div>
</div>

@endsection
