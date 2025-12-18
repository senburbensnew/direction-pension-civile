@props(['actualite'])
    <!--     <article class="bg-gray-50 rounded-lg shadow p-6 flex flex-col">
        <img src="{{ $actualite->images->isNotEmpty() ? Storage::url($actualite->images->first()->image_path) : asset('images/image_placeholder.png') }}"
            alt="{{ $actualite->title }}" class="rounded mb-4 w-full h-40 object-cover" />
        <div class="text-sm text-gray-500 mb-2 flex flex-wrap gap-2">
@if($actualite->category)
                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded">
                    {{ $actualite->category }}
                </span>
@endif

@if($actualite->published_at)
                <span>
                    {{ $actualite->published_at->translatedFormat('d M Y') }}
                </span>
@endif
        </div>

        <h3 class="text-lg font-semibold mb-2 flex-1">
            {{ $actualite->title }}
        </h3>

        <p class="text-gray-700 mb-4">
            {{ Str::limit($actualite->description, 80) }}
        </p>

        <div class="mt-auto flex items-center justify-between">
@if($actualite->posted_in)
                <span class="text-sm text-gray-500 italic">
                    {{ $actualite->posted_in }}
                </span>
@endif

            <a href="{{ route('actualites.show', $actualite->id) }}"
                class="text-blue-600 font-medium hover:underline">
                Lire →
            </a>
        </div>
    </article> -->

<article class="relative h-64 rounded-lg overflow-hidden group shadow-md">

    <!-- Background image -->
    <div
        class="absolute inset-0 bg-cover bg-center
               transition-transform duration-500
               group-hover:scale-105"
        style="background-image: url('{{ $actualite->images->isNotEmpty()
            ? Storage::url($actualite->images->first()->image_path)
            : asset('images/image_placeholder.png') }}')">
    </div>

    <!-- Top overlay (very light blur) -->
    <div
        class="absolute top-0 left-0 w-full p-2 flex justify-between items-center
                backdrop-blur-[2px]
               text-sm text-gray-700 z-10">

        @if($actualite->category)
            <span class="px-2 py-1 bg-blue-100/80 text-blue-700 rounded">
                {{ $actualite->category }}
            </span>
        @endif

        @if($actualite->created_at)
            <span class="px-2 py-1 bg-blue-100/80 text-blue-700 rounded">
                {{ $actualite->created_at->translatedFormat('d M Y') }}
            </span>
        @endif
    </div>

    <!-- Bottom overlay (subtle glass + gradient) -->
    <div
        class="absolute bottom-0 left-0 w-full p-3
               bg-gradient-to-t from-white/40 via-white/25 to-white/10
               backdrop-blur-[3px]
               transition-all duration-300
               group-hover:backdrop-blur-[4px]
               z-10">

        <!-- Text content -->
        <div>
            <!-- Title -->
            <h3
                title="{{ $actualite->title }}"
                class="text-lg font-semibold text-gray-900 leading-tight
                       line-clamp-1
                       transition-all duration-300
                       group-hover:line-clamp-none">
                {{ $actualite->title }}
            </h3>

            <!-- Description -->
            <p
                class="relative text-gray-800 text-sm mt-1
                       overflow-hidden
                       line-clamp-2
                       max-h-[3rem]
                       transition-all duration-500 ease-in-out
                       group-hover:line-clamp-none
                       group-hover:max-h-[12rem]">
                {{ $actualite->description }}
            </p>
        </div>

        <!-- Footer -->
        <div class="mt-2 flex items-center justify-between">
            @if($actualite->posted_in)
                <span class="text-xs text-blue-700 italic">
                    {{ $actualite->posted_in }}
                </span>
            @endif

            <a
                href="{{ route('actualites.show', $actualite->id) }}"
                class="text-blue-700 font-medium hover:underline">
                Lire →
            </a>
        </div>
    </div>

</article>

