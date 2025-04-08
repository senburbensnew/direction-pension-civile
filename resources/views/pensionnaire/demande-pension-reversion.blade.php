@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6 bg-white">
        <!-- Breadcrumb -->
        <nav aria-label="Breadcrumb" class="mb-4">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li>
                    <a href="/" class="text-gray-800 hover:text-blue-600">Fonctionnaire</a>
                </li>
                <li class="flex items-center">
                    <span class="mx-2">›</span>
                </li>
                <li aria-current="page">
                    <span class="text-gray-800 font-medium">Demande de pension par réversion</span>
                </li>
            </ol>
        </nav>

        <!-- Main Content -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Demande de Pension par Réversion</h1>

            <!-- Document Upload Form -->
            <form class="space-y-6" method="POST" action="/submit-reversion-pension" enctype="multipart/form-data">
                @csrf

                <!-- Deceased Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-800">Informations du défunt</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom complet du défunt</label>
                            <input type="text" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Numéro de pension</label>
                            <input type="text" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Required Documents -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Documents requis</h2>

                    <!-- Certificat de Carrière -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Certificat de Carrière (Original)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="certificat_carriere"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Acte de Décès -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Acte de Décès (Copie + Original)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="acte_deces"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Certificat de Non-Dissolution -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Certificat de Non-Dissolution du Mariage (Original)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="certificat_non_dissolution"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Carte de Pension -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Carte de Pension du Défunt
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="carte_pension"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Souche de Chèque -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Souche de Chèque ou Preuve de Paiement
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="souche_cheque"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <!-- Beneficiary Information -->
                <div class="space-y-4 mt-6">
                    <h3 class="text-md font-semibold text-gray-800">Informations du bénéficiaire</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nom du bénéficiaire</label>
                            <input type="text" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Relation avec le défunt</label>
                            <select required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option>Conjoint(e)</option>
                                <option>Enfant</option>
                                <option>Tuteur légal</option>
                            </select>
                        </div>
                    </div>

                    <!-- Beneficiary Documents -->
                    <div class="space-y-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-800">Documents du bénéficiaire</h4>

                        <!-- Extrait Acte de Mariage -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Extrait récent de l'Acte de Mariage (Copie + Original)
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="file" required name="extrait_acte_mariage"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Extrait Acte de Naissance -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Extrait récent de l'Acte de Naissance (Copie + Original)
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="file" required name="extrait_acte_naissance"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Matricule Fiscal -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Copie du Matricule Fiscal
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="file" required name="matricule_fiscal"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Carte Électorale -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Copie de la Carte Électorale
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="file" required name="carte_electorale"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Photos d'identité -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Deux (2) Photos d'Identité Récentes
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="file" required name="photos_identite" multiple
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="mt-1 text-xs text-gray-500">Veuillez télécharger les deux photos</p>
                        </div>
                    </div>
                </div>

                <!-- Additional Documents -->
                <div class="space-y-4 mt-6">
                    <h3 class="text-md font-semibold text-gray-800">Documents supplémentaires</h3>

                    <!-- PV Tutelle -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Procès-Verbal de Tutelle (le cas échéant)
                        </label>
                        <input type="file" name="pv_tutelle"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Certificat Médical -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Certificat Médical (pour handicapés physiques ou interdits)
                        </label>
                        <input type="file" name="certificat_medical"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Copie du Moniteur -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Copie du Moniteur (pour les Grands Commis)
                        </label>
                        <input type="file" name="copie_moniteur"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Attestation Scolaire -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Attestation Scolaire/Universitaire (pour les étudiants majeurs)
                        </label>
                        <input type="file" name="attestation_scolaire"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <!-- Submission -->
                <div class="mt-8">
                    <button type="submit"
                        class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Soumettre la demande
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
