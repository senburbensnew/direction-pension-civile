@extends('layouts.main')

@section('title', 'Publications & Rapports')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Communications</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Publications & Rapports</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Rapports annuels, notes officielles, bulletins et documents administratifs publiés par la Direction de la Pension Civile.
            </p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-5">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Rechercher un rapport</h2>
                <span class="ml-auto text-sm text-gray-400">{{ $reports->total() }} document(s)</span>
            </div>
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input name="q" value="{{ request('q') }}"
                           placeholder="Rechercher un rapport par titre, année ou description…"
                           class="w-full pl-9 pr-4 py-2.5 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy">
                </div>
                <button type="submit"
                        class="px-5 py-2.5 bg-navy text-white text-sm font-semibold hover:opacity-90 whitespace-nowrap">
                    Rechercher
                </button>
                @if(request('q'))
                    <a href="{{ route('reports.index') }}"
                       class="px-4 py-2.5 border border-gray-200 text-gray-700 hover:bg-gray-50 text-sm whitespace-nowrap flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-xmark text-xs"></i> Effacer
                    </a>
                @endif
            </form>
        </section>

        @if($reports->isEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-12 text-center text-gray-500">
                <i class="fa-solid fa-folder-open text-3xl text-navy mb-4 block"></i>
                <p>Aucun rapport trouvé.</p>
                @if(request('q'))
                    <a href="{{ route('reports.index') }}" class="inline-block mt-3 text-sm text-navy font-medium hover:text-orange-500">
                        Voir tous les rapports
                    </a>
                @else
                    <p class="text-sm text-gray-400 mt-1">Aucun document publié pour le moment.</p>
                @endif
            </section>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($reports as $report)
                    <x-report-card :report="$report" />
                @endforeach
            </div>
            <div class="flex justify-center">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
