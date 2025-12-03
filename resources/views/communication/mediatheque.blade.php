@extends('layouts.main')

@section('title', 'Médiathèque')

@section('content')
<style>
    .media-card {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .media-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(to bottom, #3b82f6, #1e40af);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }
    .media-card:hover::before {
        transform: scaleY(1);
    }
    .media-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
    }
    .category-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
        font-weight: 500;
    }
    .gradient-bg {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
    }
    .gradient-text {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .card-shadow {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .search-box {
        transition: all 0.3s ease;
    }
    .search-box:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
    }
    .filter-btn {
        transition: all 0.2s ease;
    }
    .filter-btn.active {
        background-color: #3b82f6;
        color: white;
    }
    .fade-in {
        animation: fadeIn 0.5s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .section-divider {
        height: 1px;
        background: linear-gradient(to right, transparent, #d1d5db, transparent);
        margin: 2rem 0;
    }
</style>

<div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <header class="mb-10 text-center fade-in">
        <p class="text-lg text-gray-600 max-w-3xl mx-auto">Accédez à toutes les images, vidéos et audios disponibles</p>

        <!-- Search and Filters -->
        <div class="mt-8 max-w-2xl mx-auto">
            <div class="relative">
                <input type="text" id="search-media" placeholder="Rechercher un média..."
                       class="w-full p-4 pl-12 rounded-xl border border-gray-300 search-box focus:outline-none">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>

            <div class="flex flex-wrap justify-center gap-2 mt-4">
                <button class="filter-btn active px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="all">Tous</button>
                <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="image">Images</button>
                <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="video">Vidéos</button>
                <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="audio">Audios</button>
            </div>
        </div>
    </header>

    <!-- Media Grid -->
    <section id="media-library" class="fade-in">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($images as $image)
            <div class="media-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="image">
                <span class="category-badge bg-blue-100 text-blue-800">Image</span>
                <img src="{{ asset('media/images/' . $image->getFilename()) }}" class="w-full h-48 object-cover rounded mb-4">
                <h3 class="text-md font-semibold text-gray-800 leading-tight truncate">{{ $image->getFilename() }}</h3>
            </div>
            @endforeach

            @foreach($videos as $video)
            <div class="media-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="video">
                <span class="category-badge bg-green-100 text-green-800">Vidéo</span>
                <video controls class="w-full h-48 rounded mb-4">
                    <source src="{{ asset('media/videos/' . $video->getFilename()) }}" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture vidéo.
                </video>
                <h3 class="text-md font-semibold text-gray-800 leading-tight truncate">{{ $video->getFilename() }}</h3>
            </div>
            @endforeach

            @foreach($audios as $audio)
            <div class="media-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="audio">
                <span class="category-badge bg-purple-100 text-purple-800">Audio</span>
                <div class="flex items-center mb-4">
                    <i class="fas fa-music text-purple-600 text-xl mr-3"></i>
                    <h3 class="text-md font-semibold text-gray-800 leading-tight truncate">{{ $audio->getFilename() }}</h3>
                </div>
                <audio controls class="w-full">
                    <source src="{{ asset('media/audios/' . $audio->getFilename()) }}" type="audio/mpeg">
                    Votre navigateur ne supporte pas la lecture audio.
                </audio>
            </div>
            @endforeach
        </div>
    </section>

    <div class="section-divider"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search-media');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const mediaCards = document.querySelectorAll('.media-card');

    function filterMedia() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('.filter-btn.active').getAttribute('data-filter');

        mediaCards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const category = card.getAttribute('data-category');
            const matchesSearch = title.includes(searchTerm);
            const matchesFilter = activeFilter === 'all' || category === activeFilter;

            card.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
        });
    }

    searchInput.addEventListener('input', filterMedia);

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            filterMedia();
        });
    });
});
</script>
@endsection
