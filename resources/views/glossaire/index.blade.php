@extends('layouts.main')

@section('title', 'Glossaire')

@section('content')
@php
    $categories = $terms->pluck('category')->unique()->sort()->values();
    $colorMap = [
        'Retraite'       => ['bg-blue-100 text-blue-800',    'bg-blue-100',    'text-blue-600'],
        'Finance'        => ['bg-green-100 text-green-800',  'bg-green-100',   'text-green-600'],
        'Agent'          => ['bg-purple-100 text-purple-800','bg-purple-100',  'text-purple-600'],
        'Calcul'         => ['bg-amber-100 text-amber-800',  'bg-amber-100',   'text-amber-600'],
        'Invalidité'     => ['bg-red-100 text-red-800',      'bg-red-100',     'text-red-600'],
        'Administration' => ['bg-indigo-100 text-indigo-800','bg-indigo-100',  'text-indigo-600'],
        'Général'        => ['bg-gray-100 text-gray-800',    'bg-gray-100',    'text-gray-600'],
    ];
@endphp

<style>
    .term-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .term-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #3b82f6, #1e40af); transform: scaleY(0); transition: transform 0.3s ease; }
    .term-card:hover::before { transform: scaleY(1); }
    .term-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,.15); }
    .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0,0,0,.1); }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .term-card[hidden] { display: none !important; }
    .tab-btn.active { background: #3b82f6; color: #fff; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold gradient-text mb-3">Glossaire de la Pension Civile</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Retrouvez la définition des termes essentiels utilisés dans le domaine de la pension civile.
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-4 mb-8">
        <div class="relative mb-4">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" id="search-terms" placeholder="Rechercher un terme…"
                   class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        @if($categories->isNotEmpty())
            <div class="flex flex-wrap gap-2" id="gloss-tabs">
                <button type="button" class="tab-btn active px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-cat="all">Tous</button>
                @foreach($categories as $cat)
                    <button type="button" class="tab-btn px-4 py-2 rounded-lg text-sm font-medium bg-gray-100 text-gray-700 transition" data-cat="{{ $cat }}">{{ $cat }}</button>
                @endforeach
            </div>
        @endif
    </div>

    @if($terms->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 card-shadow p-16 text-center text-gray-400">
            <i class="fas fa-book text-5xl mb-4 block"></i>
            <p class="font-medium">Aucun terme disponible pour le moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="terms-list">
            @foreach($terms as $term)
                @php
                    [$badgeClass, $iconBg, $iconColor] = $colorMap[$term->category] ?? ['bg-gray-100 text-gray-800', 'bg-gray-100', 'text-gray-600'];
                @endphp
                <div class="term-card bg-white border border-gray-200 rounded-xl p-5 card-shadow"
                     data-category="{{ $term->category }}"
                     data-search="{{ strtolower($term->term . ' ' . $term->definition) }}">
                    <span class="absolute top-3 right-3 text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeClass }}">{{ $term->category }}</span>
                    <div class="flex items-start mb-3 pr-16">
                        <div class="w-11 h-11 {{ $iconBg }} rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                            <i class="fas {{ $term->icon ?: 'fa-book' }} {{ $iconColor }}"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 pt-1">{{ $term->term }}</h3>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $term->definition }}</p>
                </div>
            @endforeach
        </div>
        <div id="gloss-empty" class="hidden bg-white rounded-2xl border border-gray-200 card-shadow p-12 text-center text-gray-400 mt-4">
            <i class="fas fa-search text-3xl mb-3 block"></i>
            <p>Aucun terme ne correspond à votre recherche.</p>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('search-terms');
    const cards = document.querySelectorAll('.term-card');
    const empty = document.getElementById('gloss-empty');
    const tabs = document.querySelectorAll('#gloss-tabs .tab-btn');
    let cat = 'all';

    function apply() {
        const q = (search?.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const matchCat = cat === 'all' || card.dataset.category === cat;
            const matchQ = !q || (card.dataset.search || '').includes(q);
            const show = matchCat && matchQ;
            card.hidden = !show;
            if (show) visible++;
        });
        if (empty) empty.classList.toggle('hidden', visible > 0 || cards.length === 0);
    }

    tabs.forEach(tab => tab.addEventListener('click', function () {
        tabs.forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        cat = this.dataset.cat;
        apply();
    }));
    search?.addEventListener('input', apply);
});
</script>
@endsection
