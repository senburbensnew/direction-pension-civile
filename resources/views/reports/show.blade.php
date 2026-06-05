@extends('layouts.main')

@section('title', $report->title)

@section('content')

{{-- Breadcrumb --}}
<div class="bg-white border-b border-gray-100">
    <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-blue-600 transition-colors">Accueil</a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <a href="{{ route('reports.index') }}" class="hover:text-blue-600 transition-colors">Publications & Rapports</a>
            <i class="fas fa-chevron-right text-xs text-gray-300"></i>
            <span class="text-gray-700 font-medium truncate max-w-xs">{{ Str::limit($report->title, 55) }}</span>
        </nav>
    </div>
</div>

<div class="bg-gray-50 min-h-screen">

    {{-- Document header --}}
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 py-8">
        <div class="container mx-auto px-4 max-w-5xl">
            <div class="flex items-start gap-5">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                    @php $mime = $report->mime_type ?? ''; @endphp
                    <i class="fas {{ str_contains($mime, 'pdf') ? 'fa-file-pdf' : 'fa-file-alt' }} text-white text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        @if($report->year)
                            <span class="text-xs font-bold bg-white/20 text-white px-2.5 py-1 rounded-full">
                                {{ $report->year }}
                            </span>
                        @endif
                        @if($report->file_size)
                            <span class="text-xs text-blue-200">
                                <i class="fas fa-file mr-1"></i>{{ number_format($report->file_size / 1024, 0) }} Ko
                            </span>
                        @endif
                        @if($report->published_at)
                            <span class="text-xs text-blue-200">
                                <i class="far fa-calendar-alt mr-1"></i>{{ $report->published_at->translatedFormat('d F Y') }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-xl md:text-2xl font-bold text-white leading-snug mb-2">
                        {{ $report->title }}
                    </h1>
                    @if($report->description)
                        <p class="text-blue-100 text-sm leading-relaxed line-clamp-2">{{ $report->description }}</p>
                    @endif
                </div>
                <a href="{{ route('reports.download', $report) }}"
                   class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 bg-white text-blue-900 font-semibold text-sm rounded-xl hover:bg-blue-50 transition-colors shadow-lg">
                    <i class="fas fa-download text-blue-700"></i>
                    Télécharger
                </a>
            </div>
        </div>
    </div>

    {{-- Viewer area --}}
    <div class="container mx-auto px-4 max-w-5xl py-8">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Viewer toolbar --}}
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 bg-gray-50">
                <span class="text-sm font-medium text-gray-600 flex items-center gap-2">
                    <i class="fas fa-eye text-blue-500"></i>
                    Aperçu du document
                </span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.download', $report) }}"
                       class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i class="fas fa-download text-[10px]"></i> Télécharger
                    </a>
                    <a href="{{ route('reports.index') }}"
                       class="inline-flex items-center gap-1.5 text-xs px-3 py-1.5 border border-gray-200 hover:bg-gray-100 text-gray-600 font-medium rounded-lg transition-colors">
                        <i class="fas fa-arrow-left text-[10px]"></i> Retour
                    </a>
                </div>
            </div>

            @if(str_contains($report->mime_type ?? '', 'pdf'))
                <iframe src="{{ route('reports.view', $report) }}"
                        class="w-full border-0"
                        style="height: 78vh; min-height: 500px;"
                        frameborder="0"
                        title="{{ $report->title }}">
                </iframe>
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center px-4">
                    <div class="w-20 h-20 bg-gray-50 rounded-2xl border border-gray-200 flex items-center justify-center mb-5">
                        <i class="fas fa-file text-4xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-700 font-semibold mb-2">Aperçu non disponible pour ce format</p>
                    <p class="text-sm text-gray-400 mb-6 max-w-sm">
                        Ce type de fichier ne peut pas être prévisualisé dans le navigateur.
                        Téléchargez-le pour le consulter.
                    </p>
                    <a href="{{ route('reports.download', $report) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">
                        <i class="fas fa-download"></i> Télécharger le fichier
                    </a>
                </div>
            @endif

        </div>
    </div>

</div>

@endsection
