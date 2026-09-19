@extends('layouts.main')

@section('title', 'Médiathèque')

@section('content')
@php
    $allItems = collect()
        ->merge($images->map(fn ($i) => ['item' => $i, 'kind' => 'image']))
        ->merge($videos->map(fn ($i) => ['item' => $i, 'kind' => 'video']))
        ->merge($audios->map(fn ($i) => ['item' => $i, 'kind' => 'audio']))
        ->merge($documents->map(fn ($i) => ['item' => $i, 'kind' => 'document']));

    $typeMeta = [
        'image'    => ['label' => 'Image',    'icon' => 'fa-image'],
        'video'    => ['label' => 'Vidéo',    'icon' => 'fa-video'],
        'audio'    => ['label' => 'Audio',    'icon' => 'fa-music'],
        'document' => ['label' => 'Document', 'icon' => 'fa-file-lines'],
    ];
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .tab-btn.is-active { background: #173052; color: #fff; }
    .media-card[hidden] { display: none !important; }
    [x-cloak] { display: none !important; }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8"
     x-data="{ lbOpen: false, lbSrc: '', lbAlt: '' }"
     @keydown.escape.window="lbOpen = false">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Communications</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Médiathèque</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Ressources multimédias de la Direction de la Pension Civile : images, vidéos, audios et documents.
            </p>
        </div>

        @if($featured && ($featured->embedUrl() || $featured->fileUrl()))
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-5">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-star" aria-hidden="true"></i>
                    </span>
                    <div>
                        <h2 class="text-xl font-bold text-navy">Contenu en vedette</h2>
                        <p class="text-sm text-gray-500">{{ $featured->title }}</p>
                    </div>
                </div>
                <div class="max-w-4xl mx-auto border border-gray-200 overflow-hidden bg-black">
                    @if($featured->embedUrl())
                        <iframe
                            class="w-full aspect-video"
                            src="{{ $featured->embedUrl() }}"
                            title="{{ $featured->title }}"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            style="border:0;">
                        </iframe>
                    @elseif($featured->fileUrl())
                        <video controls class="w-full aspect-video bg-black">
                            <source src="{{ $featured->fileUrl() }}">
                        </video>
                    @endif
                </div>
            </section>
        @endif

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Rechercher un média</h2>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 mb-5">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input type="text" id="media-search" placeholder="Rechercher un média…"
                           class="w-full pl-9 pr-4 py-2.5 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <button type="button" id="media-search-clear"
                        class="hidden px-4 py-2.5 border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-xmark text-xs"></i> Effacer
                </button>
            </div>
            <div class="flex flex-wrap gap-2" id="media-tabs">
                <button type="button" class="tab-btn is-active px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="all">
                    Tous <span class="opacity-70">({{ $allItems->count() }})</span>
                </button>
                <button type="button" class="tab-btn px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition" data-tab="image">
                    Images <span class="opacity-70">({{ $images->count() }})</span>
                </button>
                <button type="button" class="tab-btn px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition" data-tab="video">
                    Vidéos <span class="opacity-70">({{ $videos->count() }})</span>
                </button>
                <button type="button" class="tab-btn px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition" data-tab="audio">
                    Audios <span class="opacity-70">({{ $audios->count() }})</span>
                </button>
                <button type="button" class="tab-btn px-4 py-2 text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition" data-tab="document">
                    Documents <span class="opacity-70">({{ $documents->count() }})</span>
                </button>
            </div>
        </section>

        <div id="media-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($allItems as $entry)
                @php
                    $item = $entry['item'];
                    $kind = $entry['kind'];
                    $meta = $typeMeta[$kind];
                    $search = strtolower(($item->title ?? '') . ' ' . ($item->description ?? ''));
                @endphp
                <article class="media-card bg-white border border-gray-200 card-shadow flex flex-col"
                         data-type="{{ $kind }}"
                         data-searchable="{{ $search }}">

                    @if($kind === 'image' && $item->fileUrl())
                        <div class="relative h-44 bg-gray-50 cursor-zoom-in group flex items-center justify-center p-3"
                             @click="lbOpen = true; lbSrc = '{{ $item->fileUrl() }}'; lbAlt = '{{ addslashes($item->title) }}'">
                            <img src="{{ $item->fileUrl() }}" alt="{{ $item->title }}"
                                 class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors flex items-center justify-center">
                                <i class="fa-solid fa-expand text-white opacity-0 group-hover:opacity-100 text-xl drop-shadow"></i>
                            </div>
                        </div>
                    @elseif($kind === 'video')
                        @if($item->embedUrl())
                            <div class="aspect-video bg-black">
                                <iframe class="w-full h-full" src="{{ $item->embedUrl() }}" title="{{ $item->title }}"
                                    loading="lazy" allowfullscreen style="border:0;"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                            </div>
                        @elseif($item->fileUrl())
                            <video controls class="w-full aspect-video bg-black">
                                <source src="{{ $item->fileUrl() }}">
                            </video>
                        @else
                            <div class="aspect-video bg-gray-50 flex items-center justify-center">
                                <i class="fa-solid fa-video text-navy text-3xl"></i>
                            </div>
                        @endif
                    @elseif($kind === 'audio')
                        <div class="h-28 bg-gray-50 flex items-center justify-center">
                            <i class="fa-solid fa-music text-navy text-2xl"></i>
                        </div>
                    @else
                        <div class="h-28 bg-gray-50 flex items-center justify-center">
                            <i class="fa-solid fa-file-lines text-navy text-2xl"></i>
                        </div>
                    @endif

                    <div class="p-5 flex flex-col flex-1">
                        <p class="text-[11px] font-bold text-orange-500 uppercase tracking-widest mb-2">
                            {{ $meta['label'] }}
                        </p>
                        <h3 class="font-bold text-navy text-base leading-snug mb-2">{{ $item->title }}</h3>

                        @if($item->description)
                            <p class="text-gray-700 text-sm leading-relaxed mb-3">{{ Str::limit($item->description, 100) }}</p>
                        @endif

                        <div class="mt-auto">
                            @if($kind === 'audio' && $item->fileUrl())
                                <audio controls class="w-full" preload="metadata">
                                    <source src="{{ $item->fileUrl() }}">
                                </audio>
                            @elseif($kind === 'audio' && $item->url)
                                <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1.5 text-navy text-sm font-medium hover:text-orange-500">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Écouter
                                </a>
                            @elseif($kind === 'document')
                                <div class="flex flex-wrap gap-2">
                                    @if($item->fileUrl())
                                        <a href="{{ $item->fileUrl() }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-navy text-white text-xs font-medium hover:opacity-90">
                                            <i class="fa-solid fa-eye"></i> Voir
                                        </a>
                                        <a href="{{ $item->fileUrl() }}" download
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 text-navy text-xs font-medium hover:bg-gray-50">
                                            <i class="fa-solid fa-download"></i> Télécharger
                                        </a>
                                    @elseif($item->url)
                                        <a href="{{ $item->url }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 text-navy text-sm font-medium hover:text-orange-500">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i> Ouvrir
                                        </a>
                                    @endif
                                </div>
                            @elseif($kind === 'video' && $item->url && !$item->embedUrl())
                                <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1.5 text-navy text-sm font-medium hover:text-orange-500">
                                    <i class="fa-solid fa-circle-play"></i> Voir la vidéo
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div id="media-empty" class="{{ $allItems->isEmpty() ? '' : 'hidden' }} bg-white border border-gray-200 card-shadow p-12 text-center text-gray-500">
            <i class="fa-solid fa-photo-film text-3xl text-navy mb-4 block"></i>
            <p>Aucun média disponible pour le moment.</p>
        </div>

        <div x-show="lbOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.self="lbOpen = false"
             class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 p-4">
            <div class="relative max-w-4xl w-full">
                <button type="button" @click="lbOpen = false"
                        class="absolute -top-3 -right-3 z-10 bg-white w-8 h-8 flex items-center justify-center shadow-lg hover:bg-gray-100">
                    <i class="fa-solid fa-xmark text-navy text-sm"></i>
                </button>
                <img :src="lbSrc" :alt="lbAlt" class="w-full h-auto block max-h-[85vh] object-contain">
                <p class="text-white/80 text-sm text-center mt-3" x-text="lbAlt"></p>
            </div>
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-btn');
        const cards = document.querySelectorAll('#media-grid .media-card');
        const empty = document.getElementById('media-empty');
        const searchEl = document.getElementById('media-search');
        const clearBtn = document.getElementById('media-search-clear');
        let activeTab = 'all';

        function applyFilters() {
            const q = (searchEl.value || '').trim().toLowerCase();
            clearBtn.classList.toggle('hidden', q === '');
            let visible = 0;

            cards.forEach(card => {
                const type = card.getAttribute('data-type');
                const text = card.getAttribute('data-searchable') || '';
                const show = (activeTab === 'all' || type === activeTab) && (q === '' || text.includes(q));
                card.hidden = !show;
                if (show) visible++;
            });

            empty.classList.toggle('hidden', visible > 0);
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                tabs.forEach(t => t.classList.remove('is-active'));
                this.classList.add('is-active');
                activeTab = this.getAttribute('data-tab');
                applyFilters();
            });
        });

        searchEl.addEventListener('input', applyFilters);
        clearBtn.addEventListener('click', () => {
            searchEl.value = '';
            applyFilters();
            searchEl.focus();
        });

        applyFilters();
    });
</script>
@endsection
