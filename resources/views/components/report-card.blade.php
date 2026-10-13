@props(['report'])

@php
    $date = $report->published_at ?? $report->created_at;
@endphp

<article class="bg-white border border-gray-200 card-shadow flex flex-col overflow-hidden">
    <a href="{{ route('reports.show', $report) }}" class="block overflow-hidden bg-gray-50">
        <x-optimized-image
            :src="$report->coverComponentSrc()"
            :alt="$report->title"
            class="w-full object-cover"
            style="aspect-ratio: 16/10;"
            sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
        ></x-optimized-image>
    </a>
    <div class="flex flex-col flex-1 p-5">
        <p class="text-xs font-bold text-orange-500 uppercase tracking-widest mb-2">
            @if($report->year)
                {{ $report->year }}
            @else
                Publications &amp; Rapports
            @endif
            @if($date)
                <span class="text-gray-400 font-normal normal-case tracking-normal"> · {{ $date->translatedFormat('d/m/Y') }}</span>
            @endif
        </p>
        <h3 class="text-lg font-bold text-navy leading-snug line-clamp-3 mb-4">
            <a href="{{ route('reports.show', $report) }}" class="hover:text-orange-500">{{ $report->title }}</a>
        </h3>
        <div class="mt-auto flex items-center gap-2">
            <a href="{{ route('reports.show', $report) }}"
               class="inline-flex items-center gap-1.5 text-base px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium">
                Consulter
            </a>
            <a href="{{ route('reports.download', $report) }}"
               class="inline-flex items-center gap-1.5 text-base px-4 py-2 border border-gray-200 text-navy font-medium hover:bg-gray-50"
               title="Télécharger"
               aria-label="Télécharger {{ $report->title }}">
                <i class="fa-solid fa-download text-xs"></i> Télécharger
            </a>
        </div>
    </div>
</article>
