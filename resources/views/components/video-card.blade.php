<div {{ $attributes->merge(['class' => 'w-full rounded-lg pt-0']) }}>
    <!-- Header -->
    @if ($title)
    <div class="bg-blue-900 text-white p-1">
        <h4 class="text-lg font-bold">{{ $title }}</h4>
    </div>
    @endif

    <!-- Video Embed -->
    @php
        if ($videoUrl && str_contains($videoUrl, 'youtube.com/watch?v=')) {
            $videoUrl = str_replace('watch?v=', 'embed/', $videoUrl);
        }
    @endphp

    @if ($videoUrl)
        <div class="relative rounded-lg overflow-hidden shadow-md">
            <iframe
                class="w-full aspect-video"
                src="{{ $videoUrl }}"
                title="{{ $title ?? 'Vidéo' }}"
                loading="lazy"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                style="border:0;">
            </iframe>
        </div>

    @else
        <div class="aspect-video bg-gray-100 flex items-center justify-center">
            <span class="text-gray-500">No video available</span>
        </div>
    @endif
</div>
