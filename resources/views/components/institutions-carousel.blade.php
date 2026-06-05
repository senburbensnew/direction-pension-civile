<section class="fade-in py-4 md:py-6">
    <div class="relative overflow-hidden">
        {{-- Edge fade masks --}}
        <div class="absolute left-0 top-0 bottom-0 w-20 md:w-32 bg-gradient-to-r from-white to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-20 md:w-32 bg-gradient-to-l from-white to-transparent z-10 pointer-events-none"></div>

        <div class="carousel-outer overflow-hidden py-4">
            {{--
                Use margin-right on each item instead of flex gap.
                This makes every item occupy exactly (item-width + margin) space,
                so translateX(-50%) lands at exactly one copy width — no loop jump.
            --}}
            <div class="carousel-track" style="animation-duration: {{ $speed }}s;">
                @for($i = 0; $i < 2; $i++)
                    @foreach ($institutions as $institution)
                        @php
                            $rawLogo = $institution['logo'] ?? null;
                            if (!$rawLogo) {
                                $logoSrc = asset('images/setting-logo-1-M13oPLiYoM.png');
                            } elseif (str_starts_with($rawLogo, 'http') || str_starts_with($rawLogo, '/storage')) {
                                $logoSrc = $rawLogo;
                            } elseif (file_exists(public_path($rawLogo))) {
                                $logoSrc = asset($rawLogo);
                            } else {
                                $logoSrc = asset('images/setting-logo-1-M13oPLiYoM.png');
                            }
                        @endphp
                        <div class="carousel-item">
                            <div class="logo-wrap">
                                <img
                                    src="{{ $logoSrc }}"
                                    alt="{{ $institution['name'] }}"
                                    class="w-full h-full object-contain"
                                    loading="lazy"
                                    onerror="this.onerror=null;this.src='{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}'"
                                >
                            </div>
                            <p class="institution-name">{{ $institution['name'] }}</p>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes institutions-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }

    .carousel-track {
        display: flex;
        align-items: flex-start;
        /* width: max-content is required so that translateX(-50%) is relative to the
           full content width (all items), not the container width. Without it the
           animation jumps at a random mid-point instead of at the exact loop seam. */
        width: max-content;
        /* Do NOT use the animation shorthand — it resets animation-duration to 0s,
           overriding the inline style. Set sub-properties individually instead. */
        animation-name: institutions-scroll;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
        animation-play-state: running;
        will-change: transform;
    }

    .carousel-outer:hover .carousel-track {
        animation-play-state: paused;
    }

    /* Fixed item width + margin-right means every slot = 160px + 40px = 200px.
       Total track = 2N × 200px. translateX(-50%) = N × 200px = exactly one copy. */
    .carousel-item {
        flex-shrink: 0;
        width: 150px;
        margin-right: 32px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    @media (min-width: 768px) {
        .carousel-item {
            width: 160px;
            margin-right: 48px;
        }
    }

    .logo-wrap {
        width: 88px;
        height: 88px;
        margin-bottom: 12px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 8px;
        background: white;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.10);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    @media (min-width: 768px) {
        .logo-wrap {
            width: 96px;
            height: 96px;
        }
    }

    .carousel-item:hover .logo-wrap {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
    }

    .institution-name {
        text-align: center;
        color: #4b5563;
        font-size: 0.72rem;
        font-weight: 500;
        line-height: 1.35;
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
        max-width: 150px;
    }

    @media (min-width: 768px) {
        .institution-name {
            font-size: 0.75rem;
            max-width: 160px;
        }
    }
</style>
