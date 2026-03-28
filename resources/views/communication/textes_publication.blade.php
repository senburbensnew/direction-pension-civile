@extends('layouts.main')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }

        .document-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .document-card::before {
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

        .document-card:hover::before {
            transform: scaleY(1);
        }

        .document-card:hover {
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

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 bg-white">
        <!-- Header -->
        <header class="mb-10 text-center fade-in">
            <p class="text-lg text-gray-600 max-w-3xl mx-auto">Accédez à tous les documents officiels relatifs à la fonction publique et aux pensions de retraite</p>

            <!-- Search and Filters -->
            <div class="mt-8 max-w-2xl mx-auto">
                <div class="relative">
                    <input type="text" id="search-documents" placeholder="Rechercher un document..."
                           class="w-full p-4 pl-12 rounded-xl border border-gray-300 search-box focus:outline-none">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                </div>

                <div class="flex flex-wrap justify-center gap-2 mt-4">
                    <button class="filter-btn active px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="all">
                        Tous les documents
                    </button>
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="legal">
                        Documents légaux
                    </button>
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="avis">
                        Avis de publication
                    </button>
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="other">
                        Autres documents
                    </button>
                </div>
            </div>
        </header>


        <!-- Legal Documents Section -->
        <section id="legal-documents"  class="mb-12 fade-in">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                
                <!-- Section Header -->
                <div class="bg-gray-100 text-gray-800 p-6 border-b">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-lg p-3 mr-4">
                            <i class="fas fa-gavel text-gray-700 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Documents Légaux Officiels</h2>
                            <p class="text-gray-500 mt-1">
                                Textes réglementaires et décrets en vigueur
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Documents Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <!-- CARD 1 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="legal">
                            <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">Comptabilité</span>
                            <div class="flex items-start mb-4 mt-2">
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Arrêté sur la comptabilité publique
                                </h3>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'arrete-sur-la-comptabilite-pub.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('documents.download', 'arrete-sur-la-comptabilite-pub.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 2 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded">Organisation</span>
                            <div class="flex items-start mb-4 mt-2">
 
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Décret portant organisation de l'administration centrale de l'État
                                </h3>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'decret-portant-organisation-de-l-administration-centrale-de-l-etat.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('documents.download', 'decret-portant-organisation-de-l-administration-centrale-de-l-etat.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 3 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Déontologie</span>
                            <div class="flex items-start mb-4 mt-2">
     
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Arrêté définissant la règle déontologique applicable aux agents de la fonction publique
                                </h3>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'arrete-definissant-la-regle-deontologique-applicable-aux-agents-de-la-fp.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('documents.download', 'arrete-definissant-la-regle-deontologique-applicable-aux-agents-de-la-fp.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 4 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded">Ministère</span>
                            <div class="flex items-start mb-4 mt-2">
                              
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Décret réorganisant le ministère de l'Économie et des Finances
                                </h3>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'decret-reorganisant-le-mef.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('documents.download', 'decret-reorganisant-le-mef.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 5 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">Pension</span>
                            <div class="flex items-start mb-4 mt-2">
                              
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Décret du 09 octobre 2015 sur la pension civile de retraite
                                </h3>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'decret-su-09-octobre-2015-sur-la-pension-civile-de-retraite.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('documents.download', 'decret-su-09-octobre-2015-sur-la-pension-civile-de-retraite.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 6 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded">Statut</span>
                            <div class="flex items-start mb-4 mt-2">
                             
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Décret portant révision du Statut Général de la Fonction Publique
                                </h3>
                            </div>
                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'statut-general-fonction-publique-2005.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>
                                <a href="{{ route('documents.download', 'statut-general-fonction-publique-2005.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- Section Divider -->
        <div class="section-divider"></div>
        
        <!-- Avis de Publication Section -->
        <section id="avis-publication" data-category="avis" class="mb-12 fade-in">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                
                <!-- Section Header -->
                <div class="bg-gray-100 text-gray-800 p-6 border-b">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-lg p-3 mr-4">
                            <i class="fas fa-bullhorn text-gray-700 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Avis de Publication</h2>
                            <p class="text-gray-500 mt-1">
                                Avis officiels publiés par la Direction de la Pension Civile
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Documents Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <!-- SAMPLE CARD -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="avis">
                            
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                Avis Officiel
                            </span>

                            <div class="flex items-start mb-4 mt-2">
                               
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Avis de liquidation No 2 publié le 4 janivier 2024
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'avis-de-liquidation-no-2-publie-le-4-janvier-2024.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'avis-de-liquidation-no-2-publie-le-4-janvier-2024.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="avis">
                            
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                Avis Officiel
                            </span>

                            <div class="flex items-start mb-4 mt-2">
                               
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Avis de liquidation No 4 publié le 10 fevrier 2026
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'avis-de-liquidation-no-4-publie-le-10-fevrier-2026.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'avis-de-liquidation-no-4-publie-le-10-fevrier-2026.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="avis">
                            
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                Avis Officiel
                            </span>

                            <div class="flex items-start mb-4 mt-2">
                               
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Avis de liquidation No 56 publié le 28 août 2025
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'avis-de-liquidation-no-56-publie-le-28-aout-2025.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'avis-de-liquidation-no-56-publie-le-28-aout-2025.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="avis">
                            
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                Avis Officiel
                            </span>

                            <div class="flex items-start mb-4 mt-2">
                               
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Avis rectificatif No 2 publié le  6 Jan 2026
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'avis-rectificatif-no-2-publie-le-6-jan-2026.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'avis-rectificatif-no-2-publie-le-6-jan-2026.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="avis">
                            
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded">
                                Avis Officiel
                            </span>

                            <div class="flex items-start mb-4 mt-2">
                               
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Avis Rectificatif No 22 publié le 24 mai  2024
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'avis-rectificatif-no-22-publie-le-24-mai-2024.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'avis-rectificatif-no-22-publie-le-24-mai-2024.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- 👉 Duplicate this card dynamically for each PDF -->

                    </div>
                </div>
            </div>
        </section>

        <!-- Section Divider -->
        <div class="section-divider"></div>

        <!-- Other Documents Section -->
        <section id="other-documents" data-category="other" class="mb-12 fade-in">
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                
                <!-- Section Header -->
                <div class="bg-gray-100 text-gray-800 p-6 border-b">
                    <div class="flex items-center">
                        <div class="bg-gray-200 rounded-lg p-3 mr-4">
                            <i class="fas fa-info-circle text-gray-700 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Guides et Informations</h2>
                            <p class="text-gray-500 mt-1">
                                Documents d'information et conseils pratiques
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Documents Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                        <!-- CARD 1 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition" data-category="other">
                            <span class="bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded">Conseils</span>
                            
                            <div class="flex items-start mb-4 mt-2">
                                
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Conseils pour les Retraités et Futurs Retraités
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'Conseils_pour_les_Retraités_et_Futurs_Retraites.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'Conseils_pour_les_Retraités_et_Futurs_Retraites.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 2 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md transition">
                            <span class="bg-indigo-100 text-indigo-800 text-xs px-2 py-1 rounded">Droits</span>
                            
                            <div class="flex items-start mb-4 mt-2">
                               
                                <h3 class="text-sm font-semibold text-gray-800">
                                    Vos droits à la retraite
                                </h3>
                            </div>

                            <div class="flex space-x-2 mt-4">
                                <a href="{{ route('documents.view', 'VOS_DROITS_A_LA_RETRAITE.pdf') }}"
                                target="_blank"
                                class="flex-1 bg-blue-500 text-white py-2 rounded-lg text-sm text-center hover:bg-emerald-600 transition">
                                    <i class="fas fa-eye mr-1"></i> Voir
                                </a>

                                <a href="{{ route('documents.download', 'VOS_DROITS_A_LA_RETRAITE.pdf') }}"
                                class="bg-gray-100 px-3 rounded-lg hover:bg-gray-200 flex items-center">
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-documents');
            const filterButtons = document.querySelectorAll('.filter-btn');
            const documentCards = document.querySelectorAll('.document-card');
            const sections = document.querySelectorAll('section');

            function updateSectionVisibility() {
                sections.forEach(section => {
                    const visibleCards = section.querySelectorAll('.document-card:not([style*="display: none"])');
                    section.style.display = visibleCards.length > 0 ? 'block' : 'none';
                });
            }

            function filterDocuments() {
                const searchTerm = searchInput.value.toLowerCase();
                const activeFilter = document.querySelector('.filter-btn.active').getAttribute('data-filter');

                documentCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const category = card.getAttribute('data-category');
                    const matchesSearch = title.includes(searchTerm);
                    const matchesFilter = activeFilter === 'all' || category === activeFilter;

                    card.style.display = (matchesSearch && matchesFilter) ? 'block' : 'none';
                });

                updateSectionVisibility();
            }

            // Search input
            searchInput.addEventListener('input', filterDocuments);

            // Filter buttons
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    filterDocuments();
                });
            });
        });
    </script>
@endsection


