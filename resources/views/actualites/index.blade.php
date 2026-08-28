@extends('layouts.main')

@section('title', 'Actualités')

@section('content')

{{-- Page hero --}}
<div class="bg-gradient-to-r from-blue-900 to-blue-700">
    <div class="container mx-auto px-4 py-12">
        <div class="flex items-center gap-2 text-blue-300 text-sm mb-3">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors">Accueil</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-white font-medium">Actualités</span>
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-2">Actualités pour les Retraités</h1>
        <p class="text-blue-200 max-w-2xl">
            Restez informé des dernières nouvelles, annonces et informations de la Direction de la Pension Civile.
        </p>
    </div>
</div>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-10">

        {{-- Search bar --}}
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input name="q" value="{{ request('q') }}"
                           placeholder="Rechercher un article..."
                           class="pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg bg-white shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-72">
                </div>
                <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    Rechercher
                </button>
                @if(request('q'))
                    <a href="{{ route('actualites.index') }}"
                       class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times mr-1"></i> Effacer
                    </a>
                @endif
            </form>
            <p class="text-sm text-gray-500">
                {{ $actualites->total() }} article(s)
                @if(request('q'))
                    pour <strong class="text-gray-700">"{{ request('q') }}"</strong>
                @endif
            </p>
        </div>

        @if($actualites->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 bg-white rounded-full border border-gray-200 flex items-center justify-center mb-4 shadow-sm">
                    <i class="fas fa-newspaper text-2xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 font-medium mb-1">Aucun article trouvé.</p>
                @if(request('q'))
                    <a href="{{ route('actualites.index') }}" class="text-sm text-blue-600 hover:underline mt-2">
                        Voir toutes les actualités
                    </a>
                @else
                    <p class="text-sm text-gray-400">Revenez prochainement.</p>
                @endif
            </div>
        @else

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
                @foreach($actualites as $actu)
                    <x-actualite-card :actualite="$actu" />
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="flex justify-center mt-4">
                {{ $actualites->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
