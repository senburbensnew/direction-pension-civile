@extends('layouts.main')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }
    </style>

    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Institutions</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande d'Adhésion</span>
        </nav>

        <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold mb-2">MINISTERE DE L'ECONOMIE ET DES FINANCES (MEF)</h1>
                <p class="text-gray-600">DIRECTION DE LA PENSION CIVILE (DPC)</p>
                <p class="text-gray-600">FICHE INDIVIDUELLE DES EMPLOYES COTISANT AU FONDS DE PENSION</p>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <form action="#" method="POST" class="space-y-6">
                @csrf
                <fieldset class="shadow-md rounded-lg p-5 border">
                    <legend class="text-sm font-medium text-gray-700 mb-2"></legend>
                    <div>
                        <div>
                            <label for="institution" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                l'Institution *</label>
                            <input type="text" id="institution" name="institution" placeholder="Institution"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('institution') border-red-500 @enderror"
                                value="{{ old('institution') }}">
                            @error('institution')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto] gap-2">
                        <div class="">
                            <div>
                                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom
                                    *</label>
                                <input type="text" id="lastname" name="lastname" placeholder="Nom"
                                    class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lastname') border-red-500 @enderror"
                                    value="{{ old('lastname') }}">
                                @error('lastname')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prenom *</label>
                                <input type="text" id="firstname" name="firstname" placeholder="Prenom"
                                    class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('firstname') border-red-500 @enderror"
                                    value="{{ old('firstname') }}">
                                @error('firstname')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="mother_lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom de la
                                    mere
                                    *</label>
                                <input type="text" id="mother_lastname" name="mother_lastname"
                                    placeholder="Nom de la mere"
                                    class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('mother_lastname') border-red-500 @enderror"
                                    value="{{ old('mother_lastname') }}">
                                @error('mother_lastname')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="mother_firstname" class="block text-sm font-medium text-gray-700 mb-1">Prenom de
                                    la
                                    mere
                                    *</label>
                                <input type="text" id="mother_firstname" name="mother_firstname"
                                    placeholder="Prenom de la mere"
                                    class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('mother_firstname') border-red-500 @enderror"
                                    value="{{ old('mother_firstname') }}">
                                @error('mother_firstname')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label for="birth_place" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                    Naissance *</label>
                                <input type="text" id="birth_place" name="birth_place" placeholder="Lieu de naissance"
                                    class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('birth_place') border-red-500 @enderror"
                                    value="{{ old('birth_place') }}">
                                @error('birth_place')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div><x-profile-picture :showLabel="true" /></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                Jeune
                                Fille</label>
                            <input type="text" id="nom_jeune_fille" name="nom_jeune_fille"
                                placeholder="Nom de jeune fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('nom_jeune_fille') }}">
                        </div>
                        <div>
                            <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                Naissance *</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance" placeholder="Lieu de naissance"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') border-red-500 @enderror"
                                value="{{ old('lieu_naissance') }}">
                            @error('lieu_naissance')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                Jeune
                                Fille</label>
                            <input type="text" id="nom_jeune_fille" name="nom_jeune_fille"
                                placeholder="Nom de jeune fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('nom_jeune_fille') }}">
                        </div>
                        <div>
                            <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                Naissance *</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance"
                                placeholder="Lieu de naissance"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') border-red-500 @enderror"
                                value="{{ old('lieu_naissance') }}">
                            @error('lieu_naissance')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                Jeune
                                Fille</label>
                            <input type="text" id="nom_jeune_fille" name="nom_jeune_fille"
                                placeholder="Nom de jeune fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('nom_jeune_fille') }}">
                        </div>
                        <div>
                            <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                Naissance *</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance"
                                placeholder="Lieu de naissance"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') border-red-500 @enderror"
                                value="{{ old('lieu_naissance') }}">
                            @error('lieu_naissance')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                Jeune
                                Fille</label>
                            <input type="text" id="nom_jeune_fille" name="nom_jeune_fille"
                                placeholder="Nom de jeune fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('nom_jeune_fille') }}">
                        </div>
                        <div>
                            <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                Naissance *</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance"
                                placeholder="Lieu de naissance"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') border-red-500 @enderror"
                                value="{{ old('lieu_naissance') }}">
                            @error('lieu_naissance')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                Jeune
                                Fille</label>
                            <input type="text" id="nom_jeune_fille" name="nom_jeune_fille"
                                placeholder="Nom de jeune fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('nom_jeune_fille') }}">
                        </div>
                        <div>
                            <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                Naissance *</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance"
                                placeholder="Lieu de naissance"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') border-red-500 @enderror"
                                value="{{ old('lieu_naissance') }}">
                            @error('lieu_naissance')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">Nom de
                                Jeune
                                Fille</label>
                            <input type="text" id="nom_jeune_fille" name="nom_jeune_fille"
                                placeholder="Nom de jeune fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                                value="{{ old('nom_jeune_fille') }}">
                        </div>
                        <div>
                            <label for="lieu_naissance" class="block text-sm font-medium text-gray-700 mb-1">Lieu de
                                Naissance *</label>
                            <input type="text" id="lieu_naissance" name="lieu_naissance"
                                placeholder="Lieu de naissance"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') border-red-500 @enderror"
                                value="{{ old('lieu_naissance') }}">
                            @error('lieu_naissance')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- Signature et Dates -->
                <fieldset class="shadow-md rounded-lg p-5 border">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Signature
                                *</label>
                            <x-signature-pad name="cotisant" />
                            @error('signature')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date
                                *</label>
                            <x-signature-pad />
                            @error('date_signature')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

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
