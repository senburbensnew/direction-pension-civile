@extends('layouts.admin')

@section('content')
    <div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Documents</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Téléverser</span>
        </nav>

        <!-- Upload PDF Form -->
        <div class="bg-gray-100 p-6 rounded-lg shadow-md">
            <form id="upload-form" action="{{ route('admin.documents.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label for="pdf_name" class="block text-sm font-medium text-gray-700">Choisir le document</label>
                    <select id="pdf_name" name="pdf_name"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="administration-mp" selected>Administration - Manuel des procédures</option>
                        <option value="administration-mu">Administration - Manuel des utilisateurs</option>
                        <option value="catalogue-dbs">Catalogue - Demande de biens et de services</option>
                        <option value="catalogue-mp">Catalogue - Manuel des procédures</option>
                        <option value="catalogue-mu">Catalogue - Manuel des utilisateurs</option>
                        <option value="comptabilite-nb">Comptabilité - Nomenclatures budgétaires</option>
                        <option value="comptabilite-npj">Comptabilité - Nomenclature des pièces justificatives</option>
                        <option value="comptabilite-pceh">Comptabilité - PCEH détaillé</option>
                        <option value="comptes-payer-mp">Comptes à payer - Manuel des procédures</option>
                        <option value="comptes-payer-mu">Comptes à payer - Manuel des utilisateurs</option>
                        <option value="execution-budgetaire-mp">Exécution budgétaire - Manuel des procédures</option>
                        <option value="execution-budgetaire-mu">Exécution budgétaire - Manuel des utilisateurs</option>
                        <option value="materiel-informatif-pedagogique">Matériel informatif et pédagogique</option>
                        <option value="textes-legaux-apch">Textes légaux et réglementaires - Arrêté portant Plan comptable
                            haïtien</option>
                        <option value="textes-legaux-apnb">Textes légaux et réglementaires - Arrêté portant nomenclatures
                            budgétaires</option>
                        <option value="textes-legaux-lo">Textes légaux et réglementaires - Loi organique</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="pdf_file" class="block text-sm font-medium text-gray-700">Sélectionner un fichier
                        PDF</label>

                    <!-- Hidden File Input -->
                    <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="hidden"
                        onchange="validateSize(this)">

                    <!-- Horizontal Layout for Upload Button, File Name, and Submit Button -->
                    <div class="flex items-center justify-between mt-2 space-x-4">

                        <!-- Custom File Upload Button -->
                        <label for="pdf_file"
                            class="flex items-center gap-2 text-sm text-white bg-blue-600 px-4 py-2 rounded-md shadow-sm cursor-pointer hover:bg-blue-700">

                            <!-- Upload Icon -->
                            <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4-4m0 0l-4 4m4-4v12">
                                </path>
                            </svg>

                            <span>Choisir un fichier</span>
                        </label>

                        <!-- Display File Name -->
                        <p id="file-name" class="text-sm text-gray-600 flex-1 truncate">Aucun fichier sélectionné</p>

                        <!-- Submit Button -->
                        <button id="upload-button" type="submit" disabled
                            class="px-4 py-2 bg-gray-400 text-white font-medium text-sm rounded cursor-not-allowed">
                            Téléverser
                        </button>
                    </div>
                </div>


            </form>
        </div>

        @if ($errors->has('pdf_file'))
            <div class="alert alert-danger">
                {{ $errors->first('pdf_file') }}
            </div>
        @else
            <div class="alert alert-success">
                No errors detected. You can upload a file.
            </div>
        @endif

        <!-- Simplified error display -->
        @error('pdf_file')
            <div class="text-red-500 text-sm mt-2">
                {{ $message }}
            </div>
        @enderror
    </div>

    <!-- Flash Messages -->
    @if (session('success'))
        <div id="toast-success" class="fixed top-4 px-4 py-2 bg-green-500 text-white rounded shadow-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Loading Indicator -->
    <div id="loading-spinner"
        class="hidden fixed top-4 bg-blue-600 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 00-8 8z"></path>
        </svg>
        <span class="text-sm">Téléversement en cours...</span>
    </div>

    <!-- JavaScript -->
    <script>
        window.onload = function() {
            // Hide success message after 5 seconds
            setTimeout(function() {
                var successToast = document.getElementById('toast-success');
                if (successToast) {
                    successToast.style.display = 'none';
                }
            }, 5000);
        };

        document.getElementById('pdf_file').addEventListener('change', function() {
            var uploadButton = document.getElementById('upload-button');
            if (this.files.length > 0) {
                uploadButton.disabled = false;
                uploadButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
                uploadButton.classList.add('bg-green-600', 'hover:bg-green-700');
            } else {
                uploadButton.disabled = true;
                uploadButton.classList.remove('bg-green-600', 'hover:bg-green-700');
                uploadButton.classList.add('bg-gray-400', 'cursor-not-allowed');
            }
        });

        document.getElementById('upload-form').addEventListener('submit', function() {
            // Show the loading spinner
            document.getElementById('loading-spinner').classList.remove('hidden');

            // Disable the submit button
            var uploadButton = document.getElementById('upload-button');
            uploadButton.disabled = true;
            uploadButton.innerText = 'Téléchargement...';
        });

        document.getElementById('pdf_file').addEventListener('change', function() {
            var fileName = this.files.length > 0 ? this.files[0].name : 'Aucun fichier sélectionné';
            document.getElementById('file-name').textContent = fileName;
        });

        /* function validateSize(input) {
            if (input.files[0].size > 2048 * 1024) {
                alert('Le fichier est trop volumineux (max 2 Mo)');
                input.value = '';
            }
        } */
    </script>
@endsection
