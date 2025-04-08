@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6 bg-white">
        <!-- Fil d'Ariane -->
        <nav aria-label="Fil d'Ariane" class="mb-4">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li>
                    <a href="/" class="text-gray-800 hover:text-blue-600">Fonctionnaire</a>
                </li>
                <li class="flex items-center">
                    <span class="mx-2">›</span>
                </li>
                <li aria-current="page">
                    <span class="text-gray-800 font-medium">Demande de pension</span>
                </li>
            </ol>
        </nav>

        <!-- Contenu Principal -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Demande de Pension</h1>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    <h2 class="font-bold mb-2">Veuillez corriger les erreurs suivantes :</h2>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li class="p-1">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulaire de Téléchargement -->
            <form class="space-y-6" method="POST" action="{{ route('fonctionnaire.process-pension-standard') }}"
                enctype="multipart/form-data">
                @csrf

                <!-- Informations Personnelles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" readonly
                            class="bg-gray-100 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Matricule</label>
                        <input type="text" name="nif" value="{{ auth()->user()->nif }}" readonly
                            class="bg-gray-100 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Documents Requis -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Documents requis :</h2>

                    <!-- Certificat de Carrière -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Original du Certificat de Carrière (autant de certificats que d'employeurs) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="certificat_carriere" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('certificat_carriere')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Copie du Moniteur -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Copie du Moniteur (pour les Grands Commis) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="copie_moniteur" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('copie_moniteur')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acte de Mariage -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Extrait récent de l'Acte de Mariage (Copie + Original pour les femmes mariées) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="acte_mariage" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('acte_mariage')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acte de Naissance -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Extrait récent de l'Acte de Naissance (Copie + Original) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="acte_naissance" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('acte_naissance')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acte de divorce -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Acte de divorce (le cas échéant) (pdf)
                        </label>
                        <input type="file" name="acte_divorce" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('acte_divorce')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Matricule fiscal + CIN -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Copie du Matricule fiscal avec CIN (pdf, jpg, png)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="matricule_fiscal_cin" accept=".pdf,.jpg,.png"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('matricule_fiscal_cin')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Photos d'identité -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Photos d'identité récentes (2 exemplaires) (jpeg, png, jpg)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="photos[]" multiple accept="image/jpeg, image/png, image/jpg"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('photos')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('photos.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Format accepté: JPEG, PNG (Max 1MB par photo)</p>
                    </div>

                    <!-- Certificat Médical -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Certificat Médical (pour cause d'incapacité) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="certificat_medical" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('certificat_medical')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Souche de chèque -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Souche de chèque ou preuve de paiement (pdf, jpg, png)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="souche_cheque" accept=".pdf,.jpg,.png"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        @error('souche_cheque')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Soumission -->
                <div class="mt-8 text-right">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-2 py-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
