
<div
    x-data="{ lbOpen: false, lbSrc: '', lbAlt: '' }"
    @open-slide-lightbox="lbOpen = true; lbSrc = $event.detail.src; lbAlt = $event.detail.alt"
    @keydown.escape.window="lbOpen = false"
>
    <div class="relative overflow-hidden rounded-xl">
        {{-- Edge fade masks --}}
        <div class="absolute left-0 top-0 bottom-0 w-16 md:w-24 bg-gradient-to-r from-gray-50 to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-16 md:w-24 bg-gradient-to-l from-gray-50 to-transparent z-10 pointer-events-none"></div>

        <div class="overflow-hidden py-3">
            {{--
                Duplicate images twice + use margin-right (not gap) so every slot is
                exactly (width + margin) wide and translateX(-50%) lands at exactly
                one copy — seamless infinite loop, no visible reset jump.
            --}}
            <div class="asc-track">
                @for ($i = 0; $i < 2; $i++)
                    @foreach ($images as $image)
                        <div
                            class="asc-item cursor-zoom-in"
                            @click="$dispatch('open-slide-lightbox', { src: '{{ $image['src'] }}', alt: '{{ $image['alt'] }}' })"
                        >
                            <img
                                src="{{ $image['src'] }}"
                                alt="{{ $image['alt'] }}"
                                class="w-full h-full object-cover rounded-lg transition-transform duration-300 hover:scale-105"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='{{ asset('images/image_placeholder.png') }}'"
                            >
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </div>

    {{-- Lightbox modal --}}
    <div
        x-show="lbOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="lbOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4"
        x-cloak
    >
        <div
            x-show="lbOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative max-w-3xl w-full"
        >
            <button
                @click="lbOpen = false"
                class="absolute -top-3 -right-3 z-10 bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:bg-gray-100 transition-colors"
                aria-label="Fermer"
            >
                <i class="fas fa-times text-gray-600 text-sm"></i>
            </button>
            <img
                :src="lbSrc"
                :alt="lbAlt"
                class="w-full h-auto rounded-xl shadow-2xl block"
            >
        </div>
    </div>
</div>

<style>
    @keyframes asc-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }

    .asc-track {
        display: flex;
        align-items: center;
        /* width: max-content ensures translateX(-50%) is relative to content width,
           not the container — required for the seamless loop calculation. */
        width: max-content;
        animation-name: asc-scroll;
        animation-duration: 30s;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
        will-change: transform;
    }

    .asc-track:hover {
        animation-play-state: paused;
    }

    /* Each slot = width + margin-right, both copies identical → -50% = exactly one copy. */
    .asc-item {
        flex-shrink: 0;
        width: 280px;
        height: 180px;
        margin-right: 16px;
        overflow: hidden;
        border-radius: 8px;
    }

    @media (min-width: 768px) {
        .asc-item {
            width: 340px;
            height: 220px;
        }
        .asc-track {
            animation-duration: 40s;
        }
    }
</style>
