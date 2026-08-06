@props(['src', 'alt' => ''])

<div
    class="book-card inline-block w-72 sm:w-80 bg-white rounded-xl overflow-hidden shadow-md mx-3 animate-fadeIn align-top cursor-zoom-in border border-gray-100"
    @click="$dispatch('open-lightbox', { src: @js($src), alt: @js($alt) })"
>
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        class="w-full h-auto block"
        loading="lazy"
        onerror="this.onerror=null;this.src='{{ asset('images/image_placeholder.png') }}'"
    >
</div>
