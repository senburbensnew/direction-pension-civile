<div
    x-data="{
        current: 0,
        total: 0,
        slides: [],
        timer: null,
        touchStartX: 0,

        init() {
            this.$nextTick(() => {
                this.slides = Array.from(this.$refs.wrapper.querySelectorAll('.swiper-slide'));
                this.total = this.slides.length;
                this.applySlide(0);
                this.startAuto();
            });
        },

        applySlide(index) {
            this.slides.forEach((slide, i) => {
                slide.style.opacity  = i === index ? '1' : '0';
                slide.style.zIndex   = i === index ? '1' : '0';
                slide.style.pointerEvents = i === index ? 'auto' : 'none';
            });
            this.current = index;
        },

        goTo(index) {
            this.applySlide((index + this.total) % this.total);
        },

        next() { this.goTo(this.current + 1); },
        prev() { this.goTo(this.current - 1); },

        startAuto()   { this.timer = setInterval(() => this.next(), 6000); },
        stopAuto()    { clearInterval(this.timer); },
        restartAuto() { this.stopAuto(); this.startAuto(); },

        onTouchStart(e) { this.touchStartX = e.touches[0].clientX; },
        onTouchEnd(e) {
            const delta = e.changedTouches[0].clientX - this.touchStartX;
            if (delta < -50)      { this.next(); this.restartAuto(); }
            else if (delta > 50)  { this.prev(); this.restartAuto(); }
        }
    }"
    @mouseenter="stopAuto()"
    @mouseleave="restartAuto()"
    @touchstart.passive="onTouchStart($event)"
    @touchend.passive="onTouchEnd($event)"
    class="hero-carousel relative overflow-hidden w-full"
>
    {{-- Slides --}}
    <div x-ref="wrapper" class="relative w-full h-full">
        {{ $slot }}
    </div>

    {{-- Prev button --}}
    <button
        @click="prev(); restartAuto()"
        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 bg-black/30 hover:bg-black/50 text-white rounded-full w-10 h-10 flex items-center justify-center transition-colors backdrop-blur-sm"
        aria-label="Précédent"
    >
        <i class="fas fa-chevron-left text-sm"></i>
    </button>

    {{-- Next button --}}
    <button
        @click="next(); restartAuto()"
        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 bg-black/30 hover:bg-black/50 text-white rounded-full w-10 h-10 flex items-center justify-center transition-colors backdrop-blur-sm"
        aria-label="Suivant"
    >
        <i class="fas fa-chevron-right text-sm"></i>
    </button>

    {{-- Dot indicators --}}
    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex items-center gap-2">
        <template x-for="i in total" :key="i">
            <button
                @click="goTo(i - 1); restartAuto()"
                :class="current === i - 1 ? 'bg-white w-5' : 'bg-white/50 w-3'"
                class="h-3 rounded-full transition-all duration-300 hover:bg-white/80"
                :aria-label="`Diapositive ${i}`"
            ></button>
        </template>
    </div>
</div>

<style>
    .hero-carousel {
        height: 280px;
    }
    @media (min-width: 768px)  { .hero-carousel { height: 380px; } }
    @media (min-width: 1024px) { .hero-carousel { height: 480px; } }

    /* Slides stack on top of each other and crossfade */
    .hero-carousel .swiper-slide {
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.7s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .hero-carousel .swiper-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
    }
</style>
