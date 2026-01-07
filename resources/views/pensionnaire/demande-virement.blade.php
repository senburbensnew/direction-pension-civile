@extends('layouts.main')

@section('content')

<div id="bank-transfer-request" class="max-w-6xl mx-auto p-6 m-2">
    <form method="POST" action="{{ route('demandes.virements.store') }}"
        enctype="multipart/form-data">

        @csrf

        <!-- MAIN FORM WRAPPER -->
        <div id="bank_transfer_request_form" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
                <div>
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                        class="w-24 h-24 object-cover">
                </div>

                <div class="mx-4 md:mx-12 text-center flex-grow">
                    <h1 class="text-xl md:text-2xl font-bold">
                        MINISTERE DE L’ECONOMIE ET DES FINANCES <br />
                        <span class="underline">PENSION CIVILE</span><br />
                        <span>PAIEMENT PAR VIREMENT BANCAIRE</span><br />
                        <span class="underline font-normal text-base md:text-lg">
                            Formulaire de souscription
                        </span>
                    </h1>
                </div>

                <x-profile-picture />
            </div>

            <!-- FLASH MESSAGES -->
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- VALIDATION ERRORS -->
            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Pensioneer info -->
            <fieldset class="mb-4 shadow-md rounded-lg p-5 border">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Code du pensionné *
                        </label>
                        <input
                            type="text"
                            name="pensioner_code"
                            value="{{ old('pensioner_code', auth()->user()->pension_code) }}"
                            class="mt-1 block w-full rounded-md border-gray-300"
                        >
                    </div>

<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Type de Pension *
    </label>

    <div class="p-4 rounded-lg border border-gray-300 space-y-2">
        @foreach($pensionTypes as $type)
            <div class="flex items-center">
                <input
                    type="radio"
                    name="pension_type_id"
                    value="{{ $type['id'] }}"
                    class="h-4 w-4 text-blue-600"
                    @checked(old('pension_type_id') == $type['id'])
                >
                <span class="ml-2 text-sm">{{ $type['name'] }}</span>
            </div>
        @endforeach
    </div>
</div>


                </div>
            </fieldset>

            <!-- PERSONAL INFO -->
            <fieldset class="mb-4 shadow-md rounded-lg p-5 border">
                <legend class="text-sm font-medium text-gray-700 mb-4">
                    Informations personnelles
                </legend>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <!-- NIF -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">NIF *</label>
                        <input type="text" value="{{ auth()->user()->nif }}" name="nif"
                            class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <!-- Nom et prénom -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nom et Prénom(s) *
                        </label>
                        <input type="text" value="{{ auth()->user()->name }}" name="full_name"
                            class="mt-1 block w-full rounded-md border-gray-300 ">
                    </div>

                    <!-- Date de naissance -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Date de naissance *
                        </label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}"
                            class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <!-- État civil -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            État civil *
                        </label>
                        <select name="civil_status_id" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="">-- Sélectionner --</option>
                            @foreach($civilStatuses as $civilStatus)
                                <option value="{{ $civilStatus->id }}" @selected(old('civil_status_id')==$civilStatus->id
                                    )>{{ $civilStatus->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sexe -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Sexe *
                        </label>
                        <select name="gender_id" class="mt-1 block w-full rounded-md border-gray-300">
                            <option value="">-- Sélectionner --</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender->id }}" @selected(old('gender_id')==$gender->
                                    id)>{{ $gender->name }}</option>
                            @endforeach
                        </select>

                    </div>

                    <!-- Montant allocation -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Montant allocation *
                        </label>
                        <input type="number" step="0.01" name="allocation_amount"
                            value="{{ old('allocation_amount') }}"
                            class="mt-1 block w-full rounded-md border-gray-300" placeholder="Ex: 25000">
                    </div>

                    <!-- Nom de la mère -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Nom et Prénom(s) de la mère *
                        </label>
                        <input type="text" name="mother_name" value="{{ old('mother_name') }}"
                            class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <!-- Portable -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Portable *
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone') }}"
                            class="mt-1 block w-full rounded-md border-gray-300" placeholder="+509 xxxx xxxx">
                    </div>

                    <!-- Adresse (full width) -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">
                            Adresse *
                        </label>
                        <input type="text" name="address" value="{{ old('address') }}"
                            class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                    <!-- Ville -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Ville *
                        </label>
                        <input type="text" name="city" value="{{ old('city') }}"
                            class="mt-1 block w-full rounded-md border-gray-300">
                    </div>

                </div>
            </fieldset>

            <!-- PENSION categorie  -->
<fieldset class="mb-4 shadow-md rounded-lg p-5 border">
    <legend class="text-sm font-medium text-gray-700 mb-4">
        Catégorie de la pension *
    </legend>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($pensionCategories as $pensionCategory)
            <label class="flex items-center space-x-2 cursor-pointer">
                <input
                    type="radio"
                    name="pension_category_id"
                    value="{{ $pensionCategory->id }}"
                    class="rounded border-gray-300 text-blue-600"
                    @checked(old('pension_category_id') == $pensionCategory->id)
                >
                <span class="text-sm text-gray-700">{{ $pensionCategory->name }}</span>
            </label>
        @endforeach
    </div>
</fieldset>



            <!-- BANK INFO -->
            <fieldset class="mb-4 shadow-md rounded-lg p-5 border">
                <legend class="text-sm font-medium text-gray-700 mb-4">
                    Informations Bancaires *
                </legend>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Banque du pensionné (Banque locale) *
                        </label>
                        <input type="text" name="bank_name" value="{{ old('bank_name') }}"
                            placeholder="Banque" class="mt-1 w-full rounded-md border-gray-300">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Numéro de compte *
                            </label>
                            <input type="number" name="account_number"
                                value="{{ old('account_number') }}" placeholder="Numéro de compte"
                                class="mt-1 w-full rounded-md border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Nom du compte *
                            </label>
                            <input type="text" name="account_name" value="{{ old('account_name') }}"
                                placeholder="Nom du compte" class="mt-1 w-full rounded-md border-gray-300">
                        </div>
                    </div>
                </div>

                <p class="mt-4 text-sm text-gray-600 italic">
                    Seul le numéro de compte personnel du pensionné est autorisé pour cette opération.
                </p>
            </fieldset>

            <!-- DECLARATION -->
            <fieldset class="mb-6 shadow-md rounded-lg p-5 border">
                <legend class="text-sm font-medium text-gray-700 mb-4">
                    Déclaration et engagement *
                </legend>

                <p class="text-sm text-gray-700 leading-relaxed mb-6">
                    Par la présente, je demande à la Direction du Trésor de virer sur mon compte personnel
                    le montant net de mes prestations mensuelles et je l’autorise à déduire toutes valeurs
                    indûment versées sur mon compte, en l’absence d’un règlement à l’amiable.
                </p>

                <!-- Signature + Acceptance -->
                <div class="flex flex-col gap-6">

                    <!-- Signature -->
                    <div class="w-1/2">
                        <!-- <x-signature-pad /> -->
                    </div>

                    <!-- Checkbox + Date -->
                    <div class="flex justify-between items-end">

                        <!-- Declaration checkbox -->
                        <label class="flex items-start space-x-3 max-w-xl">
                            <input type="checkbox" name="declaration_accepted" value="1" 
                                class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500" >


                            <span class="text-sm text-gray-700">
                                Je certifie l’exactitude des informations fournies et j’accepte la présente déclaration.
                            </span>
                        </label>

                        <!-- Date -->
                        <div class="text-sm text-gray-800 font-medium">
                            le {{ now()->format('d/m/Y') }}
                            <input type="hidden" name="declaration_date" value="{{ now()->toDateString() }}">
                        </div>

                    </div>

                    <!-- Validation error -->
@error('declaration_accepted')
    <p class="mt-2 text-sm text-red-600">
        {{ $message }}
    </p>
@enderror


                </div>
            </fieldset>


            <!-- SUBMIT -->
            <div class="mt-8 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    Soumettre
                </button>
            </div>
        </div>
    </form>
</div>

@endsection
