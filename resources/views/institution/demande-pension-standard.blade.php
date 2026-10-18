@extends('layouts.main')

@section('content')
    <div class="site-gutter p-6">
        <!-- Contenu Principal -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <x-breadcrumb :items="[
                ['label' => 'Page demandes pension', 'url' => route('demandes.demande-pension.index')],
                ['label' => 'Demande de pension']
            ]" />
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-12 mb-12 gap-8">
                <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                    <div class="site-gutter">
                        <h1 class="text-lg md:text-xl font-bold mb-1">Demande de Pension</h1>
                        <p class="text-sm text-gray-600">Direction de la Pension Civile (DPC)</p>
                    </div>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded bg-red-100 p-4 text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Formulaire de Téléchargement -->
            <form class="space-y-6" method="POST" action="{{ route('demandes.demande-pension-standard.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">
                            Titre personnalisé <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        <input
                            id="title"
                            type="text"
                            name="title"
                            value="{{ old('title', $demande?->title ?? '') }}"
                            placeholder="ex : Demande de pension standard — 2026"
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
                                                    Original du Certificat de Carrière (autant de certificats que d'employeurs) (pdf)
                                                    <span class="text-red-500">*</span>
                                                </label>
                                        
                                            </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('career_certificates') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                        
                                                            <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                                <x-file-input name="career_certificates[]" accept="application/pdf" multiple />
                                                <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Copie du Moniteur (pour les Grands Commis) (pdf)
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('monitor_copy') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                             
                                                            <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                            <x-file-input name="monitor_copy" accept="application/pdf" />
                                            <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Acte de Mariage -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Extrait récent de l'Acte de Mariage (Copie + Original pour les femmes mariées) (pdf)
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('marriage_certificates') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                           <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>

                                                       
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="marriage_certificates[]" accept="application/pdf" multiple
                                            hint="Si vous fournissez un acte de mariage, veuillez joindre obligatoirement la copie et l’original." />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Acte de Naissance -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Extrait récent de l'Acte de Naissance (Copie + Original) (pdf)
                                                <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('birth_certificates') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                        
                                                           <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>

                                                      
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="birth_certificates[]" accept="application/pdf" multiple />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Acte de divorce -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Acte de divorce (le cas échéant) (pdf)
                                            </label>

                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('divorce_certificate') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                       
                                                          <button type="button"
                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                onclick="deleteDocument({{ $document->id }})">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>

                                                    
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="divorce_certificate" accept="application/pdf" />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">                                        
                                        <!-- Matricule fiscal + CIN -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Copie du Matricule fiscal accompagné de la Carte d’Identification Nationale (CIN) (pdf, jpg, png)
                                                <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('tax_id_numbers') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                           
                                                            <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>

                                                       
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="tax_id_numbers[]" accept="application/pdf" multiple />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Photos d'identité -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Photos d'identité récentes (2 exemplaires) (jpeg, png, jpg)
                                                <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('photos') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                                                    
                                                            <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>

                                                       
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="photos[]" accept="image/jpeg, image/png, image/jpg" multiple
                                            hint="Format accepté: JPEG, PNG (Max 1MB par photo)" />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Certificat Médical -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Certificat Médical (pour cause d'incapacité) (pdf)
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->getMedia('medical_certificate') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                            <button type="button"
                                                                    class="text-red-500 hover:text-red-700 p-2"
                                                                    onclick="deleteDocument({{ $document->id }})">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="medical_certificate" accept="application/pdf" />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Souche de chèque -->
                                        <div class="document-upload rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Souche de chèque ou preuve de paiement (pdf, jpg, png)
                                                <span class="text-red-500">*</span>
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">                                                                                
                                        @if($demande)
                                            @foreach ($demande->getMedia('check_stub') as $document)
                                                    <p class="mb-2 text-sm text-gray-600">
                                                        <a href="{{ $demande->urlForMedia($document) }}" target="_blank" class="text-blue-600 underline">
                                                            {{ $document->file_name }}
                                                        </a>
                                                                
                                                        <button type="button"
                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                onclick="deleteDocument({{ $document->id }})">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </p>
                                                @endforeach
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <x-file-input name="check_stub" accept="application/pdf" />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                    </tbody>
                </table>

                <!-- Soumission -->
                <x-demande-actions :demande="$demande" />
            </form>
            <form id="delete-document-form" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
    <x-push-vite :js="['resources/js/pages/institution-pension-standard.js']" />
@endsection
