@extends('layouts.main')

<style>
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
            <span class="text-gray-800">Demande de virement</span>
        </nav>

        <div id="bank_transfer_request_form"
            class="tab-content max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
                <!-- Increased bottom margin and added gap -->
                <!-- Logo -->
                <div class="order-1">
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                        class="w-24 h-24 object-cover">
                </div>

                <!-- Title Section -->
                <div class="order-2 mx-4 md:mx-12 text-center flex-grow"> <!-- Added horizontal margins -->
                    <h1 class="text-xl md:text-2xl font-bold">
                        MINISTERE DE L’ECONOMIE ET DES FINANCES <br />
                        <span class="underline">PENSION CIVILE</span><br />
                        <span>PAIEMENT PAR VIREMENT BANCAIRE</span><br />
                        <span class="underline font-normal text-base md:text-lg">Formulaire de souscription</span>
                    </h1>
                </div>

                <!-- Enhanced Photo Upload Component -->
                {{--                 <div class="order-3 flex flex-col items-center space-y-3">
                    <label class="block font-semibold text-gray-700 mb-1">Photo de profil</label>

                    <div class="relative group" id="dropzone">
                        <label for="photoUpload"
                            class="cursor-pointer flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl w-32 h-32 hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 ease-in-out group-hover:scale-102"
                            ondragover="handleDragOver(event)" ondragleave="handleDragLeave(event)"
                            ondrop="handleDrop(event)">

                            <!-- Upload icon -->
                            <svg class="w-8 h-8 text-gray-400 mb-2 group-hover:text-blue-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>

                            <!-- Upload text -->
                            <span class="text-sm text-gray-500 group-hover:text-blue-600 text-center">Cliquez ou
                                glissez-déposez</span>
                        </label>

                        <!-- Preview image with remove button -->
                        <div id="photoPreviewContainer" class="hidden absolute inset-0 w-32 h-32">
                            <img id="photoPreview" class="w-full h-full object-cover rounded-xl border border-gray-200" />
                            <button type="button"
                                class="absolute -top-2 -right-2 bg-white rounded-full p-1 shadow-sm border hover:bg-red-50 transition-colors"
                                onclick="removePhoto()">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- File requirements text -->
                    <p class="text-xs text-gray-500 text-center">JPEG, PNG (Max 5MB)</p>

                    <!-- Error message -->
                    <p id="uploadError" class="text-red-500 text-sm hidden"></p>
                </div> --}}
                <x-profile-picture />
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

            <form method="POST" action="{{ route('pensionnaire.process-virement-request') }}">
                @csrf
                {{-- General Errors --}}
                @if ($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <!-- Hidden file input -->
                <input type="file" id="photoUpload" accept="image/*" class="hidden" onchange="previewPhoto(event)"
                    name="profile_photo">
                <fieldset class="mt-2 mb-2 shadow-md rounded-lg p-5 border">
                    <div class="grid grid-cols-2 gap-4 mb-4 items-center">
                        <div>
                            <label for="pensioner_code" class="block text-sm font-medium text-gray-700">
                                Code du pensionné *
                            </label>
                            <input type="text" name="pensioner_code" id="pensioner_code" {{-- value="{{ old('pensioner_code') }}" --}}
                                value="{{ auth()->user()->pension_code }}"
                                class="mt-1 block w-full rounded-md @error('pensioner_code') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                                readonly>
                            @error('pensioner_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <div class="flex flex-col space-y-2 mt-2">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de Pension *</label>
                                    <div
                                        class="space-y-2 @error('pension_type') border-red-200 @enderror p-4 rounded-lg border">
                                        @foreach ($pensionTypes as $type)
                                            <div class="flex items-center">
                                                <input type="radio" id="pension_type_{{ $type['id'] }}"
                                                    name="pension_type_id" value="{{ $type['id'] }}"
                                                    class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                                    @checked(old('pension_type_id') == $type['id'])>
                                                <label for="pension_type_{{ $type['id'] }}"
                                                    class="ml-3 block text-sm text-gray-700">
                                                    {{ $type['name'] }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    @error('pension_type')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="shadow-md rounded-lg p-5 border">
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="nif" class="block text-sm font-medium text-gray-700">
                                NIF *
                            </label>
                            <input type="text" name="nif" id="nif" {{-- value="{{ old('nif') }}"  --}}
                                value="{{ auth()->user()->nif }}"
                                class="mt-1 block w-full rounded-md @error('nif') border-red-500 @else border-gray-300 @enderror shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100"
                                readonly>
                            @error('nif')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Full Name -->
                        <div>
                            <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom et Prénom(s) *
                            </label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}"
                                class="w-full rounded-md @error('full_name') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('full_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">
                                Adresse *
                            </label>
                            <input type="text" id="address" name="address" value="{{ old('address') }}"
                                class="w-full rounded-md @error('address') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- City -->
                        <div>
                            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">
                                Ville *
                            </label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}"
                                class="w-full rounded-md @error('city') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('city')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Date de Naissance -->
                        <div>
                            <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date de naissance *
                            </label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                                class="w-full rounded-md @error('birth_date') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                max="{{ now()->subDay()->format('Y-m-d') }}">
                            @error('birth_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- État Civil -->
                        <div>
                            <label for="civil_status" class="block text-sm font-medium text-gray-700 mb-1">
                                État civil *
                            </label>
                            <select id="civil_status" name="civil_status_id"
                                class="w-full rounded-md @error('civil_status') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Sélectionner</option>
                                @foreach ($civilStatuses as $status)
                                    <option value="{{ $status['id'] }}" @selected(old('civil_status_id') == $status['id'])>
                                        {{ ucfirst($status['name']) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('civil_status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Sexe -->
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700 mb-1">
                                Sexe *
                            </label>
                            <select id="gender" name="gender_id"
                                class="w-full rounded-md @error('gender') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Sélectionner</option>
                                @foreach ($genders as $gender)
                                    <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                        {{ $gender['name'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('gender')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Montant Allocation -->
                        <div>
                            <label for="allocation_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                Montant allocation *
                            </label>
                            <input type="number" id="allocation_amount" name="allocation_amount"
                                value="{{ old('allocation_amount') }}"
                                class="w-full rounded-md @error('allocation_amount') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                step="0.01" min="0">
                            @error('allocation_amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <!-- Mother's Name -->
                        <div>
                            <label for="mother_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom et Prénom(s) de la mère *
                            </label>
                            <input type="text" id="mother_name" name="mother_name" value="{{ old('mother_name') }}"
                                class="w-full rounded-md @error('mother_name') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('mother_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                Portable *
                            </label>
                            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                                class="w-full rounded-md @error('phone') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                pattern="[0-9]{10}" placeholder="06 00 00 00 00">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset
                    class="mt-2 mb-2 shadow-md rounded-lg p-5 border @error('pension_category') border-red-200 @enderror">
                    <div class="space-y-4">
                        <legend class="text-sm font-medium text-gray-700 mb-2">Catégorie de Pension *</legend>

                        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
                            @foreach ($pensionCategories as $category)
                                <div class="flex items-center">
                                    <input type="radio" id="pension_{{ $category->slug }}" name="pension_category_id"
                                        value="{{ $category->id }}"
                                        class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                        @checked(old('pension_category_id') == $category->id)>
                                    <label for="pension_{{ $category->slug }}" class="ml-2 text-sm text-gray-700">
                                        {{ $category->name }}
                                    </label>
                                </div>
                            @endforeach
                        </div>

                        @error('pension_category')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </fieldset>

                <fieldset
                    class="shadow-md rounded-lg p-5 border @error('bank_name', 'account_number', 'account_name') border-red-200 @enderror">
                    <div class="space-y-4">
                        <legend class="text-sm font-medium text-gray-700 mb-2">Informations Bancaires *</legend>

                        <!-- Bank Name -->
                        <div>
                            <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-1">
                                Banque du pensionné (Banque locale) *
                            </label>
                            <input type="text" id="bank_name" name="bank_name" value="{{ old('bank_name') }}"
                                class="w-full rounded-md @error('bank_name') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                            @error('bank_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Account Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Account Number -->
                            <div>
                                <label for="account_number" class="block text-sm font-medium text-gray-700 mb-1">
                                    Numéro de compte *
                                </label>
                                <input type="number" id="account_number" name="account_number"
                                    value="{{ old('account_number') }}"
                                    class="w-full rounded-md @error('account_number') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                    min="10000" max="99999999999999999999">
                                @error('account_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Account Name -->
                            <div>
                                <label for="account_name" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nom du compte *
                                </label>
                                <input type="text" id="account_name" name="account_name"
                                    value="{{ old('account_name') }}"
                                    class="w-full rounded-md @error('account_name') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                                    maxlength="255">
                                @error('account_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Information Notice -->
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-700">
                                ℹ️ Seul le numéro de compte personnel du pensionné est autorisé pour cette opération.
                            </p>
                        </div>
                    </div>
                </fieldset>

                <div class="mt-8 text-sm text-gray-700">
                    <!-- Legal Text -->
                    <div class="leading-relaxed border-l-4 border-blue-200 pl-4">
                        <p class="italic">
                            Par la présente, je demande à la Direction du Trésor de virer sur mon compte personnel le
                            montant net
                            de mes prestations mensuelles et je l'autorise à déduire toutes valeurs indûment versées sur mon
                            compte, en l'absence d'un règlement à l'amiable.
                        </p>
                    </div>

                    <!-- Date Section -->
                    <div class="text-left mb-8"> <!-- Added margin-bottom -->
                        <p class="font-semibold text-gray-600 pt-5">
                            Fait à <span contenteditable="true">Port-au-Prince</span>, le
                            <span class="inline-block mx-2 px-2 bg-gray-100 rounded">{{ now()->format('d/m/Y') }}</span>
                        </p>
                    </div>

                    <!-- Signatures -->
                    <div class="mt-5 flex flex-col md:flex-row justify-between gap-8 mb-16"> <!-- Added margin-bottom -->
                        <!-- Chef de Service -->
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                Chef de Service de la Comptabilité
                            </p>
                            <div class="h-2 border-t-2 border-gray-700"></div>
                        </div>

                        <!-- Pensionné Signature -->
                        <div class="flex-1">
                            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                                Signature du Pensionné
                            </p>
                            <div class="h-2 border-t-2 border-gray-700"></div>
                            <x-signature-pad />
                        </div>
                    </div>

                    <!-- Footer Information -->
                    <div class="mt-16 text-center space-y-2"> <!-- Increased margin-top -->
                        <p class="text-sm text-gray-500 tracking-wide">
                            dpc/sicp/jr
                        </p>
                        <p class="text-sm font-semibold text-gray-600">
                            5, 16, Ave Charles Sumner et 104, Rue Oswald Durand,<br>
                            Port-au-Prince, Haïti
                        </p>
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded">Soumettre</button>
                </div>
            </form>
        </div>
    </div>

    {{--     <script>
        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);
        const clearButton = document.getElementById('clear-button');
        const signatureData = document.getElementById('signature-data');
        const form = document.getElementById('signature-form');

        // Clear the signature
        clearButton.addEventListener('click', function() {
            signaturePad.clear();
        });

        // Handle form submission
        form.addEventListener('submit', function(event) {
            if (!signaturePad.isEmpty()) {
                signatureData.value = signaturePad.toDataURL(); // Save as base64 image
            } else {
                alert("Please sign before submitting.");
                event.preventDefault();
            }
        });
    </script> --}}
@endsection
