@extends('layouts.main')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }

        .gradient-bg {
            background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 0.5rem;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>

    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li>
                    <span class="text-gray-800">Fonctionnaire</span>
                </li>
                <li class="flex items-center">
                    <span class="mx-2">›</span>
                </li>
                <li aria-current="page">
                    <span class="text-gray-800 font-medium">Demande de pension</span>
                </li>
            </ol>
        </nav>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
                <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                        alt="Logo de la Direction de la Pension Civile"
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0" loading="lazy">

                    <div class="px-4">
                        <h1 class="text-lg md:text-xl font-bold mb-1">Direction de la Pension Civile (DPC)</h1>
                        <p class="text-base md:text-lg font-semibold mb-2">Pièces à fournir pour demande de pension</p>
                        <p class="text-sm text-gray-600">Service des Requêtes</p>
                    </div>

                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt=""
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 hidden md:block" role="presentation"
                        loading="lazy">
                </div>
            </div>

            <!-- Content Sections -->
            <div class="space-y-8">
                <!-- Reversion Pension Section -->
                <section aria-labelledby="reversion-pension-heading"
                    class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <h2 id="reversion-pension-heading" class="text-lg font-semibold text-gray-800 mb-4">Demande de pension
                        par réversion</h2>
                    <div class="gradient-bg">
                        <p class="text-sm">Si le défunt est fonctionnaire et laisse un conjoint survivant et des enfants
                            mineurs et ou majeurs jusqu'à 24 ans</p>
                    </div>
                    <div class="p-6">
                        <ul class="space-y-3 text-gray-700 list-none">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Original Certificat de Carrière</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Copie et Original Acte de décès</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Original Certificat de non-dissolution du Mariage</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Copie et Original de l'Extrait récent de l'Acte de Mariage</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Copie et Original de l'Extrait récent de l'Acte de Naissance</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Copie du Matricule fiscal</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Copie de la carte électorale</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Deux (2) photos d'identité récentes</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Procès-verbal désignant un tuteur pour un mineur ou un handicapé</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Certificat Médical (pour les handicapés physiques ou les interdits)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Copie du Moniteur (pour les Grands Commis)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Carte de pension (du conjoint décédé)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Souche de chèque ou preuve de paiement (du pensionné décédé)</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-2" aria-hidden="true"></i>
                                <span>Attestation scolaire ou Universitaire (pour les étudiants majeurs)</span>
                            </li>
                        </ul>
                        <div class="mt-6 flex flex-col sm:flex-row gap-4">
                            <a href="{{ route('pensionnaire.pension-reversion-form') }} " role="button"
                                class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fas fa-file-alt mr-2" aria-hidden="true"></i> Faire la demande
                            </a>
                            <a href="/downloads/pension-reversion-checklist.pdf"
                                class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                download="Liste_Pension_Reversion.pdf">
                                <i class="fas fa-download mr-2" aria-hidden="true"></i> Télécharger la liste
                            </a>
                        </div>
                    </div>
                </section>

                <!-- Legal References -->
                <section aria-labelledby="legal-references-heading"
                    class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <h2 id="legal-references-heading" class="text-lg font-semibold text-gray-800 mb-4">Références légales
                    </h2>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-3">
                            <i class="fas fa-gavel text-lg w-6 h-6 text-blue-500 flex-shrink-0" aria-hidden="true"></i>
                            <div class="text-gray-700">
                                <strong class="block text-gray-800">Article 7</strong>
                                Toute demande de pension doit être adressée au Ministère de l’Economie et des Finances.
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Contact Information -->
                <section aria-labelledby="contact-heading"
                    class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <h2 id="contact-heading" class="text-lg font-semibold text-gray-800 mb-4">Coordonnées</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                            </svg>
                            <address class="not-italic text-gray-700">
                                104, Rue Oswald Durand,<br>
                                Port-au-Prince, Haïti, W.I.
                            </address>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.11-.21 11.36 11.36 0 003.56.57 1 1 0 011 1v3.61a1 1 0 01-1 1A16 16 0 014 5a1 1 0 011-1h3.61a1 1 0 011 1 11.36 11.36 0 00.57 3.56 1 1 0 01-.21 1.11l-2.35 2.12z" />
                            </svg>
                            <div class="text-gray-700">
                                <p class="mb-1 font-medium">Téléphone :</p>
                                <p>2992-1006 / 2992-1007</p>
                                <p>2992-1008 / 2992-1009</p>
                                <p>2992-1057 / 2992-1058</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </div>
@endsection
