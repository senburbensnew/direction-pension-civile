@extends('layouts.main')

@section('content')

<div id="bank-transfer-request" class="max-w-6xl mx-auto p-6 m-2">
    <form method="POST" action="{{ route('demandes.virements.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
                <h1 class="text-xl md:text-2xl font-bold text-center">
                    MINISTERE DE L’ECONOMIE ET DES FINANCES<br>
                    <span class="underline">PENSION CIVILE</span><br>
                    PAIEMENT PAR VIREMENT BANCAIRE
                </h1>
                <div>
                    <x-profile-picture />
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <!-- PENSIONER INFO -->
            <fieldset class="mb-4 p-5 border rounded-lg">
                <div class="grid md:grid-cols-2 gap-4">

                    <!-- Pensioner code -->
                    <div>
                        <label>Code du pensionné *</label>
                        <input type="text" name="code_pension"
                            value="{{ old('code_pension', auth()->user()->pension_code) }}"
                            class="mt-1 w-full rounded-md border @error('code_pension') border-red-500 @else border-gray-300 @enderror">
                        @error('code_pension') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <!-- Pension type -->
                    <div>
                        <label>Type de pension *</label>
                        <div class="p-4 rounded border @error('type_pension_id') border-red-500 @else border-gray-300 @enderror">
                            @foreach($pensionTypes as $type)
                                <label class="flex items-center">
                                    <input type="radio" name="type_pension_id" value="{{ $type['id'] }}"
                                        @checked(old('type_pension_id') == $type['id'])>
                                    <span class="ml-2">{{ $type['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type_pension_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                </div>
            </fieldset>

            <!-- PERSONAL INFO -->
            <fieldset class="mb-4 p-5 border rounded-lg">
                <legend>Informations personnelles</legend>

                <div class="grid md:grid-cols-2 gap-4">

                @foreach([
                    'nif' => 'NIF',
                    'nom_complet' => 'Nom et Prénom(s)',
                    'date_naissance' => 'Date de naissance',
                    'montant_allocation' => 'Montant allocation',
                    'nom_mere' => 'Nom de la mère',
                    'telephone' => 'Téléphone',
                    'ville' => 'Ville'
                ] as $name => $label)

                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $label }} *
                        </label>

                        <input
                            placeholder="{{ 
                                $name === 'nif' 
                                    ? '123-456-789-0' 
                                    : ($name === 'telephone' ? '+509 XXXX-XXXX' : '') 
                            }}"
                            type="{{ $name === 'date_naissance' ? 'date' : 'text' }}"
                            name="{{ $name }}"
                            value="{{ old($name, auth()->user()->$name ?? '') }}"
                            class="mt-1 w-full rounded-md border
                                @error($name) border-red-500 @else border-gray-300 @enderror"
                                
                        >

                        @error($name)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                @endforeach


                    <!-- Address -->
                    <div class="md:col-span-2">
                        <label>Adresse *</label>
                        <input type="text" name="adresse" value="{{ old('adresse') }}"
                            class="mt-1 w-full rounded-md border @error('adresse') border-red-500 @else border-gray-300 @enderror">
                        @error('adresse') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <!-- Civil status -->
                    <div>
                        <label>État civil *</label>
                        <select name="statut_civil_id"
                            class="mt-1 w-full rounded-md border @error('statut_civil_id') border-red-500 @else border-gray-300 @enderror">
                            <option value="">-- Sélectionner --</option>
                            @foreach($civilStatuses as $civil)
                                <option value="{{ $civil->id }}" @selected(old('statut_civil_id') == $civil->id)>
                                    {{ $civil->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('statut_civil_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label>Sexe *</label>
                        <select name="sexe_id"
                            class="mt-1 w-full rounded-md border @error('sexe_id') border-red-500 @else border-gray-300 @enderror">
                            <option value="">-- Sélectionner --</option>
                            @foreach($genders as $gender)
                                <option value="{{ $gender->id }}" @selected(old('sexe_id') == $gender->id)>
                                    {{ $gender->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sexe_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>

                </div>
            </fieldset>

            <!-- PENSION CATEGORY -->
            <fieldset class="mb-4 p-5 border rounded-lg @error('categorie_pension_id') border-red-500 @enderror">
                <legend>Catégorie de la pension *</legend>
                @foreach($pensionCategories as $cat)
                    <label class="flex items-center">
                        <input type="radio" name="categorie_pension_id" value="{{ $cat->id }}"
                            @checked(old('categorie_pension_id') == $cat->id)>
                        <span class="ml-2">{{ $cat->name }}</span>
                    </label>
                @endforeach
                @error('categorie_pension_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
            </fieldset>

            <!-- BANK INFO -->
            <fieldset class="mb-4 p-5 border rounded-lg">
                <legend>Informations bancaires *</legend>

                @foreach(['nom_banque' => 'Banque', 'numero_compte' => 'Numéro de compte', 'nom_compte' => 'Nom du compte'] as $name => $label)
                    <div class="mb-4">
                        <label>{{ $label }}</label>
                        <input type="text" name="{{ $name }}" value="{{ old($name) }}"
                            class="mt-1 w-full rounded-md border @error($name) border-red-500 @else border-gray-300 @enderror">
                        @error($name) <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </fieldset>

            <!-- DECLARATION -->
            <fieldset class="mb-6 p-5 border rounded-lg">
                <label class="flex items-start">
                    <input type="checkbox" name="consentement" value="1">
                    <span class="ml-2 text-sm">Je certifie l’exactitude des informations</span>
                </label>
                @error('consentement')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="text-right">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded">
                    Soumettre
                </button>
            </div>

        </div>
    </form>
</div>

@endsection
