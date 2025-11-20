@props(['imageUrls'])

<div class="book-card inline-block w-64 bg-white rounded-lg overflow-hidden shadow-md mx-4 animate-fadeIn">
    <div class="book-cover p-6 book-image-container overflow-hidden">
        <img src="{{ $imageUrls[0] ?? 'https://via.placeholder.com/400x600' }}"
             alt="Book Cover"
             class="w-full h-auto object-cover object-top rounded">
    </div>
</div>
