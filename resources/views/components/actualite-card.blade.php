@props(['actualite'])

<article 
    class="group relative h-72 rounded-xl overflow-hidden shadow-lg transition-all duration-300 hover:shadow-2xl"
    aria-labelledby="actualite-{{ $actualite->id }}"
>
    <!-- Background image with overlay -->
    <div class="absolute inset-0">
        <img 
            src="{{ $actualite->images->isNotEmpty() ? Storage::url($actualite->images->first()->image_path) : asset('images/image_placeholder.png') }}"
            alt="{{ $actualite->title }}"
            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110"
            loading="lazy"
        />
        <!-- Gradient overlay for better text contrast -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
    </div>

    <!-- Top metadata -->
    <div class="absolute top-0 left-0 right-0 p-4 flex justify-between items-start z-20">
        @if($actualite->category)
            <span class="px-3 py-1.5 bg-white/90 backdrop-blur-sm text-gray-900 text-xs font-semibold rounded-full shadow-sm">
                {{ $actualite->category }}
            </span>
        @endif
        
        @if($actualite->created_at)
            <time 
                datetime="{{ $actualite->created_at->toIso8601String() }}"
                class="px-3 py-1.5 bg-black/60 backdrop-blur-sm text-white text-xs rounded-full"
            >
                {{ $actualite->created_at->translatedFormat('d M Y') }}
            </time>
        @endif
    </div>

    <!-- Content container -->
    <div class="absolute bottom-0 left-0 right-0 p-6 z-10 transform translate-y-2 transition-transform duration-500 group-hover:translate-y-0">
        <!-- Title -->
        <h3 
            id="actualite-{{ $actualite->id }}"
            class="text-xl font-bold text-white mb-3 line-clamp-2 group-hover:line-clamp-none transition-all duration-300"
        >
            {{ $actualite->title }}
        </h3>

        <!-- Description -->
        <div class="overflow-hidden">
            <p class="text-gray-200 text-sm leading-relaxed opacity-0 max-h-0 group-hover:opacity-100 group-hover:max-h-32 transition-all duration-500 delay-100">
                {{ $actualite->description }}
            </p>
        </div>

        <!-- Footer with link -->
        <div class="flex items-center justify-between mt-4 pt-4 border-t border-white/20">
            @if($actualite->posted_in)
                <span class="text-xs text-gray-300 italic font-medium">
                    {{ $actualite->posted_in }}
                </span>
            @endif
            
            <a 
                href="{{ route('actualites.show', $actualite->id) }}"
                class="inline-flex items-center text-white font-semibold text-sm hover:text-blue-300 transition-colors"
                aria-label="Lire {{ $actualite->title }}"
            >
                Lire l'article
                <svg 
                    class="w-4 h-4 ml-2 transform transition-transform group-hover:translate-x-1" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Hover effect indicator -->
    <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/30 transition-all duration-300 rounded-xl pointer-events-none"></div>
</article>