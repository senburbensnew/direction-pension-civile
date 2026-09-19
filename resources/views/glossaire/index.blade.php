@extends('layouts.main')

@section('title', 'Glossaire')

@section('content')
@php
    $categories = $terms->pluck('category')->unique()->sort()->values();
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .term-card[hidden] { display: none !important; }
    .tab-btn.active { background: #173052; color: #fff; }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Ressources</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Glossaire de la Pension Civile</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Retrouvez la définition des termes essentiels utilisés dans le domaine de la pension civile.
            </p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-book" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Rechercher un terme</h2>
            </div>
            <div class="relative mb-5">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" id="search-terms" placeholder="Rechercher un terme…"
                       class="w-full pl-9 pr-4 py-2.5 border border-gray-200 text-base focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
            </div>
            @if($categories->isNotEmpty())
                <div class="flex flex-wrap gap-2" id="gloss-tabs">
                    <button type="button" class="tab-btn active px-4 py-2 text-base font-medium bg-gray-100 text-gray-700 transition" data-cat="all">Tous</button>
                    @foreach($categories as $cat)
                        <button type="button" class="tab-btn px-4 py-2 text-base font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition" data-cat="{{ $cat }}">{{ $cat }}</button>
                    @endforeach
                </div>
            @endif
        </section>

        @if($terms->isEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-12 text-center text-gray-500">
                <i class="fa-solid fa-book text-3xl text-navy mb-4 block"></i>
                <p>Aucun terme disponible pour le moment.</p>
            </section>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5" id="terms-list">
                @foreach($terms as $term)
                    <article class="term-card bg-white border border-gray-200 card-shadow p-6"
                             data-category="{{ $term->category }}"
                             data-search="{{ strtolower($term->term . ' ' . $term->definition) }}">
                        <p class="text-xs font-bold text-orange-500 uppercase tracking-widest mb-3">{{ $term->category }}</p>
                        <div class="flex items-start gap-3 mb-3">
                            <span class="text-xl text-navy mt-0.5 shrink-0">
                                <i class="fa-solid {{ $term->icon ?: 'fa-book' }}" aria-hidden="true"></i>
                            </span>
                            <h3 class="text-lg font-bold text-navy">{{ $term->term }}</h3>
                        </div>
                        <p class="text-gray-700 text-base leading-relaxed">{{ $term->definition }}</p>
                    </article>
                @endforeach
            </div>
            <div id="gloss-empty" class="hidden bg-white border border-gray-200 card-shadow p-12 text-center text-gray-500">
                <i class="fa-solid fa-magnifying-glass text-2xl text-navy mb-3 block"></i>
                <p>Aucun terme ne correspond à votre recherche.</p>
            </div>
        @endif

    </div>
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
