@props(['actualite'])
    <article class="bg-gray-50 rounded-lg shadow p-6 flex flex-col">
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
    </article>