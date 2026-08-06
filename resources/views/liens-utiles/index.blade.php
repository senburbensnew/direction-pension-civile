@extends('layouts.main')

@section('title', 'Liens Utiles')

@section('content')
@php
    $categories = $links->pluck('category')->unique()->sort()->values();
    $catColors = [
        'Gouvernement' => 'bg-blue-100 text-blue-800',
        'Services'     => 'bg-green-100 text-green-800',
        'Ressources'   => 'bg-purple-100 text-purple-800',
        'Finances'     => 'bg-amber-100 text-amber-800',
    ];
@endphp

<style>
    .link-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .link-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #3b82f6, #1e40af); transform: scaleY(0); transition: transform 0.3s ease; }
    .link-card:hover::before { transform: scaleY(1); }
    .link-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,.15); }
    .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0,0,0,.1); }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .tab-btn.active { background: #3b82f6; color: #fff; }
    .link-card[hidden] { display: none !important; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold gradient-text mb-3">Liens Utiles</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Accédez rapidement aux ressources et services en ligne essentiels.
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-4 mb-8">
        <div class="relative mb-4">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" id="search-links" placeholder="Rechercher un lien…"
                   class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2" id="link-tabs">
                <button type="button" class="tab-btn active px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-filter="all">Tous</button>
                @foreach($categories as $cat)
                    <button type="button" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-filter="{{ $cat }}">{{ $cat }}</button>
                @endforeach
            </div>
        @endif
    </div>

    @if($links->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 card-shadow p-16 text-center text-gray-400">
            <i class="fas fa-link text-5xl mb-4 block"></i>
            <p class="font-medium">Aucun lien disponible pour le moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="links-grid">
            @foreach($links as $link)
                @php $badgeClass = $catColors[$link->category] ?? 'bg-gray-100 text-gray-800'; @endphp
                <div class="link-card bg-white border border-gray-200 rounded-xl p-5 card-shadow flex flex-col"
                     data-category="{{ $link->category }}"
                     data-search="{{ strtolower(($link->name ?? '') . ' ' . ($link->abbr ?? '') . ' ' . ($link->url ?? '')) }}">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-11 h-11 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-external-link-alt text-blue-600"></i>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeClass }}">{{ $link->category }}</span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800 mb-1">{{ $link->name }}</h3>
                    @if($link->abbr)
                        <p class="text-xs text-gray-500 mb-4 font-mono">{{ $link->abbr }}</p>
                    @else
                        <div class="mb-4"></div>
                    @endif
                    <div class="flex items-center justify-between mt-auto pt-2">
                        <a href="{{ $link->url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium text-sm transition-colors">
                            Visiter le site <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                        <button type="button" class="copy-link text-gray-400 hover:text-blue-600 transition-colors px-2"
                                data-url="{{ $link->url }}" title="Copier le lien">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <div id="links-empty" class="hidden bg-white rounded-2xl border border-gray-200 card-shadow p-12 text-center text-gray-400 mt-4">
            <i class="fas fa-search text-3xl mb-3 block"></i>
            <p>Aucun lien ne correspond à votre recherche.</p>
        </div>
    @endif

    <div id="copy-toast" class="fixed bottom-6 right-6 hidden bg-gray-900 text-white text-sm px-4 py-2 rounded-lg shadow-lg z-50">
        Lien copié
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('search-links');
    const cards = document.querySelectorAll('.link-card');
    const empty = document.getElementById('links-empty');
    const tabs = document.querySelectorAll('#link-tabs .tab-btn');
    const toast = document.getElementById('copy-toast');
    let filter = 'all';

    function apply() {
        const q = (search?.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const matchFilter = filter === 'all' || card.dataset.category === filter;
            const matchQ = !q || (card.dataset.search || '').includes(q);
            const show = matchFilter && matchQ;
            card.hidden = !show;
            if (show) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible > 0 || cards.length === 0);
    }

    tabs.forEach(tab => tab.addEventListener('click', function () {
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        filter = this.dataset.filter;
        apply();
    }));
    search?.addEventListener('input', apply);

    document.querySelectorAll('.copy-link').forEach(btn => {
        btn.addEventListener('click', () => {
            navigator.clipboard.writeText(btn.dataset.url).then(() => {
                toast.classList.remove('hidden');
                setTimeout(() => toast.classList.add('hidden'), 1800);
            });
        });
    });
});
</script>
@endsection
