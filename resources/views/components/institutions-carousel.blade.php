<section class="fade-in py-4 md:py-4">
    <div class="relative overflow-hidden">
        <div class="absolute left-0 top-0 bottom-0 w-16 md:w-24 bg-gradient-to-r from-gray-100 to-transparent z-10 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-16 md:w-24 bg-gradient-to-l from-gray-100 to-transparent z-10 pointer-events-none"></div>

        <div class="carousel-container flex overflow-x-visible py-4">
            <div class="carousel-track flex items-start gap-8 md:gap-12 animate-scroll whitespace-nowrap"
                 style="animation-duration: {{ $speed }}s;">
                @for($i = 0; $i < 2; $i++)
                    @foreach ($institutions as $institution)
                        <div class="carousel-item flex flex-col items-center min-w-[180px] md:min-w-[200px] px-2">
                            <div class="logo-container w-24 h-24 md:w-28 md:h-28 mb-3 md:mb-4 flex items-center justify-center p-2 bg-white rounded-lg shadow-sm">
                                @if($institution['logo'] && file_exists(public_path($institution['logo'])))
                                    <img
                                        src="{{ asset($institution['logo']) }}"
                                        alt="{{ $institution['name'] }}"
                                        class="w-full h-full object-contain max-w-full max-h-full"
                                        loading="lazy"
                                        onerror="this.src='{{ asset('images/placeholder-logo.svg') }}'"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded-lg">
                                        <span class="text-gray-400 text-xs text-center">{{ $institution['name'] }}</span>
                                    </div>
                                @endif
                            </div>
                            <h6 class="institution-text text-center text-gray-700 font-medium text-xs md:text-sm leading-tight px-1 max-w-[180px]">
                                {{ $institution['name'] }}
                            </h6>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes scroll {
        0% {
            transform: translateX(0);
        }
        100% {
            transform: translateX(-50%);
        }
    }

    .animate-scroll {
        animation: scroll linear infinite;
        will-change: transform;
    }

    .carousel-container:hover .animate-scroll {
        animation-play-state: paused;
    }

    .carousel-item {
        flex-shrink: 0;
    }

    .institution-text {
        white-space: normal;
        line-height: 1.3;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .logo-container {
        transition: transform 0.3s ease;
    }

    .carousel-item .logo-container {
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
    }

    .carousel-item:hover .logo-container {
        transform: translateY(-5px);
    }
</style>
