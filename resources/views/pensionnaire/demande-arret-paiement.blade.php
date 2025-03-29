@extends('layouts.main')

<style>
    @media print {
        body * {
            visibility: hidden;
        }

        #form-section,
        #form-section * {
            visibility: visible;
        }

        #form-section {
            position: absolute;
            left: 0;
            top: 0;
        }

        #form-section button {
            visibility: hidden;
        }
    }

    .input-error {
        @apply border-red-500 focus:border-red-500 focus:ring-red-500;
    }

    .error-message {
        @apply mt-1 text-sm text-red-600;
    }
</style>

@section('content')
    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Pensionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande d'Arrêt de Paiement</span>
        </nav>

        <!-- Form Section -->
        <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="text-center mb-6">
                <h2 class="text-lg md:text-xl font-bold">Direction de la Pension Civile (DPC)</h2>
                <h3 class="text-base md:text-lg font-semibold">Formulaire de Demande de Cessation pour motif de Contrat</h3>
                <p class="text-sm md:text-gray-600">Service de Comptabilité</p>
                <p class="text-sm md:text-gray-600 mt-1">Exercice : <span contenteditable="true">20... / 20...</span></p>
            </div>

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

                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Période de Cessation</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_month" class="block text-sm font-medium text-gray-700 mb-1">
                                A partir du mois de *
                            </label>
                            <input type="month" name="start_month" id="start_month"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date *
                            </label>
                            <input type="date" name="request_date" id="request_date"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Régime de Pension *</legend>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="regime_pension" value="civile"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"> Civile
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="regime_pension" value="militaire"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"> Militaire
                        </label>
                    </div>
                </fieldset>

                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Informations Personnelles</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="code">Code *</label>
                            <input type="text" name="code" id="code" value="{{ auth()->user()->pension_code }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                                readonly>
                        </div>
                        <div>
                            <label for="montant">Montant (htg)*</label>
                            <input type="text" name="montant" id="montant"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="nom">Nom *</label>
                            <input type="text" name="nom" id="nom"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="prenom">Prénom *</label>
                            <input type="text" name="prenom" id="prenom"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="nom_jeune_fille">Nom de Jeune Fille *</label>
                            <input type="text" name="nom_jeune_fille" id="nom_jeune_fille"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="nif">NIF *</label>
                            <input type="text" name="nif" id="nif" value="{{ auth()->user()->nif }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                                readonly>
                        </div>
                        <div>
                            <label for="ninu">NINU *</label>
                            <input type="text" name="ninu" id="ninu"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="adresse">Adresse *</label>
                            <input type="text" name="adresse" id="adresse"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="telephone">Telephone *</label>
                            <input type="text" name="telephone" id="telephone"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="courriel">Courriel *</label>
                            <input type="text" name="courriel" id="courriel"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Période de Cessation</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="de">De *</label>
                            <input type="text" name="de" id="de"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label for="a">À *</label>
                            <input type="text" name="a" id="a"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset>

                <!-- Signature Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    {{--                     <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="fait_a" class="block text-sm font-medium text-gray-700 mb-1">
                                Fait à *
                            </label>
                            <input type="text" name="fait_a" id="fait_a" value="{{ old('fait_a') }}"
                                class="w-full rounded-md @error('fait_a') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('fait_a')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="signature_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date de signature *
                            </label>
                            <input type="date" name="signature_date" id="signature_date"
                                value="{{ old('signature_date') }}"
                                class="w-full rounded-md @error('signature_date') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('signature_date')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-4 mb-4">
                        <label for="signature" class="block text-sm font-medium text-gray-700 mb-1">
                            Signature *
                        </label>
                        <input type="text" name="signature" id="signature" value="{{ old('signature') }}"
                            class="w-full rounded-md @error('signature') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            placeholder="Signature">
                        @error('signature')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
                    </div> --}}
                    <div class="text-left mb-4"> <!-- Added margin-bottom -->
                        <p class="font-semibold text-gray-600 pt-5">
                            Fait à Port-au-Prince, le
                            <span class="inline-block mx-2 px-2 bg-gray-100 rounded">{{ now()->format('d/m/Y') }}</span>
                        </p>
                    </div>
                    <div class="w-1/2">
                        <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                            Signature du Pensionné
                        </p>
                        <div class="h-2 border-t-2 border-gray-700"></div>
                        <x-signature-pad />
                    </div>
                </fieldset>

                <p class="text-sm text-gray-600 mt-4">NB: Prière de joindre à ce formulaire toutes les pièces justifiant
                    votre requête.</p>

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
