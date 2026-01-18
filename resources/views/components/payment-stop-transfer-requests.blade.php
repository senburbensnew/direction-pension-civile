@extends('layouts.main')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .mt-1 text-sm text-red-600 {
            @apply mt-1 text-sm text-red-600;
        }
    </style>

    <div class="max-w-6xl mx-auto p-6 m-2">
{{--         <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Adhésion Auto-Assurance</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Formulaire</span>
        </nav> --}}

        <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold mb-4">Formulaire d'adhésion au Programme d'Auto-Assurance</h1>
                <p class="text-gray-600">Direction de la Pension Civile</p>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('demandes.arret-paiement.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Informations du Pensionnaire -->
                <fieldset class="shadow-md rounded-lg p-5 border">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Informations du Pensionnaire</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pensioner_code" class="block text-sm font-medium text-gray-700 mb-1">Code Pension
                                *</label>
                            <input type="text" id="code_pension" name="pensioner_code" placeholder="Code Pension"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('pensioner_code') border-red-500 @enderror">
                            @error('pensioner_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Repeat similar structure for other fields -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" id="name" name="name" placeholder="Nom"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Add all other personal information fields following the same pattern -->

                    </div>
                </fieldset>

                <!-- Informations sur l'Assurance -->
                <fieldset class="shadow-md rounded-lg p-5 border">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Informations sur l'Assurance</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="company"
                                class="block text-sm font-medium text-gray-700 mb-1">Compagnie</label>
                            <input type="text" id="company" name="company"
                                placeholder="Nom de la compagnie"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Add other insurance fields -->
                    </div>
                </fieldset>

                <!-- Contribution Funéraire -->
                <fieldset class="shadow-md rounded-lg p-5 border">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Contribution Funéraire</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label for="personne_habilitee" class="block text-sm font-medium text-gray-700 mb-1">Personne(s)
                                habilitée(s) *</label>
                            <input type="text" id="personne_habilitee" name="personne_habilitee"
                                placeholder="Nom et prénom"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Add other contribution fields -->
                    </div>
                </fieldset>

                <!-- Partie I -->
{{--                 <fieldset class="shadow-md rounded-lg p-5 border">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Partie I: Désignation du Dépendant Éligible
                    </legend>
                    <textarea id="partie1" name="partie1" rows="4" placeholder="Informations complémentaires..."
                        class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                </fieldset> --}}

                <!-- Dépendant Éligible / Conjoint(e) -->
                <fieldset class="shadow-md rounded-lg p-5 border">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Dépendant Éligible / Conjoint(e)</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="nom_conjoint" class="block text-sm font-medium text-gray-700 mb-1">Nom *</label>
                            <input type="text" id="nom_conjoint" name="nom_conjoint" placeholder="Nom du conjoint(e)"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <!-- Add other dependent fields -->
                    </div>
                </fieldset>

                <!-- Signature Section -->
{{--                 <fieldset class="shadow-md rounded-lg p-5 border">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="signature" class="block text-sm font-medium text-gray-700 mb-1">Signature *</label>
                            <input type="text" id="signature" name="signature" placeholder="Nom complet"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="date_signature" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                            <input type="date" id="date_signature" name="date_signature"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset> --}}

                <!-- Submit Button -->
                <div class="mt-8 text-right">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                        Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
