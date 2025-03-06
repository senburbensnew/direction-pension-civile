<div {{ $attributes->merge(['class' => 'w-full md:w-2/4 bg-white p-4 pt-0']) }}>
    <!-- Header -->
    <div class="bg-blue-900 text-white p-1">
        <h4 class="text-lg font-bold">{{ $title }}</h4>
    </div>

    <!-- Video Embed -->
    @if ($videoUrl)
        <div class="relative">
            <iframe class="w-full aspect-video" src="{{ $videoUrl }}" style="border: none" allowfullscreen
                loading="lazy" title="{{ $title }}"></iframe>
        </div>
    @else
        <div class="aspect-video bg-gray-100 flex items-center justify-center">
            <span class="text-gray-500">No video available</span>
        </div>
    @endif
</div>
