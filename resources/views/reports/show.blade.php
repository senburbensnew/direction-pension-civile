@extends('layouts.main')

@section('title', $report->title)

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <nav class="text-sm text-gray-500">
            <a href="{{ url('/') }}" class="hover:text-navy">Accueil</a>
            <span class="mx-1.5">/</span>
            <a href="{{ route('reports.index') }}" class="hover:text-navy">Publications &amp; Rapports</a>
            <span class="mx-1.5">/</span>
            <span class="text-navy font-medium">{{ Str::limit($report->title, 55) }}</span>
        </nav>

        <section class="bg-white border border-gray-200 card-shadow overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-start gap-5 p-6 sm:p-8">
                <div class="w-28 h-20 overflow-hidden border border-gray-200 bg-gray-50 shrink-0">
                    <img src="{{ $report->coverUrl() }}" alt="{{ $report->title }}" class="w-full h-full object-cover">
                </div>
                <div class="flex-1 min-w-0">
                    <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Publications &amp; Rapports</span>
                    <h1 class="text-2xl md:text-3xl font-bold text-navy mt-1 mb-2">{{ $report->title }}</h1>
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-3">
                        @if($report->year)
                            <span>{{ $report->year }}</span>
                        @endif
                        @if($report->file_size)
                            <span>{{ number_format($report->file_size / 1024, 0) }} Ko</span>
                        @endif
                        @if($report->published_at)
                            <span>{{ $report->published_at->translatedFormat('d F Y') }}</span>
                        @endif
                    </div>
                    @if($report->description)
                        <p class="text-gray-700 text-sm leading-relaxed">{{ $report->description }}</p>
                    @endif
                </div>
                <a href="{{ route('reports.download', $report) }}"
                   class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold text-sm">
                    <i class="fa-solid fa-download"></i>
                    Télécharger
                </a>
            </div>
        </section>

        <section class="bg-white border border-gray-200 card-shadow overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <span class="text-2xl text-navy"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
                    <h2 class="text-xl font-bold text-navy">Aperçu du document</h2>
                </div>
                <a href="{{ route('reports.index') }}"
                   class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 border border-gray-200 text-navy font-medium hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Retour
                </a>
            </div>
            @if(str_contains($report->mime_type ?? '', 'pdf'))
                <iframe src="{{ route('reports.view', $report) }}"
                        class="w-full border-0"
                        style="height: 78vh; min-height: 500px;"
                        title="{{ $report->title }}">
                </iframe>
            @else
                <div class="p-12 text-center text-gray-500">
                    <i class="fa-solid fa-file-lines text-3xl text-navy mb-4 block"></i>
                    <p class="font-medium text-navy mb-2">Aperçu non disponible pour ce format</p>
                    <p class="text-sm mb-5">Téléchargez le fichier pour le consulter.</p>
                    <a href="{{ route('reports.download', $report) }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-semibold">
                        <i class="fa-solid fa-download"></i> Télécharger le fichier
                    </a>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
