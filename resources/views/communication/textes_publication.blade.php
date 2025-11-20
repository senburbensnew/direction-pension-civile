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
        }

        .document-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>


    <!-- Legal Documents Section -->
    <div class="mb-8 bg-white p-6 shadow-md rounded-lg">
        <div class="text-center mb-6">
            <h1 class="text-lg font-bold mb-2">DOCUMENTS LÉGAUX</h1>
        </div>

        <!-- Documents Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

            <!-- CARD 1 -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                        Arrêté sur la comptabilité publique
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'Arrete_sur_la_Comptabilite_Pub.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'Arrete_sur_la_Comptabilite_Pub.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

            <!-- CARD 2 -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                        Décret portant organisation de l'administration centrale de l'État
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'Decret_portant_organisation_de_l_Administration_centrale_de_l_Etat.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'Decret_portant_organisation_de_l_Administration_centrale_de_l_Etat.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

            <!-- CARD 3 -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                        Arrêté définissant la règle déontologique applicable aux agents de la fonction publique
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'Arrete_definissant_la_regle_deontologique_applicable_aux_agents_de_la_FP.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'Arrete_definissant_la_regle_deontologique_applicable_aux_agents_de_la_FP.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

            <!-- CARD 4 (GREEN ICON) -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                       Décret réorganisant le ministère de l’Économie et des Finances
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'Decret_reorganisant_le_MEF.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'Decret_reorganisant_le_MEF.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

            <!-- CARD 5 (AMBER ICON) -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                        Décret du 09 octobre 2015 sur la pension civile de retraite
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'decret_su_09_octobre_2015_sur_la_Pension_Civile_de_retraite.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'decret_su_09_octobre_2015_sur_la_Pension_Civile_de_retraite.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                    Décret portant révision du Statut Général de la Fonction Publique
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'Décret_portant_révision_du_Statut_Général_de_la_Fonction_Publique.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'Décret_portant_révision_du_Statut_Général_de_la_Fonction_Publique.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

        </div>

        <!-- Information Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>

                <div>
                    <h4 class="text-blue-800 font-semibold mb-1">Information importante</h4>
                    <p class="text-blue-700 text-sm">
                        Tous les documents sont au format PDF. Vous aurez besoin d'un lecteur PDF pour les consulter.
                        <a href="https://get.adobe.com/fr/reader/" target="_blank" class="underline hover:no-underline">
                            Télécharger Adobe Reader
                        </a>
                    </p>
                </div>
            </div>
        </div>

    </div>

        <!-- Legal Documents Section -->
    <div class="mb-8 bg-white p-6 shadow-md rounded-lg">
        <div class="text-center mb-6">
            <h1 class="text-lg font-bold mb-2">AUTRES DOCUMENTS</h1>
        </div>

        <!-- Documents Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- CARD 1 -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                        Conseils pour les Retraités et Futurs Retraités
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'Conseils_pour_les_Retraités_et_Futurs_Retraites.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'Conseils_pour_les_Retraités_et_Futurs_Retraites.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>

                        <!-- CARD 1 -->
            <div class="document-card bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4 flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586l6 6V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-md font-semibold text-gray-800">
                        Vos droits à la retraite
                    </h3>
                </div>

                <div class="flex space-x-2">
                    <a href="{{ route('documents.view', 'VOS_DROITS_A_LA_RETRAITE.pdf') }}"
                       target="_blank"
                       class="flex-1 bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 text-sm text-center">
                        Voir PDF
                    </a>
                    <a href="{{ route('documents.download', 'VOS_DROITS_A_LA_RETRAITE.pdf') }}"
                       class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 text-sm">
                        Télécharger
                    </a>
                </div>
            </div>
        </div>

                <!-- Information Notice -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>

                <div>
                    <h4 class="text-blue-800 font-semibold mb-1">Information importante</h4>
                    <p class="text-blue-700 text-sm">
                        Tous les documents sont au format PDF. Vous aurez besoin d'un lecteur PDF pour les consulter.
                        <a href="https://get.adobe.com/fr/reader/" target="_blank" class="underline hover:no-underline">
                            Télécharger Adobe Reader
                        </a>
                    </p>
                </div>
            </div>
        </div>
@endsection
