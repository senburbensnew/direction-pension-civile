@props(['actualite'])

<article class="bg-white border border-gray-200 flex flex-col overflow-hidden" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
    <a href="{{ route('actualites.show', $actualite->id) }}" class="block overflow-hidden bg-gray-50">
        <img src="{{ $actualite->coverUrl() }}"
             alt="{{ $actualite->title }}"
             class="w-full object-cover"
             style="aspect-ratio: 16/10;"
             loading="lazy" />
    </a>
    <div class="flex flex-col flex-1 p-5">
        <p class="text-[11px] font-bold text-orange-500 uppercase tracking-widest mb-2">
            {{ $actualite->category ?: 'Actualité' }}
            @if($actualite->created_at)
                <span class="text-gray-400 font-normal normal-case tracking-normal"> · {{ $actualite->created_at->translatedFormat('d/m/Y') }}</span>
            @endif
        </p>
        <h3 class="text-lg font-bold text-navy leading-snug line-clamp-3 mb-2">
            <a href="{{ route('actualites.show', $actualite->id) }}" class="hover:text-orange-500">{{ $actualite->title }}</a>
        </h3>
        @if($actualite->posted_in)
            <p class="text-sm text-gray-500 mb-4">
                <i class="fa-solid fa-location-dot text-xs text-navy mr-1" aria-hidden="true"></i>
                {{ $actualite->posted_in }}
            </p>
        @else
            <div class="mb-4"></div>
        @endif
        <div class="mt-auto">
            <a href="{{ route('actualites.show', $actualite->id) }}"
               class="inline-flex items-center gap-1.5 text-sm px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white font-medium">
                Lire la suite
            </a>
        </div>
    </div>
</article>
