@extends('layouts.main')

@section('title', 'Liens Utiles')

@section('content')
    <style>
        .gradient-text { background: linear-gradient(135deg, #074482 0%, #0d6efd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-shadow { box-shadow: 0 10px 25px -5px rgba(0,0,0,.1), 0 10px 10px -5px rgba(0,0,0,.04); }
        .link-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
        .link-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #074482, #0d6efd); transform: scaleY(0); transition: transform 0.3s ease; }
        .link-card:hover::before { transform: scaleY(1); }
        .link-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,.15); }
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .filter-btn.active { background-color: #074482; color: white; }
    </style>

    <section class="py-8 max-w-7xl mx-auto px-6 fade-in bg-white">
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold gradient-text mb-3">Liens Utiles</h1>
            <p class="text-gray-600 text-lg">Accédez rapidement aux ressources et services en ligne essentiels.</p>
        </div>

        <div class="bg-white rounded-2xl card-shadow p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="w-full md:flex-1 max-w-2xl">
                    <div class="relative">
                        <input type="text" id="search-links" placeholder="Rechercher un lien…"
                               class="w-full p-4 pl-12 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button class="filter-btn active px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="all">Tous</button>
                    @foreach($links->pluck('category')->unique()->sort() as $cat)
                        <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="{{ $cat }}">{{ $cat }}</button>
                    @endforeach
                </div>
            </div>
        </div>

        @if($links->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <i class="fas fa-link text-5xl mb-4 block"></i>
                <p>Aucun lien disponible pour le moment.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="links-grid">
                @foreach($links as $link)
                    @php
                        $catColors = [
                            'Gouvernement' => 'bg-blue-100 text-blue-800',
                            'Services'     => 'bg-green-100 text-green-800',
                            'Ressources'   => 'bg-purple-100 text-purple-800',
                            'Finances'     => 'bg-amber-100 text-amber-800',
                        ];
                        $badgeClass = $catColors[$link->category] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <div class="link-card bg-white border border-gray-200 rounded-xl p-6 card-shadow" data-category="{{ $link->category }}">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-external-link-alt text-blue-600 text-lg"></i>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeClass }}">{{ $link->category }}</span>
                        </div>
                        <h3 class="text-base font-semibold text-gray-800 mb-1">{{ $link->name }}</h3>
                        @if($link->abbr)
                            <p class="text-xs text-gray-500 mb-4 font-mono">{{ $link->abbr }}</p>
                        @endif
                        <div class="flex items-center justify-between mt-4">
                            <a href="{{ $link->url }}" target="_blank"
                               class="flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                                <span>Visiter le site</span>
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                            <button class="text-gray-400 hover:text-blue-600 transition-colors"
                                    onclick="copyToClipboard('{{ $link->url }}')" title="Copier le lien">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($links->count() >= 4)
                <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 card-shadow">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold gradient-text mb-1">Accès Rapide</h2>
                        <p class="text-gray-500 text-sm">Liens les plus utilisés</p>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($links->take(8) as $link)
                            <a href="{{ $link->url }}" target="_blank"
                               class="bg-white rounded-lg p-4 text-center hover:shadow-md transition-all group">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-blue-600 transition-colors">
                                    <i class="fas fa-external-link-alt text-blue-600 group-hover:text-white transition-colors"></i>
                                </div>
                                <span class="text-xs font-medium text-gray-700 group-hover:text-blue-600 transition-colors">
                                    {{ $link->abbr ?: Str::limit($link->name, 12) }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-links');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const cards = document.querySelectorAll('.link-card');
            let currentFilter = 'all';

            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase();
                cards.forEach(card => {
                    const cat = card.getAttribute('data-category');
                    const text = card.querySelector('h3').textContent.toLowerCase();
                    const matchSearch = text.includes(q);
                    const matchFilter = currentFilter === 'all' || cat === currentFilter;
                    card.style.display = (matchSearch && matchFilter) ? 'block' : 'none';
                });
            });

            filterButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    currentFilter = this.getAttribute('data-filter');
                    filterButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    const q = searchInput.value.toLowerCase();
                    cards.forEach(card => {
                        const cat = card.getAttribute('data-category');
                        const text = card.querySelector('h3').textContent.toLowerCase();
                        const matchSearch = text.includes(q);
                        const matchFilter = currentFilter === 'all' || cat === currentFilter;
                        card.style.display = (matchSearch && matchFilter) ? 'block' : 'none';
                    });
                });
            });
        });

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Lien copié dans le presse-papier !');
            });
        }
    </script>
@endsection
