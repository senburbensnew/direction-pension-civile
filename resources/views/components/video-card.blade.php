<div {{ $attributes->merge(['class' => 'w-full bg-white pt-0']) }}>
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
        <div class="relative">
            <iframe class="w-full aspect-video" src="{{ $videoUrl }}" style="border: none"
                allowfullscreen loading="lazy" title="{{ $title ?? 'Video' }}"></iframe>
        </div>
    @else
        <div class="aspect-video bg-gray-100 flex items-center justify-center">
            <span class="text-gray-500">No video available</span>
        </div>
    @endif
</div>
