@extends('layouts.main')

@section('title', 'Médiathèque')

@section('content')
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
    .media-section { display: none; }
    .media-section.active { display: block; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in"
     x-data="{ lbOpen: false, lbSrc: '', lbAlt: '' }"
     @keydown.escape.window="lbOpen = false">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold gradient-text mb-3">Médiathèque</h1>
        <p class="text-gray-600 text-lg">Accédez à nos ressources multimédias : images, vidéos et audios.</p>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-4 mb-8 flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" id="media-search" placeholder="Rechercher dans la médiathèque…"
                   class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button id="media-search-clear"
                class="hidden px-4 py-2 border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm rounded-lg flex items-center gap-1.5">
            <i class="fas fa-times text-xs"></i> Effacer
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex flex-wrap gap-2 mb-8 justify-center">
        <button class="tab-btn active px-5 py-2.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="images">
            <i class="fas fa-image mr-1.5"></i> Images ({{ $images->count() }})
        </button>
        <button class="tab-btn px-5 py-2.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="videos">
            <i class="fas fa-video mr-1.5"></i> Vidéos ({{ $videos->count() }})
        </button>
        <button class="tab-btn px-5 py-2.5 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-tab="audios">
            <i class="fas fa-music mr-1.5"></i> Audios ({{ $audios->count() }})
        </button>
    </div>

    {{-- Images --}}
    <div id="tab-images" class="media-section active">
        @if($images->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4" id="grid-images">
                @foreach($images as $item)
                    <div class="media-card rounded-xl overflow-hidden border border-gray-200 card-shadow bg-gray-50"
                         data-searchable="{{ strtolower($item->title . ' ' . $item->description) }}">
                        @if($item->file_path)
                            <div class="relative cursor-zoom-in group h-48 flex items-center justify-center p-2"
                                 @click="lbOpen = true; lbSrc = '{{ $item->fileUrl() }}'; lbAlt = '{{ addslashes($item->title) }}'">
                                <img src="{{ $item->fileUrl() }}" alt="{{ $item->title }}"
                                     class="max-w-full max-h-full object-contain transition-transform duration-300 group-hover:scale-105 rounded">
                                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300 flex items-center justify-center rounded-xl">
                                    <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 text-xl transition-opacity duration-300 drop-shadow"></i>
                                </div>
                                @if($item->title)
                                    <div class="absolute bottom-0 left-0 right-0 px-3 py-2 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-b-xl">
                                        <p class="text-white text-xs font-medium truncate">{{ $item->title }}</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($item->url)
                            <div class="h-48 flex items-center justify-center">
                                <a href="{{ $item->url }}" target="_blank" class="text-blue-500 hover:underline text-sm">
                                    <i class="fas fa-external-link-alt mr-1"></i> Lien externe
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div id="grid-images"></div>
        @endif
        <div id="empty-images" class="{{ $images->isEmpty() ? '' : 'hidden' }} py-16 text-center text-gray-400">
            <i class="fas fa-image text-5xl mb-4 block"></i>
            <p class="font-medium">Aucune image disponible pour le moment.</p>
        </div>
    </div>

    {{-- Vidéos --}}
    <div id="tab-videos" class="media-section">
        @if($videos->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="grid-videos">
                @foreach($videos as $item)
                    <div class="media-card bg-white border border-gray-200 rounded-xl p-5 card-shadow"
                         data-searchable="{{ strtolower($item->title . ' ' . $item->description) }}">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-video text-purple-600 text-xl"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800 mb-2">{{ $item->title }}</h3>
                        @if($item->description)
                            <p class="text-gray-500 text-sm mb-3">{{ Str::limit($item->description, 100) }}</p>
                        @endif
                        @if($item->url)
                            <a href="{{ $item->url }}" target="_blank" class="inline-flex items-center gap-1.5 text-purple-600 font-medium text-sm hover:underline">
                                <i class="fas fa-play-circle"></i> Voir la vidéo
                            </a>
                        @elseif($item->file_path)
                            <a href="{{ $item->fileUrl() }}" target="_blank" class="inline-flex items-center gap-1.5 text-purple-600 font-medium text-sm hover:underline">
                                <i class="fas fa-download"></i> Télécharger
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div id="grid-videos"></div>
        @endif
        <div id="empty-videos" class="{{ $videos->isEmpty() ? '' : 'hidden' }} py-16 text-center text-gray-400">
            <i class="fas fa-video text-5xl mb-4 block"></i>
            <p class="font-medium">Aucune vidéo disponible pour le moment.</p>
        </div>
    </div>

    {{-- Audios --}}
    <div id="tab-audios" class="media-section">
        @if($audios->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5" id="grid-audios">
                @foreach($audios as $item)
                    <div class="media-card bg-white border border-gray-200 rounded-xl p-5 card-shadow flex items-center gap-4"
                         data-searchable="{{ strtolower($item->title . ' ' . $item->description) }}">
                        <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-music text-yellow-600 text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold text-gray-800 truncate">{{ $item->title }}</h3>
                            @if($item->description)
                                <p class="text-gray-500 text-xs mt-0.5">{{ Str::limit($item->description, 80) }}</p>
                            @endif
                            @if($item->file_path)
                                <audio controls class="mt-2 w-full h-8">
                                    <source src="{{ $item->fileUrl() }}">
                                </audio>
                            @elseif($item->url)
                                <a href="{{ $item->url }}" target="_blank" class="text-yellow-600 hover:underline text-sm mt-1 inline-block">
                                    <i class="fas fa-external-link-alt mr-1"></i> Écouter
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div id="grid-audios"></div>
        @endif
        <div id="empty-audios" class="{{ $audios->isEmpty() ? '' : 'hidden' }} py-16 text-center text-gray-400">
            <i class="fas fa-music text-5xl mb-4 block"></i>
            <p class="font-medium">Aucun audio disponible pour le moment.</p>
        </div>
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
        <div x-show="lbOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="relative max-w-4xl w-full">
            <button @click="lbOpen = false"
                    class="absolute -top-3 -right-3 z-10 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:bg-gray-100 transition-colors">
                <i class="fas fa-times text-gray-600 text-sm"></i>
            </button>
            <img :src="lbSrc" :alt="lbAlt" class="w-full h-auto rounded-xl shadow-2xl block max-h-[85vh] object-contain">
            <p class="text-white/80 text-sm text-center mt-3" x-text="lbAlt"></p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs     = document.querySelectorAll('.tab-btn');
        const searchEl = document.getElementById('media-search');
        const clearBtn = document.getElementById('media-search-clear');

        // Original counts per tab
        const originalCounts = {};
        tabs.forEach(t => {
            const key = t.getAttribute('data-tab');
            originalCounts[key] = document.querySelectorAll(`#grid-${key} .media-card`).length;
        });

        function activateTab(tabName) {
            tabs.forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.media-section').forEach(s => s.classList.remove('active'));
            const btn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
            if (btn) btn.classList.add('active');
            const section = document.getElementById('tab-' + tabName);
            if (section) section.classList.add('active');
        }

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                activateTab(this.getAttribute('data-tab'));
            });
        });

        function applySearch(query) {
            const q = query.trim().toLowerCase();
            clearBtn.classList.toggle('hidden', q === '');

            tabs.forEach(tab => {
                const key   = tab.getAttribute('data-tab');
                const grid  = document.getElementById('grid-' + key);
                const empty = document.getElementById('empty-' + key);
                if (!grid) return;

                const cards   = grid.querySelectorAll('.media-card');
                let visible   = 0;

                cards.forEach(card => {
                    const text    = card.getAttribute('data-searchable') || '';
                    const matches = q === '' || text.includes(q);
                    card.style.display = matches ? '' : 'none';
                    if (matches) visible++;
                });

                if (empty) empty.classList.toggle('hidden', visible > 0);

                // Update tab button count
                const label = tab.innerHTML.replace(/\(\d+\)/, `(${visible})`);
                tab.innerHTML = label;
            });
        }

        searchEl.addEventListener('input', () => applySearch(searchEl.value));

        clearBtn.addEventListener('click', () => {
            searchEl.value = '';
            applySearch('');
            searchEl.focus();
        });

        // Activate first available tab
        const firstTab = document.querySelector('.tab-btn');
        if (firstTab) activateTab(firstTab.getAttribute('data-tab'));
    });
</script>
@endsection
