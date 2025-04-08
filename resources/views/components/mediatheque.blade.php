<div class="grid grid-cols-1 gap-6 md:grid-cols-5 lg:gap-8">
    <!-- Books Slider - 4/5 width on desktop -->
    <div
        class="md:col-span-4 h-full relative after:absolute after:top-1/2 after:-right-3 after:-translate-y-1/2 after:w-px after:h-4/5 after:bg-gray-200 after:hidden md:after:block lg:after:-right-4">
        <x-books-slider />
    </div>

    <!-- Video Section - 1/5 width on desktop -->
    <div class="aspect-video w-full md:col-span-1 md:mt-11">
        <x-video-card title="Mediatheque/video" videoUrl="https://www.youtube.com/embed/zpVPYB2_ztM" />
    </div>
</div>
