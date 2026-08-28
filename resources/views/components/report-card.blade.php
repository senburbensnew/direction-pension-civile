@props(['report'])

@php
    $date = $report->published_at ?? $report->created_at;
@endphp

<article class="group bg-white rounded-2xl overflow-visible flex flex-col border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300">

    <div class="relative">
        <a href="{{ route('reports.show', $report) }}" class="block overflow-hidden bg-gray-100 rounded-t-2xl">
            <img src="{{ $report->coverUrl() }}"
                 alt="{{ $report->title }}"
                 class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                 style="aspect-ratio: 16/10;"
                 loading="lazy" />
        </a>
        @if($date)
            <time datetime="{{ $date->toIso8601String() }}"
                  class="absolute left-0 bottom-0 translate-y-1/2 z-10 bg-[#173052] text-white text-[11px] font-bold tracking-wide px-3.5 py-1.5">
                {{ $date->translatedFormat('F d, Y') }}
            </time>
        @endif
    </div>

    <div class="flex flex-col flex-1 px-5 pt-8 pb-6">

        <p class="text-xs text-gray-400 mb-3">
            Publications & Rapports
            @if($report->year)
                <span class="mx-1.5">·</span>
                {{ $report->year }}
            @endif
        </p>

        <h3 class="text-[15px] md:text-base font-bold text-gray-900 leading-snug line-clamp-3 mb-6 group-hover:text-blue-700 transition-colors">
            <a href="{{ route('reports.show', $report) }}">{{ $report->title }}</a>
        </h3>

        <div class="mt-auto flex items-center justify-between gap-3">
            <a href="{{ route('reports.show', $report) }}"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-orange-500 transition-colors">
                Consulter
                <i class="fas fa-arrow-right text-[10px] transition-transform duration-300 group-hover:translate-x-1"></i>
            </a>
            <a href="{{ route('reports.download', $report) }}"
               class="text-gray-400 hover:text-blue-600 transition-colors"
               title="Télécharger"
               aria-label="Télécharger {{ $report->title }}">
                <i class="fas fa-download text-sm"></i>
            </a>
        </div>
    </div>
</article>
