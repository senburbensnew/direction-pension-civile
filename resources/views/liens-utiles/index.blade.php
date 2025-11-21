@extends('layouts.main')

@section('title', 'Liens Utiles')

@section('content')
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #074482 0%, #0d6efd 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #074482 0%, #0d6efd 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .card-shadow {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .link-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .link-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, #074482, #0d6efd);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .link-card:hover::before {
            transform: scaleY(1);
        }

        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.15);
        }

        .search-box {
            transition: all 0.3s ease;
        }

        .search-box:focus {
            box-shadow: 0 0 0 3px rgba(7, 68, 130, 0.2);
        }

        .category-badge {
            font-size: 0.7rem;
            padding: 4px 10px;
            border-radius: 12px;
            font-weight: 500;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .filter-btn {
            transition: all 0.2s ease;
        }

        .filter-btn.active {
            background-color: #074482;
            color: white;
        }
    </style>

    <!-- Header Section -->
{{--     <section class="gradient-bg text-white py-12 fade-in">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ __('messages.useful_links') }}</h1>
            <p class="text-xl text-blue-100 max-w-2xl mx-auto">
                Accédez rapidement aux ressources et services en ligne essentiels
            </p>
        </div>
    </section> --}}

    <!-- Search and Filters -->
    <section class="max-w-7xl mx-auto px-6 fade-in">
        <div class="bg-white rounded-2xl card-shadow p-6 mb-8">
            <div class="flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="w-full md:w-auto md:flex-1 max-w-2xl">
                    <div class="relative">
                        <input type="text" id="search-links" placeholder="Rechercher un lien..."
                               class="w-full p-4 pl-12 rounded-xl border border-gray-300 search-box focus:outline-none">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button class="filter-btn active px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="all">
                        Tous les liens
                    </button>
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="government">
                        Gouvernement
                    </button>
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="services">
                        Services
                    </button>
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="resources">
                        Ressources
                    </button>
                </div>
            </div>
        </div>

        <!-- Links Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($links as $link)
                @php
                    // Determine category based on link name or abbreviation for demo purposes
                    $categories = ['government', 'services', 'resources'];
                    $category = $categories[array_rand($categories)];
                    $categoryColors = [
                        'government' => ['bg-blue-100 text-blue-800', 'Gouvernement'],
                        'services' => ['bg-green-100 text-green-800', 'Services'],
                        'resources' => ['bg-purple-100 text-purple-800', 'Ressources']
                    ];
                @endphp

                <div class="link-card bg-white border border-gray-200 rounded-xl p-6 card-shadow" data-category="{{ $category }}">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-external-link-alt text-blue-600 text-lg"></i>
                        </div>
                        <span class="category-badge {{ $categoryColors[$category][0] }}">
                            {{ $categoryColors[$category][1] }}
                        </span>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $link['name'] }}</h3>
                    <p class="text-sm text-gray-600 mb-4">{{ $link['abbr'] }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <x-tooltip :text="$link['abbr']">
                            <a href="{{ $link['link'] }}" target="_blank"
                               class="flex items-center gap-2 text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                <span>Visiter le site</span>
                                <i class="fas fa-arrow-right text-sm"></i>
                            </a>
                        </x-tooltip>

                        <button class="text-gray-400 hover:text-blue-600 transition-colors"
                                onclick="copyToClipboard('{{ $link['link'] }}')"
                                title="Copier le lien">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Quick Access Section -->
        <div class="mt-12 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-8 card-shadow">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold gradient-text mb-2">Accès Rapide</h2>
                <p class="text-gray-600">Liens les plus utilisés</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach (array_slice($links, 0, 8) as $link)
                    <a href="{{ $link['link'] }}" target="_blank"
                       class="bg-white rounded-lg p-4 text-center hover:shadow-md transition-all duration-300 group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-blue-600 transition-colors">
                            <i class="fas fa-external-link-alt text-blue-600 group-hover:text-white transition-colors"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600 transition-colors">
                            {{ $link['abbr'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Information Section -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-xl p-6">
            <div class="flex items-start">
                <div class="bg-blue-100 rounded-lg p-3 mr-4 flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="text-blue-800 font-semibold mb-2 text-lg">Comment utiliser cette page</h4>
                    <p class="text-blue-700">
                        Utilisez la barre de recherche pour trouver rapidement un lien spécifique, ou filtrez par catégorie.
                        Cliquez sur l'icône de copie <i class="fas fa-copy text-blue-600"></i> pour copier l'URL d'un lien.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <script>
        // Links search and filtering functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-links');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const linkCards = document.querySelectorAll('.link-card');

            // Search functionality
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                linkCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const abbr = card.querySelector('p').textContent.toLowerCase();
                    const category = card.getAttribute('data-category');
                    const isVisible = title.includes(searchTerm) || abbr.includes(searchTerm);

                    card.style.display = isVisible ? 'block' : 'none';
                });
            });

            // Filter functionality
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');

                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');

                    // Filter cards
                    linkCards.forEach(card => {
                        const category = card.getAttribute('data-category');

                        if (filter === 'all' || category === filter) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message (you could implement a toast notification here)
                alert('Lien copié dans le presse-papier !');
            }, function(err) {
                console.error('Erreur lors de la copie: ', err);
            });
        }
    </script>
@endsection
