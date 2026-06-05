{{-- No @props needed — this component takes no inputs --}}

<style>
    .books-slider-track {
        -ms-overflow-style: none;
        scrollbar-width: none;
        scroll-behavior: smooth;
    }
    .books-slider-track::-webkit-scrollbar {
        display: none;
    }
    .book-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .book-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -8px rgba(0, 0, 0, 0.15);
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .animate-fadeIn {
        animation: fadeIn 0.5s ease-in-out;
    }
</style>

<div
    x-data="{
        current: 0,
        pageCount: 0,
        visibleCards: 3,
        touchStartX: 0,
        autoTimer: null,
        lbOpen: false,
        lbSrc: '',
        lbAlt: '',

        init() {
            this.$nextTick(() => {
                const cards = this.$refs.track.querySelectorAll('.book-card');
                this.pageCount = Math.max(1, Math.ceil(cards.length / this.visibleCards));
            });
            this.startAuto();
        },

        cardSlotWidth() {
            const card = this.$refs.track.querySelector('.book-card');
            if (!card) return 0;
            const s = getComputedStyle(card);
            return card.offsetWidth
                + parseInt(s.marginLeft  || 0)
                + parseInt(s.marginRight || 0);
        },

        goTo(index) {
            this.current = Math.max(0, Math.min(index, this.pageCount - 1));
            this.$refs.track.scrollTo({
                left: this.current * this.cardSlotWidth() * this.visibleCards,
                behavior: 'smooth'
            });
        },

        next() { this.goTo(this.current < this.pageCount - 1 ? this.current + 1 : 0); },
        prev() { this.goTo(this.current > 0 ? this.current - 1 : this.pageCount - 1); },

        startAuto()   { this.autoTimer = setInterval(() => this.next(), 5000); },
        stopAuto()    { clearInterval(this.autoTimer); },
        restartAuto() { this.stopAuto(); this.startAuto(); },

        onTouchStart(e) { this.touchStartX = e.touches[0].clientX; },
        onTouchEnd(e) {
            const delta = e.changedTouches[0].clientX - this.touchStartX;
            if (delta < -50) this.next();
            else if (delta > 50) this.prev();
            this.restartAuto();
        }
    }"
    @mouseenter="stopAuto()"
    @mouseleave="restartAuto()"
    @touchstart.passive="onTouchStart($event)"
    @touchend.passive="onTouchEnd($event)"
    @open-lightbox="lbOpen = true; lbSrc = $event.detail.src; lbAlt = $event.detail.alt"
    @keydown.escape.window="lbOpen = false"
    class="relative px-10 py-2"
>
    {{-- Prev button --}}
    <button
        @click="prev(); restartAuto()"
        class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:bg-indigo-50 transition-colors"
        aria-label="Précédent"
    >
        <i class="fas fa-chevron-left text-blue-900"></i>
    </button>

    {{-- Carousel track --}}
    <div x-ref="track" class="books-slider-track overflow-x-auto whitespace-nowrap py-4">
        @foreach($items as $item)
            <x-carousel-item
                src="{{ $item->fileUrl() }}"
                alt="{{ $item->title ?? 'Informations utiles' }}"
            />
        @endforeach
    </div>

    {{-- Next button --}}
    <button
        @click="next(); restartAuto()"
        class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:bg-indigo-50 transition-colors"
        aria-label="Suivant"
    >
        <i class="fas fa-chevron-right text-blue-900"></i>
    </button>

    {{-- Dot indicators — rendered once pageCount is known --}}
    <div class="flex justify-center mt-5 space-x-2" x-show="pageCount > 1">
        <template x-for="i in pageCount" :key="i">
            <button
                @click="goTo(i - 1); restartAuto()"
                :class="current === i - 1 ? 'bg-indigo-600 w-5' : 'bg-gray-300 w-3'"
                class="h-3 rounded-full transition-all duration-300 hover:bg-indigo-400"
                :aria-label="`Page ${i}`"
            ></button>
        </template>
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
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
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
            class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto"
        >
            {{-- Close button --}}
            <button
                @click="lbOpen = false"
                class="absolute top-3 right-3 z-10 bg-white/90 hover:bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-md transition-colors"
                aria-label="Fermer"
            >
                <i class="fas fa-times text-gray-600 text-sm"></i>
            </button>

            <img
                :src="lbSrc"
                :alt="lbAlt"
                class="w-full h-auto block rounded-2xl"
            >
        </div>
    </div>
</div>
