<style>
    .input-error {
        @apply border-red-500 focus:border-red-500 focus:ring-red-500;
    }

    .error-message {
        @apply mt-1 text-sm text-red-600;
    }
</style>

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
                    <p class="text-sm md:text-gray-600 mt-1">
                        Exercice : <span id="editableText" contenteditable="true">20.../20...</span></p>
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
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de demande *
                        </label>
                        <input type="date" name="request_date" id="request_date" value="{{ old('request_date') }}"
                            class="w-full rounded-md @error('request_date') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('request_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Pension Regime Section -->
            <fieldset
                class="shadow-md rounded-lg p-5 border mb-6 @error('pension_category_id') border-red-500 @else border-gray-300 @enderror">
                <legend class="text-sm font-medium text-gray-700 mb-2">Régime de Pension *</legend>
                <div class="flex space-x-4">
                    @foreach ($pensionCategories as $type)
                        <div class="flex items-center">
                            <input type="radio" id="regime_civile" name="pension_category_id"
                                value="{{ $type['id'] }}"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                @checked(old('pension_category_id') == $type['id'])>
                            <label for="regime_civile" class="ml-2 text-sm text-gray-700">
                                {{ $type['name'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('pension_category_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <!-- Pension Information -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="pension_code" class="block text-sm font-medium text-gray-700 mb-1">
                            Code pension *
                        </label>
                        <input type="text" name="pensioner_code" id="pension_code"
                            value="{{ auth()->user()->pension_code }}"
                            class="w-full rounded-md @error('pension_code') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                            readonly>
                        @error('pensioner_code')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">
                            Montant (en Gdes) *
                        </label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}"
                            class="w-full rounded-md @error('amount') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            step="0.01" min="0">
                        @error('amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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
                        <input type="text" name="lastname" id="nom" value="{{ old('lastname') }}"
                            class="w-full rounded-md @error('lastname') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('lastname')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">
                            Prénom *
                        </label>
                        <input type="text" name="firstname" id="prenom" value="{{ old('firstname') }}"
                            class="w-full rounded-md @error('firstname') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('firstname')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom de Jeune Fille
                        </label>
                        <input type="text" name="maiden_name" id="nom_jeune_fille"
                            value="{{ old('maiden_name') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('maiden_name') border-red-500 @else border-gray-300 @enderror">
                        @error('maiden_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
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
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">
                            NINU *
                        </label>
                        <input type="text" name="ninu" id="ninu" value="{{ old('ninu') }}"
                            class="w-full rounded-md @error('ninu') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('ninu')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">
                            Adresse *
                        </label>
                        <input type="text" name="address" id="adresse" value="{{ old('address') }}"
                            class="w-full rounded-md @error('address') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('address')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">
                            Téléphone *
                        </label>
                        <input type="tel" name="phone" id="telephone" value="{{ old('phone') }}"
                            class="w-full rounded-md @error('phone') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            pattern="[0-9]{10}">
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Courriel
                        </label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @else border-gray-300 @enderror">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Transfer Details -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <legend class="text-sm font-medium text-gray-700 mb-2">Détails du transfert</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="from" class="block text-sm font-medium text-gray-700 mb-1">
                            De *
                        </label>
                        <input type="date" name="from" id="from" value="{{ old('from') }}"
                            class="w-full rounded-md @error('from') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('from')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700 mb-1">
                            À *
                        </label>
                        <input type="date" name="to" id="to" value="{{ old('to') }}"
                            class="w-full rounded-md @error('to') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('to')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label for="transfer_reason" class="block text-sm font-medium text-gray-700 mb-1">
                        Motif du transfert *
                    </label>
                    <textarea name="transfer_reason" id="transfer_reason" rows="3"
                        class="w-full rounded-md @error('transfer_reason') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">{{ old('transfer_reason') }}</textarea>
                    @error('transfer_reason')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <!-- Signature Section -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <div class="text-left mb-4">
                    <p class="font-semibold text-gray-600 pt-5">
                        Fait à <span contenteditable="true">Port-au-Prince</span>, le
                        <span class="inline-block mx-2 px-2 bg-gray-100 rounded">{{ now()->format('d/m/Y') }}</span>
                    </p>
                </div>
                <div class="w-1/2">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                        Signature du Pensionné
                    </p>
                    <div class="h-0.5 bg-gray-300 my-2"></div>
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
