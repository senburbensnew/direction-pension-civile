@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    .tab-btn.is-active { background: #173052; color: #fff; }
    [x-cloak] { display: none !important; }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8"
     x-data="{ openId: null, q: '', cat: 'all' }">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Ressources</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Foire Aux Questions</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Retrouvez les réponses aux questions les plus fréquentes sur la pension civile.
            </p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Rechercher une question</h2>
            </div>
            <div class="relative mb-5">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" x-model="q" placeholder="Rechercher une question…"
                       class="w-full pl-9 pr-4 py-2.5 border border-gray-200 text-base focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
            </div>
            @if($items->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="cat = 'all'"
                        :class="{ 'is-active': cat === 'all' }"
                        class="tab-btn px-4 py-2 text-base font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">Tous</button>
                    @foreach($items->keys() as $category)
                        <button type="button" @click="cat = @js($category)"
                            :class="{ 'is-active': cat === @js($category) }"
                            class="tab-btn px-4 py-2 text-base font-medium bg-gray-100 text-gray-700 hover:bg-gray-200 transition">{{ $category }}</button>
                    @endforeach
                </div>
            @endif
        </section>

        @if($items->isEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-12 text-center text-gray-500">
                <i class="fa-solid fa-circle-question text-3xl text-navy mb-4 block"></i>
                <p>Aucune question disponible pour le moment.</p>
            </section>
        @else
            @foreach($items as $category => $categoryItems)
                <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8"
                         x-show="cat === 'all' || cat === @js($category)">
                    <div class="flex items-center gap-3 mb-5">
                        <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                        </span>
                        <h2 class="text-xl font-bold text-navy">{{ $category }}</h2>
                        <span class="text-sm font-normal text-gray-400">({{ $categoryItems->count() }})</span>
                    </div>
                    <div class="space-y-3">
                        @foreach($categoryItems as $faq)
                            <article class="border border-gray-200"
                                     x-show="(!q || @js(strtolower($faq->question . ' ' . $faq->answer)).includes(q.toLowerCase()))">
                                <button type="button"
                                    class="w-full flex justify-between items-center gap-4 text-left px-5 py-4"
                                    @click="openId = openId === {{ $faq->id }} ? null : {{ $faq->id }}">
                                    <span class="font-semibold text-navy text-base">{{ $faq->question }}</span>
                                    <i class="fa-solid flex-shrink-0 text-navy text-xs transition-transform"
                                       :class="openId === {{ $faq->id }} ? 'fa-minus' : 'fa-plus'"></i>
                                </button>
                                <div x-show="openId === {{ $faq->id }}" x-cloak
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 -translate-y-1"
                                     x-transition:enter-end="opacity-100 translate-y-0"
                                     class="px-5 pb-5 text-gray-700 text-base leading-relaxed border-t border-gray-100 pt-4">
                                    {!! nl2br(e($faq->answer)) !!}
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        @endif

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8 text-center">
            <div class="flex items-center justify-center gap-3 mb-3">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Vous ne trouvez pas de réponse ?</h2>
            </div>
            <p class="text-gray-700 mb-5">Écrivez-nous, l’équipe de la DPC vous orientera.</p>
            <a href="{{ route('contact') }}"
               class="inline-flex items-center gap-2 px-6 py-3 bg-navy text-white font-semibold hover:opacity-90 transition">
                <i class="fa-solid fa-envelope"></i> Nous contacter
            </a>
        </section>

    </div>
</div>
@endsection
