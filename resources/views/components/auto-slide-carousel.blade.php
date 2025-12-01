<section class="bg-gray-100 rounded-lg fade-in">
    <div class="container mx-auto px-4">
    <div class="carousel carousel-center rounded-box space-x-4 p-4 overflow-x-auto relative">
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_6728.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_6916.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_6984.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_7117.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_6792.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_6888.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_6750.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
                <div class="carousel-item">
                    <img src="{{ asset('images/carousel/KEV_7080.jpg') }}" class="rounded-box w-64 h-40 object-cover" />
                </div>
            </div>
    </div>
</section>

<script>
    const carousel = document.querySelector('.carousel');
    const firstItem = carousel.querySelector('.carousel-item');

    let firstItemStyle = window.getComputedStyle(firstItem);
    let marginRight = parseInt(firstItemStyle.marginRight) || 16;
    let scrollAmount = firstItem.offsetWidth + marginRight;
    const scrollDelay = 2000;

    function autoScroll() {
        if (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 1) {
            carousel.scrollTo({ left: 0, behavior: 'smooth' });
        } else {
            carousel.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        }
    }

    let scrollInterval = setInterval(autoScroll, scrollDelay);

    carousel.addEventListener('mouseenter', () => clearInterval(scrollInterval));
    carousel.addEventListener('mouseleave', () => scrollInterval = setInterval(autoScroll, scrollDelay));

    window.addEventListener('resize', () => {
        firstItemStyle = window.getComputedStyle(firstItem);
        marginRight = parseInt(firstItemStyle.marginRight) || 16;
        scrollAmount = firstItem.offsetWidth + marginRight;
    });
</script>
