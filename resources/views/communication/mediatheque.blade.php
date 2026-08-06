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
        'image'    => ['label' => 'Image',    'icon' => 'fa-image',    'color' => 'bg-green-100 text-green-700'],
        'video'    => ['label' => 'Vidéo',    'icon' => 'fa-video',    'color' => 'bg-purple-100 text-purple-700'],
        'audio'    => ['label' => 'Audio',    'icon' => 'fa-music',    'color' => 'bg-yellow-100 text-yellow-700'],
        'document' => ['label' => 'Document', 'icon' => 'fa-file-alt', 'color' => 'bg-red-100 text-red-700'],
    ];
@endphp

<style>
    .media-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .media-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #3b82f6, #1e40af); transform: scaleY(0); transition: transform 0.3s ease; }
    .media-card:hover::before { transform: scaleY(1); }
    .media-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,.15); }
    .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0,0,0,.1); }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .tab-btn.active { background: #3b82f6; color: #fff; }
    .media-card[hidden] { display: none !important; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in"
     x-data="{ lbOpen: false, lbSrc: '', lbAlt: '' }"
     @keydown.escape.window="lbOpen = false">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold gradient-text mb-3">Médiathèque</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Ressources multimédias de la Direction de la Pension Civile : images, vidéos, audios et documents.
        </p>
    </div>

    {{-- Featured video --}}
    @if($featured && ($featured->embedUrl() || $featured->fileUrl()))
        <div class="bg-white rounded-2xl border border-gray-200 card-shadow p-5 sm:p-6 mb-8">
            <div class="flex items-center gap-2 mb-4">
                <span class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                    <i class="fas fa-star"></i>
                </span>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Contenu en vedette</h2>
                    <p class="text-xs text-gray-500">{{ $featured->title }}</p>
                </div>
            </div>
            <div class="max-w-4xl mx-auto">
                @if($featured->embedUrl())
                    <div class="relative rounded-xl overflow-hidden bg-black shadow-md">
                        <iframe
                            class="w-full aspect-video"
                            src="{{ $featured->embedUrl() }}"
                            title="{{ $featured->title }}"
                            loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            style="border:0;">
                        </iframe>
                    </div>
                @elseif($featured->fileUrl())
                    <video controls class="w-full rounded-xl aspect-video bg-black shadow-md">
                        <source src="{{ $featured->fileUrl() }}">
                    </video>
                @endif
            </div>
        </div>
    @endif

    {{-- Search / filter bar --}}
    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-4 mb-8">
        <div class="flex flex-col sm:flex-row gap-3 mb-4">
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input type="text" id="media-search" placeholder="Rechercher un média…"
                       class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="button" id="media-search-clear"
                    class="hidden px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm rounded-lg flex items-center justify-center gap-1.5">
                <i class="fas fa-times text-xs"></i> Effacer
            </button>
        </div>

        <div class="flex flex-wrap gap-2" id="media-tabs">
            <button type="button" class="tab-btn active px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="all">
                Tous <span class="opacity-70">({{ $allItems->count() }})</span>
            </button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="image">
                <i class="fas fa-image mr-1"></i> Images <span class="opacity-70">({{ $images->count() }})</span>
            </button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="video">
                <i class="fas fa-video mr-1"></i> Vidéos <span class="opacity-70">({{ $videos->count() }})</span>
            </button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="audio">
                <i class="fas fa-music mr-1"></i> Audios <span class="opacity-70">({{ $audios->count() }})</span>
            </button>
            <button type="button" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="document">
                <i class="fas fa-file-alt mr-1"></i> Documents <span class="opacity-70">({{ $documents->count() }})</span>
            </button>
        </div>
    </div>

    {{-- Grid --}}
    <div id="media-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($allItems as $entry)
            @php
                $item = $entry['item'];
                $kind = $entry['kind'];
                $meta = $typeMeta[$kind];
                $search = strtolower(($item->title ?? '') . ' ' . ($item->description ?? ''));
            @endphp
            <div class="media-card bg-white border border-gray-200 rounded-xl card-shadow flex flex-col"
                 data-type="{{ $kind }}"
                 data-searchable="{{ $search }}">

                @if($kind === 'image' && $item->fileUrl())
                    <div class="relative h-44 bg-gray-50 cursor-zoom-in group flex items-center justify-center p-3"
                         @click="lbOpen = true; lbSrc = '{{ $item->fileUrl() }}'; lbAlt = '{{ addslashes($item->title) }}'">
                        <img src="{{ $item->fileUrl() }}" alt="{{ $item->title }}"
                             class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105 rounded">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/15 transition-colors flex items-center justify-center">
                            <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 text-xl drop-shadow"></i>
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
                        <div class="aspect-video bg-purple-50 flex items-center justify-center">
                            <i class="fas fa-video text-purple-300 text-3xl"></i>
                        </div>
                    @endif
                @elseif($kind === 'audio')
                    <div class="h-28 bg-gradient-to-br from-yellow-50 to-amber-50 flex items-center justify-center">
                        <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-music text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                @else
                    <div class="h-28 bg-gradient-to-br from-red-50 to-rose-50 flex items-center justify-center">
                        <div class="w-14 h-14 bg-red-100 rounded-xl flex items-center justify-center">
                            <i class="fas fa-file-alt text-red-600 text-xl"></i>
                        </div>
                    </div>
                @endif

                <div class="p-4 flex flex-col flex-1">
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h3 class="font-semibold text-gray-800 text-sm leading-snug">{{ $item->title }}</h3>
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full {{ $meta['color'] }} whitespace-nowrap flex-shrink-0">
                            <i class="fas {{ $meta['icon'] }} mr-0.5"></i> {{ $meta['label'] }}
                        </span>
                    </div>

                    @if($item->description)
                        <p class="text-gray-500 text-xs leading-relaxed mb-3">{{ Str::limit($item->description, 100) }}</p>
                    @endif

                    <div class="mt-auto">
                        @if($kind === 'audio' && $item->fileUrl())
                            <audio controls class="w-full" preload="metadata">
                                <source src="{{ $item->fileUrl() }}">
                            </audio>
                        @elseif($kind === 'audio' && $item->url)
                            <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1.5 text-yellow-700 text-sm font-medium hover:underline">
                                <i class="fas fa-external-link-alt"></i> Écouter
                            </a>
                        @elseif($kind === 'document')
                            <div class="flex flex-wrap gap-2">
                                @if($item->fileUrl())
                                    <a href="{{ $item->fileUrl() }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium">
                                        <i class="fas fa-eye"></i> Voir
                                    </a>
                                    <a href="{{ $item->fileUrl() }}" download
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium">
                                        <i class="fas fa-download"></i> Télécharger
                                    </a>
                                @elseif($item->url)
                                    <a href="{{ $item->url }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 text-blue-600 text-sm font-medium hover:underline">
                                        <i class="fas fa-external-link-alt"></i> Ouvrir
                                    </a>
                                @endif
                            </div>
                        @elseif($kind === 'video' && $item->url && !$item->embedUrl())
                            <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1.5 text-purple-600 text-sm font-medium hover:underline">
                                <i class="fas fa-play-circle"></i> Voir la vidéo
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div id="media-empty" class="{{ $allItems->isEmpty() ? '' : 'hidden' }} bg-white rounded-2xl card-shadow p-16 text-center text-gray-400">
        <i class="fas fa-photo-film text-5xl mb-4 block"></i>
        <p class="font-medium">Aucun média disponible pour le moment.</p>
    </div>

    {{-- Lightbox --}}
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
            <button @click="lbOpen = false"
                    class="absolute -top-3 -right-3 z-10 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:bg-gray-100">
                <i class="fas fa-times text-gray-600 text-sm"></i>
            </button>
            <img :src="lbSrc" :alt="lbAlt" class="w-full h-auto rounded-xl shadow-2xl block max-h-[85vh] object-contain">
            <p class="text-white/80 text-sm text-center mt-3" x-text="lbAlt"></p>
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
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
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
