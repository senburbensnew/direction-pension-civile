@props(['actualite'])

<article class="group bg-white rounded-2xl overflow-visible flex flex-col border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300">

    <div class="relative">
        <a href="{{ route('actualites.show', $actualite->id) }}" class="block overflow-hidden bg-gray-100 rounded-t-2xl">
            <img src="{{ $actualite->coverUrl() }}"
                 alt="{{ $actualite->title }}"
                 class="w-full object-cover transition-transform duration-500 group-hover:scale-105"
                 style="aspect-ratio: 16/10;"
                 loading="lazy" />
        </a>
        <time datetime="{{ $actualite->created_at->toIso8601String() }}"
              class="absolute left-0 bottom-0 translate-y-1/2 z-10 bg-[#173052] text-white text-[11px] font-bold tracking-wide px-3.5 py-1.5">
            {{ $actualite->created_at->translatedFormat('F d, Y') }}
        </time>
    </div>

    <div class="flex flex-col flex-1 px-5 pt-8 pb-6">

        @if($actualite->category || $actualite->posted_in)
            <p class="text-xs text-gray-400 mb-3">
                @if($actualite->category)
                    Dans {{ $actualite->category }}
                @endif
                @if($actualite->category && $actualite->posted_in)
                    <span class="mx-1.5">·</span>
                @endif
                @if($actualite->posted_in)
                    {{ $actualite->posted_in }}
                @endif
            </p>
        @else
            <p class="text-xs text-gray-400 mb-3">Actualité</p>
        @endif

        <h3 class="text-[15px] md:text-base font-bold text-gray-900 leading-snug line-clamp-3 mb-6 group-hover:text-blue-700 transition-colors">
            <a href="{{ route('actualites.show', $actualite->id) }}">{{ $actualite->title }}</a>
        </h3>

        <a href="{{ route('actualites.show', $actualite->id) }}"
           class="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-orange-500 transition-colors">
            Lire la suite
            <i class="fas fa-arrow-right text-[10px] transition-transform duration-300 group-hover:translate-x-1"></i>
        </a>
    </div>
</article>
