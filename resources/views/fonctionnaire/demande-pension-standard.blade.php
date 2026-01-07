@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <!-- Fil d'Ariane -->
{{--         <nav aria-label="Fil d'Ariane" class="mb-4">
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
        </nav> --}}

        <!-- Contenu Principal -->
        <div class="bg-white shadow-md rounded-xl p-6 border border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Demande de Pension</h1>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

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
            <form class="space-y-6" method="POST" action="{{ route('demandes.demande-pension.store') }}"
                enctype="multipart/form-data">
                @csrf

                <!-- Informations Personnelles -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nom complet</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Matricule</label>
                        <input type="text" name="nif" value="{{ auth()->user()->nif }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                        <input type="file" name="career_certificate" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('career_certificate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Copie du Moniteur -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Copie du Moniteur (pour les Grands Commis) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="monitor_copy" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('monitor_copy')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acte de Mariage -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Extrait récent de l'Acte de Mariage (Copie + Original pour les femmes mariées) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="marriage_certificate" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('marriage_certificate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acte de Naissance -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Extrait récent de l'Acte de Naissance (Copie + Original) (pdf)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="birth_certificate" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('birth_certificate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Acte de divorce -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Acte de divorce (le cas échéant) (pdf)
                        </label>
                        <input type="file" name="divorce_certificate" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('divorce_certificate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Matricule fiscal + CIN -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Copie du Matricule fiscal avec CIN (pdf, jpg, png)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="tax_id_number" accept=".pdf,.jpg,.png"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('tax_id_number')
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
                        <div class="preview-container mt-2"></div>
                        @error('photos')
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
                        <input type="file" name="medical_certificate" accept="application/pdf"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('medical_certificate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Souche de chèque -->
                    <div class="document-upload">
                        <label class="block text-sm font-medium text-gray-700">
                            Souche de chèque ou preuve de paiement (pdf, jpg, png)
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="file" name="check_stub" accept=".pdf,.jpg,.png"
                            class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <div class="preview-container mt-2"></div>
                        @error('check_stub')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Soumission -->
                <div class="mt-8 text-right">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-3 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Soumettre
                    </button>
                </div>
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
