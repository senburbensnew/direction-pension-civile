@extends('layouts.main')

@section('content')
<style>
    .term-card { transition: all 0.3s ease; position: relative; overflow: hidden; }
    .term-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #3b82f6, #1e40af); transform: scaleY(0); transition: transform 0.3s ease; }
    .term-card:hover::before { transform: scaleY(1); }
    .term-card:hover { transform: translateY(-4px); box-shadow: 0 15px 30px -10px rgba(0,0,0,.15); }
    .gradient-text { background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .fade-in { animation: fadeIn 0.6s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="my-8 rounded-lg py-8 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 bg-white">

    <header class="mb-10 text-center fade-in">
        <h1 class="text-4xl font-bold gradient-text mb-3">Glossaire de la Pension Civile</h1>
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">
            Retrouvez la définition des termes essentiels utilisés dans le domaine de la pension civile.
        </p>
        <div class="mt-8 max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" id="search-terms" placeholder="Rechercher un terme…"
                    class="w-full p-4 pl-12 rounded-xl border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-300">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
        </div>
    </header>

    @if($terms->isEmpty())
        <div class="text-center py-16 text-gray-400">
            <i class="fas fa-book text-5xl mb-4 block"></i>
            <p>Aucun terme disponible pour le moment.</p>
        </div>
    @else
        <section class="fade-in">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="terms-list">
                @foreach($terms as $term)
                    @php
                        $colorMap = [
                            'Retraite'      => ['bg-blue-100 text-blue-800',   'bg-blue-100',   'text-blue-600'],
                            'Finance'       => ['bg-green-100 text-green-800', 'bg-green-100',  'text-green-600'],
                            'Agent'         => ['bg-purple-100 text-purple-800','bg-purple-100','text-purple-600'],
                            'Calcul'        => ['bg-amber-100 text-amber-800', 'bg-amber-100',  'text-amber-600'],
                            'Invalidité'    => ['bg-red-100 text-red-800',     'bg-red-100',    'text-red-600'],
                            'Administration'=> ['bg-indigo-100 text-indigo-800','bg-indigo-100','text-indigo-600'],
                        ];
                        [$badgeClass, $iconBg, $iconColor] = $colorMap[$term->category] ?? ['bg-gray-100 text-gray-800', 'bg-gray-100', 'text-gray-600'];
                    @endphp
                    <div class="term-card bg-white border border-gray-200 rounded-xl p-5">
                        <span class="absolute top-3 right-3 text-xs px-2 py-0.5 rounded-full font-medium {{ $badgeClass }}">{{ $term->category }}</span>
                        <div class="flex items-start mb-3">
                            <div class="w-12 h-12 {{ $iconBg }} rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                <i class="fas {{ $term->icon }} {{ $iconColor }} text-lg"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-800 pt-1">{{ $term->term }}</h3>
                        </div>
                        <p class="text-gray-600 leading-relaxed">{{ $term->definition }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search-terms');
        const cards = document.querySelectorAll('.term-card');
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            cards.forEach(card => {
                const h3 = card.querySelector('h3');
                const p = card.querySelector('p');
                const text = (h3 ? h3.textContent : '') + (p ? p.textContent : '');
                card.style.display = text.toLowerCase().includes(query) ? 'block' : 'none';
            });
        });
    });
</script>
@endsection
