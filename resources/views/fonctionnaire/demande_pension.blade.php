@extends('layouts.main')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }
    </style>

    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Fonctionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande de pension</span>
        </nav>

        <!-- Content Section -->
        <div id="content-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
                <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 order-1 md:order-none">

                    <div class="px-4 order-2 md:order-none">
                        <h2 class="text-lg md:text-xl font-bold mb-1">Direction de la Pension Civile (DPC)</h2>
                        <h3 class="text-base md:text-lg font-semibold mb-2">Pièces à fournir pour demande de pension</h3>
                        <p class="text-sm md:text-gray-600">Service des Requêtes</p>
                    </div>

                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 order-1 md:order-none hidden md:block">
                </div>
            </div>

            <!-- Main Content -->
            <div class="space-y-8">
                <!-- Pension Request Section -->
                <fieldset class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2">Demande de pension standard (pour tout
                        agent public ayant fourni à l’État un minimum de cinq (5) années (60 mois) de service âgé de 58 ans)
                    </legend>
                    <ul class="list-disc ml-6 space-y-2 text-gray-700">
                        <li>Original Certificat de Carrière</li>
                        <li>Copie du Moniteur (pour les Grands Commis)</li>
                        <li>Copie et Original de l’Extrait récent de l’Acte de Mariage (pour les femmes mariées)</li>
                        <li>Copie et Original de l’Extrait récent de l’Acte de Naissance</li>
                        <li>Acte de divorce (le cas échéant)</li>
                        <li>Copie du Matricule fiscal accompagné de la carte d’identification Nationale (CIN)</li>
                        <li>Deux (2) photos d’identité récentes</li>
                        <li>Certificat Médical (pour cause d’incapacité)</li>
                        <li>Souche de chèque ou preuve de paiement</li>
                    </ul>
                    <div class="mt-6">
                        <a href="/demande-pension-standard"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                            Faire la demande
                        </a>
                    </div>
                </fieldset>

                <!-- Pension Reversion Section -->
                <fieldset class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2">Demande de pension par réversion (si le
                        défunt est fonctionnaire et laisse un conjoint survivant et des enfants mineurs et ou majeurs
                        jusqu’à 24 ans)</legend>
                    <ul class="list-disc ml-6 space-y-2 text-gray-700">
                        <li>Original Certificat de Carrière</li>
                        <li>Copie et Original Acte de décès</li>
                        <li>Original Certificat de non-dissolution du Mariage</li>
                        <li>Copie et Original de l’Extrait récent de l’Acte de Mariage</li>
                        <li>Copie et Original de l’Extrait récent de l’Acte de Naissance</li>
                        <li>Copie du Matricule fiscal</li>
                        <li>Copie de la carte électorale</li>
                        <li>Deux (2) photos d’identité récentes</li>
                        <li>Procès-verbal désignant un tuteur pour un mineur ou un handicapé</li>
                        <li>Certificat Médical (pour les handicapés physiques ou les interdits)</li>
                        <li>Copie du Moniteur (pour les Grands Commis)</li>
                        <li>Carte de pension (du conjoint décédé)</li>
                        <li>Souche de chèque ou preuve de paiement (du pensionné décédé)</li>
                        <li>Attestation scolaire ou Universitaire (pour les étudiants majeurs)</li>
                    </ul>
                    <div class="mt-6">
                        <a href="/demande-pension-reversion"
                            class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition-colors duration-200">
                            Faire la demande
                        </a>
                    </div>
                </fieldset>



                <!-- Legal References -->
                <fieldset class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2">Références légales</legend>
                    <div class="space-y-6">
                        <div class="flex items-start space-x-3">
                            <!-- Icone de loi -->
                            <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    d="M12 2a10 10 0 00-7.07 17.07l1.41-1.41A8 8 0 1112 20a8.09 8.09 0 01-5.66-2.34l-1.41 1.41A10 10 0 1012 2zm-.5 11h-2v-2h2v2zm0-4h-2V7h2v2z" />
                            </svg>
                            <div class="text-gray-700">
                                <strong class="block text-gray-800">Article 4</strong>
                                Est éligible à la pension de retraite, tout agent public âgé de 58 ans au moins ayant fourni
                                à
                                l’État un minimum de 5 ans (60 mois) de service.
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <!-- Icone de loi -->
                            <svg class="w-6 h-6 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path
                                    d="M12 2a10 10 0 00-7.07 17.07l1.41-1.41A8 8 0 1112 20a8.09 8.09 0 01-5.66-2.34l-1.41 1.41A10 10 0 1012 2zm-.5 11h-2v-2h2v2zm0-4h-2V7h2v2z" />
                            </svg>
                            <div class="text-gray-700">
                                <strong class="block text-gray-800">Article 7</strong>
                                Toute demande de pension doit être adressée au Ministère de l’Economie et des Finances.
                            </div>
                        </div>
                    </div>
                </fieldset>


                <!-- Contact Information -->
                <fieldset class="bg-white shadow-md rounded-xl p-6 border border-gray-200 mb-8">
                    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2">Coordonnées</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="flex items-center space-x-3">
                            <!-- Icône de localisation -->
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path
                                    d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z" />
                            </svg>
                            <p class="text-gray-700">
                                104, Rue Oswald Durand,<br>
                                Port-au-Prince, Haïti, W.I.
                            </p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <!-- Icône de téléphone -->
                            <svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
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
                </fieldset>

            </div>
        </div>
    </div>
@endsection
