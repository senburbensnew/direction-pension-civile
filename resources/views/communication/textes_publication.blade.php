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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                    <button class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium" data-filter="other">
                        Autres documents
                    </button>
                </div>
            </div>
        </header>

        <!-- Legal Documents Section -->
        <section id="legal-documents" class="mb-12 fade-in">
            <div class="bg-white rounded-2xl card-shadow overflow-hidden">
                <!-- Section Header -->
                <div class="gradient-bg text-white p-6">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-lg p-3 mr-4">
                            <i class="fas fa-gavel text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Documents Légaux Officiels</h2>
                            <p class="text-blue-100 mt-1">Textes réglementaires et décrets en vigueur</p>
                        </div>
                    </div>
                </div>

                <!-- Documents Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- CARD 1 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="legal">
                            <span class="category-badge bg-purple-100 text-purple-800">Comptabilité</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-file-invoice text-purple-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Arrêté sur la comptabilité publique
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'Arrete_sur_la_Comptabilite_Pub.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'Arrete_sur_la_Comptabilite_Pub.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 2 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="legal">
                            <span class="category-badge bg-indigo-100 text-indigo-800">Organisation</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-sitemap text-indigo-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Décret portant organisation de l'administration centrale de l'État
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'Decret_portant_organisation_de_l_Administration_centrale_de_l_Etat.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'Decret_portant_organisation_de_l_Administration_centrale_de_l_Etat.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 3 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="legal">
                            <span class="category-badge bg-blue-100 text-blue-800">Déontologie</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-balance-scale text-blue-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Arrêté définissant la règle déontologique applicable aux agents de la fonction publique
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'Arrete_definissant_la_regle_deontologique_applicable_aux_agents_de_la_FP.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'Arrete_definissant_la_regle_deontologique_applicable_aux_agents_de_la_FP.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 4 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="legal">
                            <span class="category-badge bg-green-100 text-green-800">Ministère</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-landmark text-green-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Décret réorganisant le ministère de l'Économie et des Finances
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'Decret_reorganisant_le_MEF.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'Decret_reorganisant_le_MEF.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 5 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="legal">
                            <span class="category-badge bg-amber-100 text-amber-800">Pension</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-piggy-bank text-amber-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Décret du 09 octobre 2015 sur la pension civile de retraite
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'decret_su_09_octobre_2015_sur_la_Pension_Civile_de_retraite.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'decret_su_09_octobre_2015_sur_la_Pension_Civile_de_retraite.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 6 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="legal">
                            <span class="category-badge bg-amber-100 text-amber-800">Statut</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-user-tie text-amber-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Décret portant révision du Statut Général de la Fonction Publique
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'Décret_portant_révision_du_Statut_Général_de_la_Fonction_Publique.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'Décret_portant_révision_du_Statut_Général_de_la_Fonction_Publique.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section Divider -->
        <div class="section-divider"></div>

        <!-- Other Documents Section -->
        <section id="other-documents" class="mb-12 fade-in">
            <div class="bg-white rounded-2xl card-shadow overflow-hidden">
                <!-- Section Header -->
                <div class="gradient-bg text-white p-6">
                    <div class="flex items-center">
                        <div class="bg-white bg-opacity-20 rounded-lg p-3 mr-4">
                            <i class="fas fa-info-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">Guides et Informations</h2>
                            <p class="text-blue-100 mt-1">Documents d'information et conseils pratiques</p>
                        </div>
                    </div>
                </div>

                <!-- Documents Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- CARD 1 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="other">
                            <span class="category-badge bg-purple-100 text-purple-800">Conseils</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-lightbulb text-purple-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Conseils pour les Retraités et Futurs Retraités
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'Conseils_pour_les_Retraités_et_Futurs_Retraites.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'Conseils_pour_les_Retraités_et_Futurs_Retraites.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>

                        <!-- CARD 2 -->
                        <div class="document-card bg-white border border-gray-200 rounded-xl p-5 card-shadow" data-category="other">
                            <span class="category-badge bg-purple-100 text-purple-800">Droits</span>
                            <div class="flex items-start mb-4">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                                    <i class="fas fa-hand-holding-heart text-purple-600 text-lg"></i>
                                </div>
                                <h3 class="text-md font-semibold text-gray-800 leading-tight">
                                    Vos droits à la retraite
                                </h3>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('documents.view', 'VOS_DROITS_A_LA_RETRAITE.pdf') }}"
                                   target="_blank"
                                   class="flex-1 bg-blue-600 text-white py-2 px-3 rounded-lg hover:bg-blue-700 transition-colors text-sm text-center flex items-center justify-center">
                                    <i class="fas fa-eye mr-2"></i> Voir PDF
                                </a>
                                <a href="{{ route('documents.download', 'VOS_DROITS_A_LA_RETRAITE.pdf') }}"
                                   class="bg-gray-100 text-gray-700 py-2 px-3 rounded-lg hover:bg-gray-200 transition-colors text-sm flex items-center justify-center">
                                    <i class="fas fa-download mr-2"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Information Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-8 fade-in">
            <div class="flex items-start">
                <div class="bg-blue-100 rounded-lg p-3 mr-4 flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                </div>
                <div>
                    <h4 class="text-blue-800 font-semibold mb-2 text-lg">Information importante</h4>
                    <p class="text-blue-700">
                        Tous les documents sont au format PDF. Vous aurez besoin d'un lecteur PDF pour les consulter.
                        <a href="https://get.adobe.com/fr/reader/" target="_blank" class="underline hover:no-underline font-medium">
                            Télécharger Adobe Reader
                        </a>
                    </p>
                </div>
            </div>
        </div>
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


