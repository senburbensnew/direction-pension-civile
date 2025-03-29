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
            <span class="text-gray-800">Demande de transfert de chèques</span>
        </nav>

        <!-- Form Section -->
        <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
                <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                    <!-- Visible on all screens -->
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 order-1 md:order-none">

                    <div class="px-4 order-2 md:order-none">
                        <h2 class="text-lg md:text-xl font-bold mb-1">Direction de la Pension Civile (DPC)</h2>
                        <h3 class="text-base md:text-lg font-semibold mb-2">Formulaire de doléances / Demande de Transfert
                        </h3>
                        <p class="text-sm md:text-gray-600">Service de Comptabilité</p>
                        <p class="text-sm md:text-gray-600 mt-1">Exercice : <span id="editableText"
                                contenteditable="true">20.../20...</span></p>
                    </div>

                    <!-- Hidden on mobile, visible on md+ screens -->
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                        class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 order-1 md:order-none hidden md:block">
                </div>
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

            <form method="POST" action="{{ route('pensionnaire.process-check-transfer-request') }}"
                id="check-transfert-form">
                @csrf
                <input type="hidden" name="fiscal_year" id="hiddenInput">
                <!-- General Errors -->
                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded mb-4">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Date & Month Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Période de transfert</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_month" class="block text-sm font-medium text-gray-700 mb-1">
                                À partir du mois de *
                            </label>
                            <input type="month" name="start_month" id="start_month" value="{{ old('start_month') }}"
                                class="w-full rounded-md @error('start_month') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('start_month')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date de demande *
                            </label>
                            <input type="date" name="request_date" id="request_date" value="{{ old('request_date') }}"
                                class="w-full rounded-md @error('request_date') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('request_date')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- Pension Regime Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Régime de Pension *</legend>
                    <div class="flex space-x-4">
                        <div class="flex items-center">
                            <input type="radio" id="regime_civile" name="regime_pension" value="civile"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                @checked(old('regime_pension') === 'civile')>
                            <label for="regime_civile" class="ml-2 text-sm text-gray-700">
                                Civile
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input type="radio" id="regime_militaire" name="regime_pension" value="militaire"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                @checked(old('regime_pension') === 'militaire')>
                            <label for="regime_militaire" class="ml-2 text-sm text-gray-700">
                                Militaire
                            </label>
                        </div>
                    </div>
                    @error('regime_pension')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </fieldset>

                <!-- Pension Information -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pension_code" class="block text-sm font-medium text-gray-700 mb-1">
                                Code pension *
                            </label>
                            <input type="text" name="pension_code" id="pension_code"
                                value="{{ auth()->user()->pension_code }}"
                                class="w-full rounded-md @error('pension_code') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                                readonly>
                            @error('pension_code')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="montant" class="block text-sm font-medium text-gray-700 mb-1">
                                Montant (en Gdes) *
                            </label>
                            <input type="number" name="montant" id="montant" value="{{ old('montant') }}"
                                class="w-full rounded-md @error('montant') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                step="0.01" min="0">
                            @error('montant')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <!-- Personal Information -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Informations personnelles</legend>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom *
                            </label>
                            <input type="text" name="nom" id="nom" value="{{ old('nom') }}"
                                class="w-full rounded-md @error('nom') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('nom')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">
                                Prénom *
                            </label>
                            <input type="text" name="prenom" id="prenom" value="{{ old('prenom') }}"
                                class="w-full rounded-md @error('prenom') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('prenom')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de Jeune Fille
                            </label>
                            <input type="text" name="nom_jeune_fille" id="nom_jeune_fille"
                                value="{{ old('nom_jeune_fille') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset>

                <!-- Contact Information -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">
                                NIF *
                            </label>
                            <input type="text" name="nif" id="nif" value="{{ auth()->user()->nif }}"
                                class="w-full rounded-md @error('nif') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                                readonly>
                            @error('nif')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">
                                NINU *
                            </label>
                            <input type="text" name="ninu" id="ninu" value="{{ old('ninu') }}"
                                class="w-full rounded-md @error('ninu') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('ninu')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">
                                Adresse *
                            </label>
                            <input type="text" name="adresse" id="adresse" value="{{ old('adresse') }}"
                                class="w-full rounded-md @error('adresse') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('adresse')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">
                                Téléphone *
                            </label>
                            <input type="tel" name="telephone" id="telephone" value="{{ old('telephone') }}"
                                class="w-full rounded-md @error('telephone') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                pattern="[0-9]{10}">
                            @error('telephone')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="courriel" class="block text-sm font-medium text-gray-700 mb-1">
                                Courriel
                            </label>
                            <input type="email" name="courriel" id="courriel" value="{{ old('courriel') }}"
                                class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </fieldset>

                <!-- Transfer Details -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-sm font-medium text-gray-700 mb-2">Détails du transfert</legend>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="de" class="block text-sm font-medium text-gray-700 mb-1">
                                De *
                            </label>
                            <input type="text" name="de" id="de" value="{{ old('de') }}"
                                class="w-full rounded-md @error('de') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('de')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="a" class="block text-sm font-medium text-gray-700 mb-1">
                                À *
                            </label>
                            <input type="text" name="a" id="a" value="{{ old('a') }}"
                                class="w-full rounded-md @error('a') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('a')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="cheque_motif" class="block text-sm font-medium text-gray-700 mb-1">
                            Motif du transfert *
                        </label>
                        <textarea name="cheque_motif" id="cheque_motif" rows="3"
                            class="w-full rounded-md @error('cheque_motif') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">{{ old('cheque_motif') }}</textarea>
                        @error('cheque_motif')
                            <p class="error-message">{{ $message }}</p>
                        @enderror
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
                            Fait à <span contenteditable="true">Port-au-Prince</span>, le
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

                <!-- Submit Section -->
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

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("check-transfert-form").addEventListener("submit", function(event) {
                let editableText = document.getElementById("editableText").innerText.trim();
                document.getElementById("hiddenInput").value = editableText;
            });
        });
    </script>
@endpush
