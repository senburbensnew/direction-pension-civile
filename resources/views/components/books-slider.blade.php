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
            class="border-red-600 carousel-container overflow-x-auto whitespace-nowrap px-4 scroll-smooth hide-scrollbar">
            <!-- Book 1 -->
            <div
                class="border-red-600 book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
                <div class="book-cover p-6">
                    <img src="https://m.media-amazon.com/images/I/71tbalAHYCL._AC_UF1000,1000_QL80_.jpg"
                        alt="Le Petit Prince" class="w-full h-64 object-cover rounded">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">Le Petit Prince</h3>
                    <p class="text-gray-600 text-sm">Antoine de Saint-Exupéry</p>
                </div>
            </div>

            <!-- Book 2 -->
            <div class="book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
                <div class="book-cover p-6">
                    <img src="https://m.media-amazon.com/images/I/71X1p4TGlxL._AC_UF1000,1000_QL80_.jpg" alt="1984"
                        class="w-full h-64 object-cover rounded">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">1984</h3>
                    <p class="text-gray-600 text-sm">George Orwell</p>
                </div>
            </div>

            <!-- Book 3 -->
            <div class="book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
                <div class="book-cover p-6">
                    <img src="https://m.media-amazon.com/images/I/81dQwQlmAXL._AC_UF1000,1000_QL80_.jpg"
                        alt="L'Étranger" class="w-full h-64 object-cover rounded">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">L'Étranger</h3>
                    <p class="text-gray-600 text-sm">Albert Camus</p>
                </div>
            </div>

            <!-- Book 4 -->
            <div class="book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
                <div class="book-cover p-6">
                    <img src="https://upload.wikimedia.org/wikipedia/en/6/6b/Harry_Potter_and_the_Philosopher%27s_Stone_Book_Cover.jpg"
                        alt="Harry Potter" class="w-full h-64 object-cover rounded">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">Harry Potter à l'école des sorciers</h3>
                    <p class="text-gray-600 text-sm">J.K. Rowling</p>
                </div>
            </div>

            <!-- Book 5 -->
            <div class="book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
                <div class="book-cover p-6">
                    <img src="https://upload.wikimedia.org/wikipedia/en/8/8e/The_Fellowship_of_the_Ring_cover.gif"
                        alt="Lord of the Rings" class="w-full h-64 object-cover rounded">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">Le Seigneur des Anneaux</h3>
                    <p class="text-gray-600 text-sm">J.R.R. Tolkien</p>
                </div>
            </div>

            <!-- Book 6 -->
            <div class="book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
                <div class="book-cover p-6">
                    <img src="https://upload.wikimedia.org/wikipedia/en/d/d0/Le_Comte_de_Monte-Cristo_2024_film_poster.jpg"
                        alt="Monte Cristo" class="w-full h-64 object-cover rounded">
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-lg truncate">Le Comte de Monte-Cristo</h3>
                    <p class="text-gray-600 text-sm">Alexandre Dumas</p>

                </div>
            </div>
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
