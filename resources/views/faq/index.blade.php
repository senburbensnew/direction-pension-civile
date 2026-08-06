@extends('layouts.main')

@section('title', 'FAQ')

@section('content')
<style>
    .faq-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .faq-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #3b82f6, #1e40af); transform: scaleY(0); transition: transform 0.3s ease; }
    .faq-card:hover::before { transform: scaleY(1); }
    .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0,0,0,.1); }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .faq-item[hidden] { display: none !important; }
</style>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 fade-in"
     x-data="{ openId: null, q: '', cat: 'all' }">

    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold gradient-text mb-3">Foire Aux Questions</h1>
        <p class="text-gray-600 text-lg max-w-2xl mx-auto">
            Retrouvez les réponses aux questions les plus fréquentes sur la pension civile.
        </p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 card-shadow p-4 mb-8">
        <div class="relative mb-4">
            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fas fa-search text-sm"></i>
            </span>
            <input type="text" x-model="q" placeholder="Rechercher une question…"
                   class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" @click="cat = 'all'"
                :class="cat === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition">Tous</button>
            @foreach($items->keys() as $category)
                <button type="button" @click="cat = @js($category)"
                    :class="cat === @js($category) ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition">{{ $category }}</button>
            @endforeach
        </div>
    </div>

    @if($items->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 card-shadow p-16 text-center text-gray-400">
            <i class="fas fa-question-circle text-5xl mb-4 block"></i>
            <p class="font-medium">Aucune question disponible pour le moment.</p>
        </div>
    @else
        @foreach($items as $category => $categoryItems)
            <div class="mb-8 faq-category"
                 x-show="cat === 'all' || cat === @js($category)">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <span class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-folder text-blue-600 text-sm"></i>
                    </span>
                    {{ $category }}
                    <span class="text-sm font-normal text-gray-400">({{ $categoryItems->count() }})</span>
                </h2>

                <div class="space-y-3">
                    @foreach($categoryItems as $faq)
                        <div class="faq-item faq-card bg-white border border-gray-200 rounded-xl card-shadow"
                             x-show="(!q || @js(strtolower($faq->question . ' ' . $faq->answer)).includes(q.toLowerCase()))"
                             data-search="{{ strtolower($faq->question . ' ' . $faq->answer) }}">
                            <button type="button"
                                class="w-full flex justify-between items-center gap-4 text-left px-5 py-4"
                                @click="openId = openId === {{ $faq->id }} ? null : {{ $faq->id }}">
                                <span class="font-semibold text-gray-800 text-sm sm:text-base">{{ $faq->question }}</span>
                                <i class="fas flex-shrink-0 text-gray-400 transition-transform"
                                   :class="openId === {{ $faq->id }} ? 'fa-minus text-blue-600' : 'fa-plus'"></i>
                            </button>
                            <div x-show="openId === {{ $faq->id }}" x-cloak
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 class="px-5 pb-5 text-gray-600 text-sm leading-relaxed border-t border-gray-100 pt-4">
                                {!! nl2br(e($faq->answer)) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-10 bg-white border border-blue-100 rounded-xl card-shadow p-6 text-center">
        <p class="text-gray-700 mb-3">Vous ne trouvez pas de réponse à votre question ?</p>
        <a href="{{ route('contact') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition">
            <i class="fas fa-envelope"></i> Nous contacter
        </a>
    </div>
</div>
@endsection
