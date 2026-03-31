@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <!-- Contenu Principal -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <x-breadcrumb :items="[
                ['label' => 'Page demandes pension', 'url' => route('demandes.demande-pension.index')],
                ['label' => 'Demande de pension']
            ]" />
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mt-12 mb-12 gap-8">
                <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                    <div class="px-4">
                        <h1 class="text-lg md:text-xl font-bold mb-1">Demande de Pension</h1>
                        <p class="text-sm text-gray-600">Direction de la Pension Civile (DPC)</p>
                    </div>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type === 'career_certificates')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'monitor_copy')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type === 'marriage_certificates')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'birth_certificates')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'divorce_certificate')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'tax_id_numbers')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'photos')
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
                                        <x-file-input name="photos[]" accept="image/jpeg, image/png, image/jpg" multiple
                                            hint="Format accepté: JPEG, PNG (Max 1MB par photo)" />
                                        <div class="preview-container mt-2"></div>
                                    </td>
                                </tr>
                                <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">
                                        <!-- Certificat Médical -->
                                        <div class="document-upload  rounded-lg p-3">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Certificat Médical (pour cause d'incapacité) (pdf)
                                            </label>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($demande)
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'medical_certificate')
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
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'check_stub')
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
@endsection


<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize files storage for all file inputs
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input._files = [];
        });

        function createPreviewElement(file, index, input) {
            const filePreview = document.createElement('div');
            filePreview.className = 'ml-5 flex items-center justify-between p-2 bg-gray-50 rounded mt-2 group';

            // Left section with icon and file info
            const leftSection = document.createElement('div');
            leftSection.className = 'flex items-center space-x-3';

            // File type icon
            const icon = document.createElement('div');
            if (file.type.startsWith('image/')) {
                icon.innerHTML = `
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            `;
                icon.innerHTML = '';
            } else {
                icon.innerHTML = `
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            `;
            }
            leftSection.appendChild(icon);

            // File info
            const fileInfo = document.createElement('div');
            fileInfo.innerHTML = `
            <div class="text-sm text-gray-600">
                <div class="font-medium">${file.name}</div>
                <div class="text-xs text-gray-500">${formatFileSize(file.size)}</div>
            </div>
        `;
            // leftSection.appendChild(fileInfo);

            // Preview link for PDFs
            if (file.type === 'application/pdf') {
                const pdfUrl = URL.createObjectURL(file);
                const viewLink = document.createElement('a');
                viewLink.href = pdfUrl;
                viewLink.target = '_blank';
                viewLink.className = 'text-blue-600 hover:text-blue-800 text-sm ml-3';
                viewLink.textContent = 'Aperçu';
                viewLink.addEventListener('click', () => {
                    setTimeout(() => URL.revokeObjectURL(pdfUrl), 1000);
                });
                leftSection.appendChild(fileInfo);
                leftSection.appendChild(viewLink);
            }

            // Image preview
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const imgPreview = document.createElement('img');
                    imgPreview.src = e.target.result;
                    imgPreview.className = 'h-16 w-16 object-cover rounded ml-3';
                    leftSection.appendChild(imgPreview);
                    leftSection.appendChild(fileInfo);
                };
                reader.readAsDataURL(file);
            }

            filePreview.appendChild(leftSection);

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className =
                'text-red-500 hover:text-red-700 opacity-0 group-hover:opacity-100 transition-opacity';
            removeBtn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        `;
            removeBtn.addEventListener('click', () => {
                // Remove file from storage
                input._files.splice(index, 1);
                updateFileInput(input);
                renderPreviews(input);
            });

            filePreview.appendChild(removeBtn);
            return filePreview;
        }

        function updateFileInput(input) {
            const dt = new DataTransfer();
            input._files.forEach(file => dt.items.add(file));
            input.files = dt.files;
        }

        function renderPreviews(input) {
            const container = input.closest('.document-upload').querySelector('.preview-container');
            container.innerHTML = '';
            input._files.forEach((file, index) => {
                container.appendChild(createPreviewElement(file, index, input));
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Handle file selection
        document.querySelectorAll('input[type="file"]').forEach(input => {
            input.addEventListener('change', function(e) {
                const files = Array.from(this.files);

                if (this.multiple) {
                    this._files.push(...files);
                } else {
                    this._files = files;
                }

                updateFileInput(this);
                renderPreviews(this);
            });
        });
    });
</script>
<script>
function deleteDocument(documentId) {
    if (!confirm('Are you sure you want to delete this file?')) return;

    const form = document.getElementById('delete-document-form');
    form.action = `/demandedocument/${documentId}`;
    form.submit();
}
</script>

