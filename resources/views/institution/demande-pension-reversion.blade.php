@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <!-- Main Content -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <x-breadcrumb :items="[
                ['label' => 'Page demandes pension', 'url' => route('demandes.demande-pension.index')],
                ['label' => 'Demande de pension de reversion']
            ]" />
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-12 mb-12 gap-8">
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

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Document Upload Form -->
            <form class="space-y-6" method="POST" action="{{route('demandes.demande-pension-reversion.store')}}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                Titre personnalisé <span class="text-gray-400 font-normal">(optionnel)</span>
                            </label>
                            <input
                                id="title"
                                type="text"
                                name="title"
                                value="{{ old('title', $demande?->title ?? '') }}"
                                placeholder="ex : Demande de réversion — 2026"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                        </div>
                        @if($demande)
                            <input
                                type="hidden"
                                name="demande_id"
                                value="{{ $demande->id }}"
                            >
                        @endif
                </div>

                <!-- Deceased Information -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                        <legend class="text-lg font-semibold mb-4">Informations du défunt</legend>
                        <div class="space-y-4 pb-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Nom complet du défunt
                                    </label>
                                    <input
                                        type="text"
                                        name="nom_complet_defunt"
                                        value="{{ old('nom_complet_defunt', data_get($demande, 'data.nom_complet_defunt', '')) }}"
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
                                        value="{{ old('numero_pension', data_get($demande, 'data.numero_pension', '')) }}"
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

                            <table class="w-full text-sm text-left text-body">
                                    <thead class="bg-gray-100 border-b border-t border-default-medium">
                                        <tr>
                                            <th class="px-6 py-3 text-blue-900 font-bold">Nom du document</th>
                                            <th class="px-6 py-3 text-blue-900 font-bold">Piece jointes</th>
                                            <th class="px-6 py-3 text-blue-900 font-bold">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                <td class="px-6 py-4 text-blue-900 font-bold">
                                                        <!-- Certificat de Carrière -->
                                                        <div class="document-upload rounded-lg p-3">
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                Certificat de Carrière (Original)
                                                                <span class="text-red-500">*</span>
                                                            </label>                                            
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($demande)
                                                        @foreach ($demande->documents as $document)
                                                            @if($document->type === 'certificat_carriere')
                                                                <p class="mb-2 text-sm text-gray-600">
                                                                    <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                        {{ $document->original_name }}
                                                                    </a>
                                                                    
                                                                        <button type="button"
                                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                                onclick="deleteDocument({{ $document->id }})">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                </p>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                            <x-file-input name="certificat_carriere" accept="application/pdf" />
                                                            <div class="preview-container mt-2"></div>
                                                </td>
                                            </tr>
                                            <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                <td class="px-6 py-4 text-blue-900 font-bold">
                                                        <!-- Certificat de Carrière -->
                                                        <div class="document-upload rounded-lg p-3">
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                Acte de Décès (Copie + Original)
                                                                <span class="text-red-500">*</span>
                                                            </label>                                            
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($demande)
                                                        @foreach ($demande->documents as $document)
                                                            @if($document->type === 'acte_deces')
                                                                <p class="mb-2 text-sm text-gray-600">
                                                                    <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                        {{ $document->original_name }}
                                                                    </a>
                                                                    
                                                                        <button type="button"
                                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                                onclick="deleteDocument({{ $document->id }})">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                </p>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                            <x-file-input name="acte_deces[]" accept="application/pdf" multiple />
                                                            <div class="preview-container mt-2"></div>
                                                </td>
                                            </tr>
                                            <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                <td class="px-6 py-4 text-blue-900 font-bold">
                                                        <!-- Certificat de Carrière -->
                                                        <div class="document-upload rounded-lg p-3">
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                Certificat de Non-Dissolution du Mariage (Original)
                                                                <span class="text-red-500">*</span>
                                                            </label>                                            
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($demande)
                                                        @foreach ($demande->documents as $document)
                                                            @if($document->type === 'certificat_non_dissolution')
                                                                <p class="mb-2 text-sm text-gray-600">
                                                                    <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                        {{ $document->original_name }}
                                                                    </a>
                                                                    
                                                                        <button type="button"
                                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                                onclick="deleteDocument({{ $document->id }})">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                </p>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                            <x-file-input name="certificat_non_dissolution" accept="application/pdf" />
                                                            <div class="preview-container mt-2"></div>
                                                </td>
                                            </tr>
                                            <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                <td class="px-6 py-4 text-blue-900 font-bold">
                                                        <!-- Certificat de Carrière -->
                                                        <div class="document-upload rounded-lg p-3">
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                Carte de Pension du Défunt
                                                                <span class="text-red-500">*</span>
                                                            </label>                                            
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($demande)
                                                        @foreach ($demande->documents as $document)
                                                            @if($document->type === 'carte_pension')
                                                                <p class="mb-2 text-sm text-gray-600">
                                                                    <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                        {{ $document->original_name }}
                                                                    </a>
                                                                    
                                                                        <button type="button"
                                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                                onclick="deleteDocument({{ $document->id }})">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                </p>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                            <x-file-input name="carte_pension" accept="application/pdf" />
                                                            <div class="preview-container mt-2"></div>
                                                </td>
                                            </tr>
                                            <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                <td class="px-6 py-4 text-blue-900 font-bold">
                                                        <!-- Certificat de Carrière -->
                                                        <div class="document-upload rounded-lg p-3">
                                                            <label class="block text-sm font-medium text-gray-700">
                                                                Souche de Chèque ou Preuve de Paiement
                                                                <span class="text-red-500">*</span>
                                                            </label>                                            
                                                        </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    @if($demande)
                                                        @foreach ($demande->documents as $document)
                                                            @if($document->type === 'souche_cheque')
                                                                <p class="mb-2 text-sm text-gray-600">
                                                                    <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                        {{ $document->original_name }}
                                                                    </a>
                                                                    
                                                                        <button type="button"
                                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                                onclick="deleteDocument({{ $document->id }})">
                                                                            <i class="fas fa-trash-alt"></i>
                                                                        </button>
                                                                </p>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4">
                                                            <x-file-input name="souche_cheque" accept="application/pdf" />
                                                            <div class="preview-container mt-2"></div>
                                                </td>
                                            </tr>
                                    </tbody>
                            </table>
                        </div>
                </fieldset>

                <!-- Beneficiary Information -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                        <legend class="text-lg font-semibold mb-4">Informations du bénéficiaire</legend>
                        <div class="space-y-4 mt-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Nom bénéficiaire --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">
                                        Nom du bénéficiaire
                                    </label>

                                    <input
                                        type="text"
                                        name="nom_beneficiaire"
                                        value="{{ old('nom_beneficiaire', data_get($demande, 'data.nom_beneficiaire', '')) }}"
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
                                        <option value="conjoint" @selected(old('relation_defunt', data_get($demande, 'data.relation_defunt')) == 'conjoint')>
                                            Conjoint(e)
                                        </option>
                                        <option value="enfant"  @selected(old('relation_defunt', data_get($demande, 'data.relation_defunt')) == 'enfant')>
                                            Enfant
                                        </option>
                                        <option value="tuteur"  @selected(old('relation_defunt', data_get($demande, 'data.relation_defunt')) == 'tuteur')>
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

                                <table class="w-full text-sm text-left text-body">
                                        <thead class="bg-gray-100 border-b border-t border-default-medium">
                                            <tr>
                                                <th class="px-6 py-3 text-blue-900 font-bold">Nom du document</th>
                                                <th class="px-6 py-3 text-blue-900 font-bold">Piece jointes</th>
                                                <th class="px-6 py-3 text-blue-900 font-bold">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Extrait récent de l'Acte de Mariage
                                                                    <span class="text-red-500">*</span>
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'extrait_acte_mariage')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="extrait_acte_mariage" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Extrait récent de l'Acte de Naissance
                                                                    <span class="text-red-500">*</span>
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'extrait_acte_naissance')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="extrait_acte_naissance" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Copie du Matricule Fiscal
                                                                    <span class="text-red-500">*</span>
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'matricule_fiscal')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="matricule_fiscal" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Copie de la Carte Électorale
                                                                    <span class="text-red-500">*</span>
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'carte_electorale')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="carte_electorale" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Deux (2) Photos d'Identité Récentes
                                                                    <span class="text-red-500">*</span>
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'photos_identites')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="photos_identites[]" accept="image/jpeg, image/png, image/jpg" multiple />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                        </tbody>
                                </table>
                            </div>
                        </div>
                </fieldset>

                <!-- Additional Documents -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                        <legend class="text-lg font-semibold mb-4">Documents supplémentaires</legend>
                        <div class="space-y-4 mt-6">
                                    <table class="w-full text-sm text-left text-body">
                                        <thead class="bg-gray-100 border-b border-t border-default-medium">
                                            <tr>
                                                <th class="px-6 py-3 text-blue-900 font-bold">Nom du document</th>
                                                <th class="px-6 py-3 text-blue-900 font-bold">Piece jointes</th>
                                                <th class="px-6 py-3 text-blue-900 font-bold">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Procès-Verbal de Tutelle (le cas échéant)
                                                                    
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'pv_tutelle')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="pv_tutelle" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                Certificat Médical
                                                                <span class="text-xs text-gray-500">
                                                                    (pour handicapés physiques ou interdits)
                                                                </span>
                                                                    
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'certificat_medical')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="certificat_medical" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Copie du Moniteur (pour les Grands Commis)
                                                                    
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'copie_moniteur')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="copie_moniteur" accept="application/pdf" />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                                            <!-- Certificat de Carrière -->
                                                            <div class="document-upload rounded-lg p-3">
                                                                <label class="block text-sm font-medium text-gray-700">
                                                                    Attestation Scolaire / Universitaire (pour les étudiants majeurs)
                                                                   
                                                                </label>
                                                        
                                                            </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        @if($demande)
                                                            @foreach ($demande->documents as $document)
                                                                @if($document->type === 'attestations_scolaires')
                                                                    <p class="mb-2 text-sm text-gray-600">
                                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                                            {{ $document->original_name }}
                                                                        </a>
                                                                        
                                                                            <button type="button"
                                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                    </p>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4">
                                                                <x-file-input name="attestations_scolaires[]" accept="application/pdf" multiple />
                                                                <div class="preview-container mt-2"></div>
                                                    </td>
                                                </tr>
                                    </tbody>
                                </table>
                        </div>
                </fieldset>

                <!-- Submit Section -->
                <x-demande-actions :demande="$demande" />
            </form>
            <form id="delete-document-form" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection

<script>
        function deleteDocument(documentId) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            const form = document.getElementById('delete-document-form');
            form.action = `/demandedocument/${documentId}`;
            form.submit();
        }
</script>
