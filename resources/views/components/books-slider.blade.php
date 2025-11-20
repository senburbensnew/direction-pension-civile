@props(['books'])

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .book-card {
        transition: all 0.3s ease;
        transform-style: preserve-3d;
    }

    .book-card:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .carousel-container {
        scroll-behavior: smooth;
    }

    .book-cover {
        perspective: 1000px;
    }

    .book-cover img {
        transform: rotateY(-10deg);
        box-shadow: 10px 10px 15px rgba(0, 0, 0, 0.3);
        transition: transform 0.3s ease;
    }

    .book-cover:hover img {
        transform: rotateY(0deg);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.5s ease-in-out;
    }

    .hide-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }

    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
</style>

<!-- Book Carousel Section -->
<div class="container mx-auto px-4 py-5">
    <div class="w-full px-4 py-5">

    <div class="relative">
        <!-- Navigation Buttons -->
        <button id="prevBtn"
            class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:bg-indigo-100 transition-colors">
            <i class="fas fa-chevron-left text-blue-900 text-xl"></i>
        </button>

        <button id="nextBtn"
            class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full w-12 h-12 flex items-center justify-center shadow-lg hover:bg-indigo-100 transition-colors">
            <i class="fas fa-chevron-right text-blue-900 text-xl"></i>
        </button>

        <!-- Carousel Content -->
        <div id="carousel"
            class="carousel-container overflow-x-auto whitespace-nowrap px-4 scroll-smooth hide-scrollbar">
                <x-carousel-item :imageUrls="[asset('images/photo_2025-11-18_23-36-39.jpg')]" />
                <x-carousel-item :imageUrls="[asset('images/photo_2025-11-18_23-36-43.jpg')]" />
                <x-carousel-item :imageUrls="[asset('images/photo_2025-11-18_23-36-46.jpg')]" />
                <x-carousel-item :imageUrls="[asset('images/photo_2025-11-18_23-36-49.jpg')]" />
                <x-carousel-item :imageUrls="[asset('images/photo_2025-11-18_23-36-52.jpg')]" />
                <x-carousel-item :imageUrls="[asset('images/photo_2025-11-18_23-36-55.jpg')]" />
        </div>

        <!-- Carousel Indicators -->
        <div class="flex justify-center mt-6 space-x-2">
            <button class="indicator w-3 h-3 rounded-full bg-gray-300 hover:bg-blue-900 transition-colors"
                data-index="0"></button>
            <button class="indicator w-3 h-3 rounded-full bg-gray-300 hover:bg-blue-900 transition-colors"
                data-index="1"></button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const carousel = document.getElementById('carousel');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const indicators = document.querySelectorAll('.indicator');
        const bookCards = document.querySelectorAll('.book-card');

        // Calculate card width including margins
        const cardStyle = window.getComputedStyle(bookCards[0]);
        const cardWidth = bookCards[0].offsetWidth +
            parseInt(cardStyle.marginLeft) +
            parseInt(cardStyle.marginRight);

        // Carousel configuration
        const visibleCards = 3;
        let currentIndex = 0;
        const maxIndex = Math.ceil(bookCards.length / visibleCards) - 1;

        function goToSlide(index) {
            currentIndex = Math.max(0, Math.min(index, maxIndex));
            const scrollPosition = currentIndex * cardWidth * visibleCards;
            carousel.scrollTo({
                left: scrollPosition,
                behavior: 'smooth'
            });
            updateIndicators();
        }

        function updateIndicators() {
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('bg-indigo-600', i === currentIndex);
                indicator.classList.toggle('bg-gray-300', i !== currentIndex);
            });
        }

        // Event listeners
        prevBtn.addEventListener('click', () => goToSlide(currentIndex - 1));
        nextBtn.addEventListener('click', () => goToSlide(currentIndex + 1));

        indicators.forEach(indicator => {
            indicator.addEventListener('click', () => {
                goToSlide(parseInt(indicator.dataset.index));
            });
        });

        // Auto-scroll management
        let autoScroll = setInterval(() => {
            goToSlide((currentIndex + 1) % (maxIndex + 1));
        }, 5000);

        carousel.addEventListener('mouseenter', () => clearInterval(autoScroll));
        carousel.addEventListener('mouseleave', () => {
            autoScroll = setInterval(() => {
                goToSlide((currentIndex + 1) % (maxIndex + 1));
            }, 5000);
        });

        // Initial setup
        updateIndicators();
    });
</script>
