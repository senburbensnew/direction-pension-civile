@props(['src', 'alt' => ''])

<div
    class="book-card inline-block w-64 bg-white rounded-xl overflow-hidden shadow-md mx-3 animate-fadeIn align-top cursor-zoom-in"
    @click="$dispatch('open-lightbox', { src: '{{ $src }}', alt: '{{ $alt }}' })"
>
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        class="w-full h-auto block"
        loading="lazy"
        onerror="this.onerror=null;this.src='{{ asset('images/image_placeholder.png') }}'"
    >
</div>
