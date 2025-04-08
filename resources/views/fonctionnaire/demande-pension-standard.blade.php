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
                    <span class="text-gray-800 font-medium">Demande de pension standard</span>
                </li>
            </ol>
        </nav>

        <!-- Main Content -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Demande de Pension Standard</h1>

            <!-- Document Upload Form -->
            <form class="space-y-6" method="POST" action="/submit-standard-pension" enctype="multipart/form-data">
                @csrf

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input type="text" value="{{ auth()->user()->name }}" required readonly
                            class="bg-gray-100 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Matricule</label>
                        <input type="text" value="{{ auth()->user()->nif }}" required readonly
                            class="bg-gray-100 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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

                    <!-- Acte de Naissance -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Extrait récent de l'Acte de Naissance (Copie + Original)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="acte_naissance"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Matricule fiscal + CIN -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Copie du Matricule fiscal avec CIN
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="matricule_fiscal_cin"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Photos d'identité -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Photos d'identité récentes (2 exemplaires)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="photos" multiple
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Souche de chèque -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Souche de chèque ou preuve de paiement
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" required name="souche_cheque"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Additional Documents -->
                    <div class="space-y-4">
                        {{-- <h3 class="text-md font-semibold text-gray-800 mt-6">Documents supplémentaires (si applicable)</h3> --}}

                        <!-- Copie du Moniteur -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Copie du Moniteur (pour les Grands Commis)
                            </label>
                            <input type="file" name="copie_moniteur"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Acte de Mariage -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Extrait récent de l'Acte de Mariage (Copie + Original pour les femmes mariées)
                            </label>
                            <input type="file" name="acte_mariage"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Acte de divorce -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Acte de divorce (le cas échéant)
                            </label>
                            <input type="file" name="acte_divorce"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Certificat Médical -->
                        <div class="document-upload">
                            <label class="block text-sm font-medium text-gray-700">
                                Certificat Médical (pour cause d'incapacité)
                            </label>
                            <input type="file" name="certificat_medical"
                                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
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
