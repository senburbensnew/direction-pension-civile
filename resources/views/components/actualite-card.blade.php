@props(['actualite'])

<article class="group bg-white rounded-xl overflow-hidden border border-gray-100 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5 flex flex-col">

    {{-- Cover image --}}
    <a href="{{ route('actualites.show', $actualite->id) }}" class="block relative overflow-hidden bg-gray-100" style="aspect-ratio: 16/9;">
        <img src="{{ $actualite->images->isNotEmpty() ? Storage::url($actualite->images->first()->image_path) : asset('images/image_placeholder.png') }}"
             alt="{{ $actualite->title }}"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
             loading="lazy" />
        @if($actualite->category)
            <span class="absolute top-3 left-3 px-2.5 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full shadow-sm">
                {{ $actualite->category }}
            </span>
        @endif
    </a>

    {{-- Body --}}
    <div class="flex flex-col flex-1 p-5">

        {{-- Meta --}}
        <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
            <i class="far fa-calendar-alt text-gray-300"></i>
            <time datetime="{{ $actualite->created_at->toIso8601String() }}">
                {{ $actualite->created_at->translatedFormat('d M Y') }}
            </time>
            @if($actualite->posted_in)
                <span class="text-gray-200">·</span>
                <span class="truncate">{{ $actualite->posted_in }}</span>
            @endif
        </div>

        {{-- Title --}}
        <h3 class="text-sm font-bold text-gray-900 mb-2 line-clamp-2 leading-snug group-hover:text-blue-700 transition-colors">
            <a href="{{ route('actualites.show', $actualite->id) }}">{{ $actualite->title }}</a>
        </h3>

        {{-- Excerpt --}}
        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2 flex-1 mb-4">
            {{ $actualite->description }}
        </p>

        {{-- CTA --}}
        <a href="{{ route('actualites.show', $actualite->id) }}"
           class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors mt-auto">
            Lire l'article
            <i class="fas fa-arrow-right text-[10px] transition-transform duration-300 group-hover:translate-x-1"></i>
        </a>
    </div>
</article>
