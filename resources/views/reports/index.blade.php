@extends('layouts.main')

@section('title', 'Publications & Rapports')

@section('content')

{{-- Page hero --}}
<div class="bg-gradient-to-r from-blue-900 to-blue-700">
    <div class="container mx-auto px-4 py-12">
        <div class="flex items-center gap-2 text-blue-300 text-sm mb-3">
            <a href="{{ url('/') }}" class="hover:text-white transition-colors">Accueil</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-white font-medium">Publications & Rapports</span>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                <i class="fas fa-book-open text-white text-xl"></i>
            </div>
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-white leading-tight">Publications & Rapports</h1>
                <p class="text-blue-200 mt-1 max-w-2xl text-sm">
                    Rapports annuels, notes officielles, bulletins et documents administratifs publiés par la Direction de la Pension Civile.
                </p>
            </div>
        </div>
    </div>
</div>

<div class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-10">

        {{-- Search bar --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 mb-8">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[240px]">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input name="q" value="{{ request('q') }}"
                           placeholder="Rechercher un rapport par titre, année ou description…"
                           class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white">
                </div>
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shrink-0">
                    <i class="fas fa-search mr-1.5"></i> Rechercher
                </button>
                @if(request('q'))
                    <a href="{{ route('reports.index') }}"
                       class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        <i class="fas fa-times mr-1"></i> Effacer
                    </a>
                @endif
                <span class="text-sm text-gray-400 ml-auto shrink-0">
                    {{ $reports->total() }} document(s)
                </span>
            </form>
        </div>

        @if($reports->isEmpty())
            <div class="flex flex-col items-center justify-center py-24 text-center">
                <div class="w-16 h-16 bg-white rounded-full border border-gray-200 flex items-center justify-center mb-4 shadow-sm">
                    <i class="fas fa-folder-open text-2xl text-gray-300"></i>
                </div>
                <p class="text-gray-500 font-medium mb-1">Aucun rapport trouvé.</p>
                @if(request('q'))
                    <a href="{{ route('reports.index') }}" class="text-sm text-blue-600 hover:underline mt-2">
                        Voir tous les rapports
                    </a>
                @else
                    <p class="text-sm text-gray-400">Aucun document publié pour le moment.</p>
                @endif
            </div>
        @else

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($reports as $report)
                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 flex flex-col overflow-hidden group">

                        {{-- Card header --}}
                        <div class="bg-gradient-to-br from-blue-700 to-blue-900 px-5 py-5 flex items-start gap-3">
                            <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center shrink-0 mt-0.5">
                                @php
                                    $mime = $report->mime_type ?? '';
                                    $icon = str_contains($mime, 'pdf') ? 'fa-file-pdf' : (str_contains($mime, 'word') ? 'fa-file-word' : 'fa-file-alt');
                                @endphp
                                <i class="fas {{ $icon }} text-white text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-white font-bold text-sm leading-snug line-clamp-2 mb-2">
                                    {{ $report->title }}
                                </h3>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @if($report->year)
                                        <span class="text-xs font-semibold bg-white/20 text-white px-2 py-0.5 rounded-full">
                                            {{ $report->year }}
                                        </span>
                                    @endif
                                    @if($report->file_size)
                                        <span class="text-xs text-blue-200">
                                            {{ number_format($report->file_size / 1024, 0) }} Ko
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="p-5 flex flex-col flex-1">
                            <p class="text-xs text-gray-500 leading-relaxed line-clamp-3 flex-1 mb-4">
                                {{ $report->description ?: 'Aucune description disponible pour ce document.' }}
                            </p>

                            {{-- Published date --}}
                            @if($report->published_at)
                                <div class="flex items-center gap-1.5 text-xs text-gray-400 mb-4">
                                    <i class="far fa-calendar-alt text-gray-300"></i>
                                    Publié le {{ $report->published_at->translatedFormat('d F Y') }}
                                </div>
                            @endif

                            {{-- Actions --}}
                            <div class="flex gap-2 mt-auto">
                                <a href="{{ route('reports.show', $report) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-eye text-[10px]"></i> Consulter
                                </a>
                                <a href="{{ route('reports.download', $report) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2 border border-gray-200 hover:bg-gray-50 text-gray-600 text-xs font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-download text-[10px]"></i> Télécharger
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex justify-center">
                {{ $reports->withQueryString()->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
