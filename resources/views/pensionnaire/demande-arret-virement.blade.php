@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Pensionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande d'arret de virement</span>
        </nav>

        <!-- Form Section -->
        <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="#">
                @csrf
                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Header Section -->
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-bold uppercase">Direction de la Pension Civile (DPC)</h1>
                    <h2 class="text-xl font-bold uppercase mt-2">Formulaire de Doléances</h2>
                    <h3 class="text-lg font-semibold uppercase mt-2">Service de Comptabilité</h3>
                </div>

                <!-- Date & Code -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium">Date :</label>
                        <input type="date" name="date" value="{{ old('date') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('date') border-red-500 @enderror">
                        @error('date')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium">Code :</label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('code') border-red-500 @enderror"
                            placeholder="">
                        @error('code')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Personal Information -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Informations Personnelles</legend>
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium">Nom :</label>
                            <input type="text" name="nom" value="{{ old('nom') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('nom') border-red-500 @enderror"
                                placeholder="">
                            @error('nom')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Prénom :</label>
                            <input type="text" name="prenom" value="{{ old('prenom') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('prenom') border-red-500 @enderror"
                                placeholder="">
                            @error('prenom')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Téléphone (WhatsApp) :</label>
                            <input type="text" name="telephone" value="{{ old('telephone') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('telephone') border-red-500 @enderror"
                                placeholder="">
                            @error('telephone')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Courriel :</label>
                            <input type="email" name="courriel" value="{{ old('courriel') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('courriel') border-red-500 @enderror"
                                placeholder="">
                            @error('courriel')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- VIREMENT Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">VIREMENT</legend>
                    <div class="mb-4">
                        <label class="block text-sm font-medium">Mois non reçu(s) :</label>
                        <input type="text" name="mois_non_recu" value="{{ old('mois_non_recu') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('mois_non_recu') border-red-500 @enderror"
                            placeholder="">
                        @error('mois_non_recu')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium">Motifs identifiés :</label>
                        <div class="ml-6 space-y-2">
                            @foreach (['Nom incorrect', 'Banque mal identifiée', 'Changement de Compte', 'Changement de Banque'] as $key => $motif)
                                <div class="flex items-center">
                                    <input type="checkbox" id="motif{{ $key }}" name="motifs[]"
                                        value="{{ $motif }}"
                                        {{ in_array($motif, old('motifs', [])) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 @error('motifs') border-red-500 @enderror">
                                    <label for="motif{{ $key }}" class="ml-2 text-sm text-gray-700">
                                        {{ $motif }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('motifs')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium">Nouveau numéro de compte :</label>
                            <input type="text" name="nouveau_numero" value="{{ old('nouveau_numero') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('nouveau_numero') border-red-500 @enderror"
                                placeholder="">
                            @error('nouveau_numero')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium">Nom du compte :</label>
                            <input type="text" name="nom_du_compte" value="{{ old('nom_du_compte') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('nom_du_compte') border-red-500 @enderror"
                                placeholder="">
                            @error('nom_du_compte')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- CHEQUES Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">CHEQUES (RECLAMATION/ TRANSFERT)</legend>
                    <textarea name="cheques"
                        class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('cheques') border-red-500 @enderror h-24"
                        placeholder="Décrivez votre réclamation...">{{ old('cheques') }}</textarea>
                    @error('cheques')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- INFORMATIONS TRANSMISES Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">INFORMATIONS TRANSMISES</legend>
                    <textarea name="informations"
                        class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('informations') border-red-500 @enderror h-16"
                        placeholder="Informations supplémentaires...">{{ old('informations') }}</textarea>
                    @error('informations')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Footer -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6 @error('signature') border-red-500 @enderror">
                    <div class="flex justify-between mt-8">
                        <!-- Pensionné Signature -->
                        <div class="w-1/2">
                            <div class="text-sm font-medium text-gray-700 mb-2">Signature du Pensionné *</div>
                            <div class="h-0.5 bg-gray-300 mb-2"></div>
                            <x-signature-pad />
                            @error('signature')
                                <p class="error-message mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Service Comptabilité -->
                        <div class="w-1/2 text-right">
                            <div class="text-sm font-medium text-gray-700 mb-2">Service Comptabilité</div>
                            <div class="h-0.5 bg-gray-300 mb-2"></div>
                            <div class="text-xs text-gray-500 italic mt-4">
                                <p>Date de réception: {{ now()->format('d/m/Y') }}</p>
                                <p class="mt-2">Cachet & Signature:</p>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="mt-8 text-right">
                    <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition-colors">
                        Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }
    </style>
@endsection
