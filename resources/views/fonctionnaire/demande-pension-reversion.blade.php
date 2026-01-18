@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6 ">
        <!-- Main Content -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
                <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}"
                        alt="Logo de la Direction de la Pension Civile"
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0" loading="lazy">

                    <div class="px-4">
                        <h1 class="text-lg md:text-xl font-bold mb-1">Demande de Pension par Réversion</h1>
                        <p class="text-sm text-gray-600">Direction de la Pension Civile (DPC)</p>
                    </div>

                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt=""
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 hidden md:block" role="presentation"
                        loading="lazy">
                </div>
            </div>
            
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Document Upload Form -->
            <form class="space-y-6" method="POST" action="{{route('demandes.demande-pension-reversion.store')}}" enctype="multipart/form-data">
                @csrf

                <!-- Deceased Information -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-800">Informations du défunt</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Nom complet du défunt
                            </label>
                            <input
                                type="text"
                                name="nom_complet_defunt"
                                value="{{ old('nom_complet_defunt') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                    focus:border-blue-500 focus:ring-blue-500
                                    @error('nom_complet_defunt') border-red-500 @enderror"
                            >

                            @error('nom_complet_defunt')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Numéro de pension
                            </label>
                            <input
                                type="text"
                                name="numero_pension"
                                value="{{ old('numero_pension') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                    focus:border-blue-500 focus:ring-blue-500
                                    @error('numero_pension') border-red-500 @enderror"
                            >

                            @error('numero_pension')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Required Documents -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        Documents requis
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Certificat de carrière --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Certificat de Carrière (Original)
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="file"
                                name="certificat_carriere"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('certificat_carriere') border border-red-500 @enderror"
                                
                            >

                            @error('certificat_carriere')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Acte de décès --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Acte de Décès (Copie + Original)
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="file"
                                multiple
                                name="acte_deces[]"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('acte_deces') border border-red-500 @enderror"
                                
                            >

                            @error('acte_deces')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Certificat de non-dissolution --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Certificat de Non-Dissolution du Mariage (Original)
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="file"
                                name="certificat_non_dissolution"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('certificat_non_dissolution') border border-red-500 @enderror"
                                
                            >

                            @error('certificat_non_dissolution')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Carte de pension --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Carte de Pension du Défunt
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="file"
                                name="carte_pension"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('carte_pension') border border-red-500 @enderror"
                                
                            >

                            @error('carte_pension')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Souche de chèque --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Souche de Chèque ou Preuve de Paiement
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                type="file"
                                name="souche_cheque"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('souche_cheque') border border-red-500 @enderror"
                                
                            >

                            @error('souche_cheque')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Beneficiary Information -->
                <div class="space-y-4 mt-6">
                    <h3 class="text-md font-semibold text-gray-800">
                        Informations du bénéficiaire
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nom bénéficiaire --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Nom du bénéficiaire
                            </label>

                            <input
                                type="text"
                                name="nom_beneficiaire"
                                value="{{ old('nom_beneficiaire') }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                    focus:border-blue-500 focus:ring-blue-500
                                    @error('nom_beneficiaire') border-red-500 @enderror"
                                
                            >

                            @error('nom_beneficiaire')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Relation --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Relation avec le défunt
                            </label>

                            <select
                                name="relation_defunt"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm
                                    focus:border-blue-500 focus:ring-blue-500
                                    @error('relation_defunt') border-red-500 @enderror"
                                
                            >
                                <option value="">-- Sélectionner --</option>
                                <option value="conjoint" {{ old('relation_defunt') === 'conjoint' ? 'selected' : '' }}>
                                    Conjoint(e)
                                </option>
                                <option value="enfant" {{ old('relation_defunt') === 'enfant' ? 'selected' : '' }}>
                                    Enfant
                                </option>
                                <option value="tuteur" {{ old('relation_defunt') === 'tuteur' ? 'selected' : '' }}>
                                    Tuteur légal
                                </option>
                            </select>

                            @error('relation_defunt')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Documents bénéficiaire --}}
                    <div class="space-y-4 mt-4">
                        <h4 class="text-sm font-semibold text-gray-800">
                            Documents du bénéficiaire
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Acte de mariage --}}
                            <div class="document-upload border rounded-lg p-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Extrait récent de l'Acte de Mariage
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="extrait_acte_mariage"
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                        file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        @error('extrait_acte_mariage') border border-red-500 @enderror"
                                    
                                >

                                @error('extrait_acte_mariage')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Acte de naissance --}}
                            <div class="document-upload border rounded-lg p-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Extrait récent de l'Acte de Naissance
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="extrait_acte_naissance"
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                        file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        @error('extrait_acte_naissance') border border-red-500 @enderror"
                                    
                                >

                                @error('extrait_acte_naissance')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Matricule fiscal --}}
                            <div class="document-upload border rounded-lg p-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Copie du Matricule Fiscal
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="matricule_fiscal"
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                        file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        @error('matricule_fiscal') border border-red-500 @enderror"
                                    
                                >

                                @error('matricule_fiscal')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Carte électorale --}}
                            <div class="document-upload border rounded-lg p-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Copie de la Carte Électorale
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="carte_electorale"
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                        file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        @error('carte_electorale') border border-red-500 @enderror"
                                    
                                >

                                @error('carte_electorale')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Photos --}}
                            <div class="document-upload border rounded-lg p-3">
                                <label class="block text-sm font-medium text-gray-700">
                                    Deux (2) Photos d'Identité Récentes
                                    <span class="text-red-500">*</span>
                                </label>

                                <input
                                    type="file"
                                    name="photos_identite[]"
                                    multiple
                                    class="mt-1 block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                        file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                        hover:file:bg-blue-100
                                        @error('photos_identite') border border-red-500 @enderror"
                                    
                                >

                                <p class="mt-1 text-xs text-gray-500">
                                    Veuillez télécharger exactement deux photos
                                </p>

                                @error('photos_identite')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Documents -->
                <div class="space-y-4 mt-6">
                    <h3 class="text-md font-semibold text-gray-800">
                        Documents supplémentaires
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- PV de tutelle --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Procès-Verbal de Tutelle
                                <span class="text-xs text-gray-500">(le cas échéant)</span>
                            </label>

                            <input
                                type="file"
                                name="pv_tutelle"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('pv_tutelle') border border-red-500 @enderror"
                            >

                            @error('pv_tutelle')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Certificat médical --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Certificat Médical
                                <span class="text-xs text-gray-500">
                                    (pour handicapés physiques ou interdits)
                                </span>
                            </label>

                            <input
                                type="file"
                                name="certificat_medical"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('certificat_medical') border border-red-500 @enderror"
                            >

                            @error('certificat_medical')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Copie du moniteur --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Copie du Moniteur
                                <span class="text-xs text-gray-500">(pour les Grands Commis)</span>
                            </label>

                            <input
                                type="file"
                                name="copie_moniteur"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('copie_moniteur') border border-red-500 @enderror"
                            >

                            @error('copie_moniteur')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Attestation scolaire --}}
                        <div class="document-upload border rounded-lg p-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Attestation Scolaire / Universitaire
                                <span class="text-xs text-gray-500">
                                    (pour les étudiants majeurs)
                                </span>
                            </label>

                            <input
                                type="file"
                                multiple
                                name="attestation_scolaires[]"
                                class="mt-1 block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0
                                    file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    @error('attestation_scolaire') border border-red-500 @enderror"
                            >

                            @error('attestation_scolaire')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Consentement --}}
                <div class="mt-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="consentement"
                                name="consentement"
                                type="checkbox"
                                value="1"
                                {{ old('consentement') ? 'checked' : '' }}
                                class="h-4 w-4 rounded border-gray-300 text-blue-600
                                    focus:ring-blue-500
                                    @error('consentement') border-red-500 @enderror"
                                
                            >
                        </div>

                        <div class="ml-3 text-sm">
                            <label for="consentement" class="font-medium text-gray-700">
                                Je certifie que les informations fournies sont exactes
                            </label>
                            <p class="text-gray-500">
                                Je reconnais que toute fausse déclaration peut entraîner le rejet
                                de la demande ou des sanctions prévues par la loi.
                            </p>
                        </div>
                    </div>

                    @error('consentement')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submission -->
                <div class="mt-8">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        {{-- <i class="fas fa-paper-plane mr-2"></i> --}}
                        Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
