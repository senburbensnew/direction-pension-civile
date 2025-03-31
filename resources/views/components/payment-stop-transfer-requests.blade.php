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
        <span class="text-gray-800">Demande d'Arrêt de Paiement</span>
    </nav>

    <!-- Form Section -->
    <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
        {{--         <div class="text-center mb-6">
            <h2 class="text-lg md:text-xl font-bold">Direction de la Pension Civile (DPC)</h2>
            <h3 class="text-base md:text-lg font-semibold">Formulaire de Demande de Cessation pour motif de Contrat</h3>
            <p class="text-sm md:text-gray-600">Service de Comptabilité</p>
            <p class="text-sm md:text-gray-600 mt-1">
                Exercice : <span class="@error('fiscal_year') border-red-500 @else border-gray-300 @enderror"
                    contenteditable="true">20... / 20...</span></p>
        </div> --}}

        <div class="flex justify-center items-center mb-12 gap-8">
            <div>
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                    class="w-24 h-24 object-cover">
            </div>

            <!-- Title Section -->
            <div class="text-center mb-6">
                <h2 class="text-lg md:text-xl font-bold">Direction de la Pension Civile (DPC)</h2>
                <h3 class="text-base md:text-lg font-semibold">Formulaire de Demande de Cessation pour motif de Contrat
                </h3>
                <p class="text-sm md:text-gray-600">Service de Comptabilité</p>
                <p class="text-sm md:text-gray-600 mt-1">
                    Exercice : <span id="editableText"
                        class="@error('fiscal_year') border border-red-500 @else border-gray-300 @enderror"
                        contenteditable="true">20... / 20...</span></p>
            </div>

            <div>
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                    class="w-24 h-24 object-cover">
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

        <form id="payment-stop-request-form" method="POST"
            action="{{ route('pensionnaire.process-payment-stop-request') }}">
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

            <input type="hidden" name="fiscal_year" id="hiddenInput" value="{{ old('fiscal_year') }}" />

            <fieldset class="shadow-md rounded-lg p-5 border mb-6 mt-5">
                <legend class="text-sm font-medium text-gray-700 mb-2">Période de Cessation</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_month" class="block text-sm font-medium text-gray-700 mb-1">
                            A partir du mois de *
                        </label>
                        <input type="month" name="start_month" id="start_month" value="{{ old('start_month') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('start_month') border-red-500 @else border-gray-300 @enderror">
                        @error('start_month')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="request_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Date *
                        </label>
                        <input type="date" name="request_date" id="request_date" value="{{ old('request_date') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('request_date') border-red-500 @else border-gray-300 @enderror">
                        @error('request_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

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
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <legend class="text-sm font-medium text-gray-700 mb-2">Informations Personnelles</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="code">Code *</label>
                        <input type="text" name="pensioner_code" id="code"
                            value="{{ auth()->user()->pension_code }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-100 @error('pensioner_code') border-red-500 @else border-gray-300 @enderror"
                            readonly>
                        @error('pensioner_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="amount">Montant (htg)*</label>
                        <input type="number" name="amount" id="amount" value="{{ old('amount') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('amount') border-red-500 @else border-gray-300 @enderror">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="lastname">Nom *</label>
                        <input type="text" name="lastname" id="lastname" value="{{ old('lastname') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('lastname') border-red-500 @else border-gray-300 @enderror">
                        @error('lastname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="firstname">Prénom *</label>
                        <input type="text" name="firstname" id="firstname" value="{{ old('firstname') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('firstname') border-red-500 @else border-gray-300 @enderror">
                        @error('firstname')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="maiden_name">Nom de Jeune Fille *</label>
                        <input type="text" name="maiden_name" id="maiden_name" value="{{ old('maiden_name') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('maiden_name') border-red-500 @else border-gray-300 @enderror">
                        @error('maiden_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nif">NIF *</label>
                        <input type="text" name="nif" id="nif" value="{{ auth()->user()->nif }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 bg-gray-100 @error('nif') border-red-500 @else border-gray-300 @enderror"
                            readonly>
                        @error('nif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ninu">NINU *</label>
                        <input type="text" name="ninu" id="ninu" value="{{ old('ninu') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('ninu') border-red-500 @else border-gray-300 @enderror">
                        @error('ninu')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="adresse">Adresse *</label>
                        <input type="text" name="address" id="adresse" value="{{ old('address') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('address') border-red-500 @else border-gray-300 @enderror">
                        @error('address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="telephone">Telephone *</label>
                        <input type="text" name="phone" id="telephone" value="{{ old('phone') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @else border-gray-300 @enderror">
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="courriel">Courriel *</label>
                        <input type="email" name="email" id="courriel" value="{{ old('email') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @else border-gray-300 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <legend class="text-sm font-medium text-gray-700 mb-2">Période de Cessation</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="from">De *</label>
                        <input type="date" name="from" id="from" value="{{ old('from') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('from') border-red-500 @else border-gray-300 @enderror">
                        @error('from')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="to">À *</label>
                        <input type="date" name="to" id="to" value="{{ old('to') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('to') border-red-500 @else border-gray-300 @enderror">
                        @error('to')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Signature Section -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
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
                    <div class="h-0.5 bg-gray-300 my-2"></div>
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

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("payment-stop-request-form").addEventListener("submit", function(event) {
                let editableText = document.getElementById("editableText").innerText.trim();
                document.getElementById("hiddenInput").value = editableText;
            });
        });
    </script>
@endpush
