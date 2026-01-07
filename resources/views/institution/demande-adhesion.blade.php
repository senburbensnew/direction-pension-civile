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

    <div class="max-w-6xl mx-auto p-6 m-2 ">
{{--         <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Institutions</span> >
            <span class="text-gray-800">Demande d'Adhésion</span>
        </nav> --}}

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

            @if (session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-5" action="{{ route('demandes.demande-adhesion.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <!-- Personal Information Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-lg font-semibold mb-4">Informations Personnelles</legend>

                    <div class="grid grid-cols-[1fr_auto] gap-4">
                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label for="institution" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nom de l'Institution *
                                </label>
                                <input type="text" id="institution" name="institution"
                                    class="w-full rounded-md border-gray-300 @error('institution') input-error @enderror"
                                    value="{{ old('institution') }}">
                                @error('institution')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nom *
                                    </label>
                                    <input type="text" id="lastname" name="lastname"
                                        class="w-full rounded-md border-gray-300 @error('lastname') input-error @enderror"
                                        value="{{ old('lastname') }}">
                                    @error('lastname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Prénom *
                                    </label>
                                    <input type="text" id="firstname" name="firstname"
                                        class="w-full rounded-md border-gray-300 @error('firstname') input-error @enderror"
                                        value="{{ old('firstname') }}">
                                    @error('firstname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="mother_lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nom de la mère *
                                    </label>
                                    <input type="text" id="mother_lastname" name="mother_lastname"
                                        class="w-full rounded-md border-gray-300 @error('mother_lastname') input-error @enderror"
                                        value="{{ old('mother_lastname') }}">
                                    @error('mother_lastname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="mother_firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Prénom de la mère *
                                    </label>
                                    <input type="text" id="mother_firstname" name="mother_firstname"
                                        class="w-full rounded-md border-gray-300 @error('mother_firstname') input-error @enderror"
                                        value="{{ old('mother_firstname') }}">
                                    @error('mother_firstname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="birth_place" class="block text-sm font-medium text-gray-700 mb-1">
                                        Lieu de Naissance *
                                    </label>
                                    <input type="text" id="birth_place" name="birth_place"
                                        class="w-full rounded-md border-gray-300 @error('birth_place') input-error @enderror"
                                        value="{{ old('birth_place') }}">
                                    @error('birth_place')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-1">
                                        Date de naissance *
                                    </label>
                                    <input type="date" id="birth_date" name="birth_date"
                                        class="w-full rounded-md border-gray-300 @error('birth_date') input-error @enderror"
                                        value="{{ old('birth_date') }}">
                                    @error('birth_date')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">
                                        NIF *
                                    </label>
                                    <input type="text" id="nif" name="nif"
                                        class="w-full rounded-md border-gray-300 @error('nif') input-error @enderror"
                                        value="{{ old('nif') }}" data-inputmask="'mask': '999-999-9999'">
                                    @error('nif')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">
                                        NINU *
                                    </label>
                                    <input type="text" id="ninu" name="ninu"
                                        class="w-full rounded-md border-gray-300 @error('ninu') input-error @enderror"
                                        value="{{ old('ninu') }}" data-inputmask="'mask': '9999-9999-9999'">
                                    @error('ninu')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="gender_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        Genre *
                                    </label>
                                    <select id="gender_id" name="gender_id"
                                        class="w-full rounded-md border-gray-300 @error('gender_id') input-error @enderror">
                                        <option value="">Sélectionner</option>
                                        @foreach ($genders as $gender)
                                            <option value="{{ $gender['id'] }}" @selected(old('gender_id') == $gender['id'])>
                                                {{ $gender['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('gender_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="civil_status_id" class="block text-sm font-medium text-gray-700 mb-1">
                                        État civil *
                                    </label>
                                    <select id="civil_status_id" name="civil_status_id"
                                        class="w-full rounded-md border-gray-300 @error('civil_status_id') input-error @enderror">
                                        <option value="">Sélectionner</option>
                                        @foreach ($civilStatuses as $status)
                                            <option value="{{ $status['id'] }}" @selected(old('civil_status_id') == $status['id'])>
                                                {{ ucfirst($status['name']) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('civil_status_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Spouse Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="spouse_lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nom du conjoint
                                    </label>
                                    <input type="text" id="spouse_lastname" name="spouse_lastname"
                                        class="w-full rounded-md border-gray-300 @error('spouse_lastname') input-error @enderror"
                                        value="{{ old('spouse_lastname') }}">
                                    @error('spouse_lastname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="spouse_firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Prénom du conjoint
                                    </label>
                                    <input type="text" id="spouse_firstname" name="spouse_firstname"
                                        class="w-full rounded-md border-gray-300 @error('spouse_firstname') input-error @enderror"
                                        value="{{ old('spouse_firstname') }}">
                                    @error('spouse_firstname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Profile Picture -->
                        <div class="ml-4">
                            <x-profile-picture :showLabel="true" />
                            @error('profile_picture')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Dependents Section -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Enfants à charge
                        </label>
                        <div id="dependents-container">
                            <template id="dependentTemplate">
                                <div class="dependent-entry grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                    <!-- Last Name -->
                                    <div class="md:col-span-1">
                                        <input type="text" name="dependents[__index__][lastname]"
                                            placeholder="Nom de l'enfant"
                                            class="w-full rounded-md border-gray-300 @error('dependents.__index__.lastname') border-red-500 @enderror"
                                            value="{{ old('dependents.__index__.lastname') }}">
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- First Name -->
                                    <div class="md:col-span-1">
                                        <input type="text" name="dependents[__index__][firstname]"
                                            placeholder="Prénom de l'enfant"
                                            class="w-full rounded-md border-gray-300 @error('dependents.__index__.firstname') border-red-500 @enderror"
                                            value="{{ old('dependents.__index__.firstname') }}">
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- Birth Date -->
                                    <div class="md:col-span-1">
                                        <input type="date" name="dependents[__index__][birthdate]"
                                            class="w-full rounded-md border-gray-300 @error('dependents.__index__.birthdate') border-red-500 @enderror"
                                            value="{{ old('dependents.__index__.birthdate') }}">
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- Relation -->
                                    <div class="md:col-span-1">
                                        <select name="dependents[__index__][relation]"
                                            class="w-full rounded-md border-gray-300 @error('dependents.__index__.relation') border-red-500 @enderror">
                                            <option value="fils">Fils</option>
                                            <option value="fille">Fille</option>
                                        </select>
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="md:col-span-1 flex items-center justify-end">
                                        <button type="button" data-action="delete-dependent"
                                            class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- No dependents message -->
                            <div id="no-dependents-message"
                                class="p-4 text-center text-gray-500 {{ count(old('dependents', [])) > 0 ? 'hidden' : '' }}">
                                Aucun enfant enregistré.
                            </div>

                            <!-- Existing dependents -->
                            @foreach (old('dependents', []) as $index => $dependent)
                                <div class="dependent-entry grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                                    <div>
                                        <input type="text" name="dependents[{{ $index }}][lastname]"
                                            value="{{ old("dependents.{$index}.lastname") }}"
                                            placeholder="Nom de l'enfant"
                                            class="w-full border rounded px-2 py-1 @error("dependents.{$index}.lastname") border-red-500 @enderror">
                                        @error("dependents.{$index}.lastname")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <input type="text" name="dependents[{{ $index }}][firstname]"
                                            value="{{ old("dependents.{$index}.firstname") }}"
                                            placeholder="Prénom de l'enfant"
                                            class="w-full border rounded px-2 py-1 @error("dependents.{$index}.firstname") border-red-500 @enderror">
                                        @error("dependents.{$index}.firstname")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <input type="date" name="dependents[{{ $index }}][birthdate]"
                                            value="{{ old("dependents.{$index}.birthdate") }}"
                                            class="w-full border rounded px-2 py-1 @error("dependents.{$index}.birthdate") border-red-500 @enderror">
                                        @error("dependents.{$index}.birthdate")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <select name="dependents[{{ $index }}][relation]"
                                            class="w-full border rounded px-2 py-1 @error("dependents.{$index}.relation") border-red-500 @enderror">
                                            <option value="fils"
                                                {{ old("dependents.{$index}.relation") == 'fils' ? 'selected' : '' }}>
                                                Fils</option>
                                            <option value="fille"
                                                {{ old("dependents.{$index}.relation") == 'fille' ? 'selected' : '' }}>
                                                Fille</option>
                                        </select>
                                        @error("dependents.{$index}.relation")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <button type="button" onclick="deleteDependent(this)"
                                            class="text-red-600 hover:text-red-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-dependent"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            + Ajouter un enfant
                        </button>
                    </div>
                </fieldset>

                <!-- Employment Information Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-lg font-semibold mb-4">Informations Professionnelles</legend>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="entry_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date d'entrée en fonction *
                            </label>
                            <input type="date" id="entry_date" name="entry_date"
                                class="w-full rounded-md border-gray-300 @error('entry_date') input-error @enderror"
                                value="{{ old('entry_date') }}">
                            @error('entry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current_salary" class="block text-sm font-medium text-gray-700 mb-1">
                                Salaire mensuel actuel (brut) *
                            </label>
                            <input type="number" id="current_salary" name="current_salary" step="0.01"
                                class="w-full rounded-md border-gray-300 @error('current_salary') input-error @enderror"
                                value="{{ old('current_salary') }}">
                            @error('current_salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Emplois anterieurs -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Emplois antérieurs (nom de l'institution, date début, date fin)
                        </label>
                        <div id="previous-jobs-container">
                            <template id="jobTemplate">
                                <div class="previous-job grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <!-- Institution -->
                                    <div class="md:col-span-1">
                                        <input type="text" name="previous_jobs[__index__][institution]"
                                            placeholder="Nom de l'institution"
                                            class="w-full rounded-md border-gray-300 @error('previous_jobs.__index__.institution') border-red-500 @enderror"
                                            value="{{ old('previous_jobs.__index__.institution') }}">
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- Start Date -->
                                    <div class="md:col-span-1">
                                        <input type="date" name="previous_jobs[__index__][start_date]"
                                            class="w-full rounded-md border-gray-300 @error('previous_jobs.__index__.start_date') border-red-500 @enderror"
                                            value="{{ old('previous_jobs.__index__.start_date') }}">
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- End Date -->
                                    <div class="md:col-span-1">
                                        <input type="date" name="previous_jobs[__index__][end_date]"
                                            class="w-full rounded-md border-gray-300 @error('previous_jobs.__index__.end_date') border-red-500 @enderror"
                                            value="{{ old('previous_jobs.__index__.end_date') }}">
                                        <div class="validation-message text-red-500 text-xs mt-1"></div>
                                    </div>

                                    <!-- Delete Button -->
                                    <div class="md:col-span-1 flex items-center justify-end">
                                        <button type="button" data-action="delete-job"
                                            class="text-red-600 hover:text-red-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>

                            <!-- No jobs message -->
                            <div id="no-jobs-message"
                                class="p-4 text-center text-gray-500 {{ count(old('previous_jobs', [])) > 0 ? 'hidden' : '' }}">
                                Aucun emploi antérieur enregistré.
                            </div>

                            <!-- Existing jobs -->
                            @foreach (old('previous_jobs', []) as $index => $job)
                                <div class="previous-job grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                    <div>
                                        <input type="text" name="previous_jobs[{{ $index }}][institution]"
                                            value="{{ old("previous_jobs.{$index}.institution") }}"
                                            placeholder="Nom de l'institution"
                                            class="w-full rounded-md border-gray-300 px-2 py-1 @error("previous_jobs.{$index}.institution") border-red-500 @enderror">
                                        @error("previous_jobs.{$index}.institution")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <input type="date" name="previous_jobs[{{ $index }}][start_date]"
                                            value="{{ old("previous_jobs.{$index}.start_date") }}"
                                            class="w-full rounded-md border-gray-300 px-2 py-1 @error("previous_jobs.{$index}.start_date") border-red-500 @enderror">
                                        @error("previous_jobs.{$index}.start_date")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div>
                                        <input type="date" name="previous_jobs[{{ $index }}][end_date]"
                                            value="{{ old("previous_jobs.{$index}.end_date") }}"
                                            class="w-full rounded-md border-gray-300 px-2 py-1 @error("previous_jobs.{$index}.end_date") border-red-500 @enderror">
                                        @error("previous_jobs.{$index}.end_date")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="flex items-center justify-end">
                                        <button type="button" onclick="deleteJob(this)"
                                            class="text-red-600 hover:text-red-900">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" id="add-job"
                            class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            + Ajouter un emploi
                        </button>
                    </div>
                </fieldset>

                <!-- Signature Section -->
{{--                 <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Cotisant Signature -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Signature du cotisant *
                            </label>
                            <x-signature-pad name="cotisant_signature" />
                        </div>

                        <!-- DRH Signature -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Direction des Ressources Humaines *
                            </label>
                            <x-signature-pad name="drh_signature" disablePad=true />
                            <p class="text-xs text-gray-500 mt-1">
                                Pour la validation de l'institution
                            </p>
                        </div>
                    </div>

                    <!-- Certification Text -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 italic">
                                Nous certifions que toutes les déclarations contenues dans ce formulaire sont<br>
                                sincères et correctes.
                            </p>
                        </div>
                    </div>
                </fieldset> --}}

                <fieldset class="relative shadow-md rounded-lg p-5 border mb-6">
                    <div class="w-full h-full bg-gray-100/50 absolute inset-0 z-10 flex items-center justify-center">
                    </div>
                    <div class="space-y-4">
                        <div class="text-center text-sm text-gray-600 mb-4">
                            <p>RESERVE A LA DIRECTION DE LA PENSION CIVILE</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Augmentation (Gdes)
                                </label>
                                <input disabled type="number" name="augmentation_amount" step="0.01"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('augmentation_amount') input-error @enderror"
                                    value="{{ old('augmentation_amount') }}">
                                @error('augmentation_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de l'augmentation (jj/mm/aaaa)
                                </label>
                                <input disabled type="date" name="augmentation_date"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('augmentation_date') input-error @enderror"
                                    value="{{ old('augmentation_date') }}">
                                @error('augmentation_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de cessation (jj/mm/aaaa)
                                </label>
                                <input disabled type="date" name="cessation_date"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('cessation_date') input-error @enderror"
                                    value="{{ old('cessation_date') }}">
                                @error('cessation_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de réintégration (jj/mm/aaaa)
                                </label>
                                <input disabled type="date" name="reintegration_date"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('reintegration_date') input-error @enderror"
                                    value="{{ old('reintegration_date') }}">
                                @error('reintegration_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nouveau salaire (Gdes)
                                </label>
                                <input disabled type="number" name="new_salary" step="0.01"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('new_salary') input-error @enderror"
                                    value="{{ old('new_salary') }}">
                                @error('new_salary')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de fin de serv. Ou de carr. (jj/mm/aaaa)
                                </label>
                                <input disabled type="date" name="end_of_service_date"
                                    class="bg-gray-100 w-full rounded-md border-gray-300 @error('end_of_service_date') input-error @enderror"
                                    value="{{ old('end_of_service_date') }}">
                                @error('end_of_service_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 text-right text-xs text-gray-500">
                            DPC/ORG.A/001
                        </div>
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

<script>
    // Dependents Section Script
    document.addEventListener('DOMContentLoaded', function() {
        let dependentCount = {{ count(old('dependents', [])) }};
        const container = document.getElementById('dependents-container');

        // Event delegation for delete buttons
        container.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('[data-action="delete-dependent"]');
            if (deleteBtn) {
                deleteDependent(deleteBtn);
            }
        });

        function addDependent() {
            const template = document.getElementById('dependentTemplate');
            const messageDiv = document.getElementById('no-dependents-message');
            const newIndex = dependentCount;

            // Clone template safely
            const newEntry = document.importNode(template.content, true);
            newEntry.querySelectorAll('[name]').forEach(element => {
                element.name = element.name.replace(/__index__/g, newIndex);
            });

            container.insertBefore(newEntry, messageDiv);
            dependentCount++;
            messageDiv.classList.add('hidden');
        }

        function deleteDependent(button) {
            const entry = button?.closest('.dependent-entry');
            if (!entry) return;

            entry.remove();

            // Reindex with null checks
            const entries = container.querySelectorAll('.dependent-entry');
            entries.forEach((entry, index) => {
                entry.querySelectorAll('[name]').forEach(element => {
                    if (element.name) { // Critical null check
                        element.name = element.name.replace(/dependents\[\d+\]/g,
                            `dependents[${index}]`);
                    }
                });
            });

            dependentCount = entries.length;
            document.getElementById('no-dependents-message')
                .classList.toggle('hidden', dependentCount > 0);
        }

        // Initialize
        document.getElementById('add-dependent').addEventListener('click', addDependent);
    });

    // Previous Jobs Section Script
    document.addEventListener('DOMContentLoaded', function() {
        let jobIndex = {{ count(old('previous_jobs', [])) }};
        const container = document.getElementById('previous-jobs-container');

        // Event delegation for job deletion
        container.addEventListener('click', function(e) {
            const deleteBtn = e.target.closest('[data-action="delete-job"]');
            if (deleteBtn) {
                deleteJob(deleteBtn);
            }
        });

        function addJob() {
            const template = document.getElementById('jobTemplate').content;
            const messageDiv = document.getElementById('no-jobs-message');
            const newEntry = document.importNode(template, true);

            newEntry.querySelectorAll('[name]').forEach(element => {
                element.name = element.name.replace(/__index__/g, jobIndex);
            });

            container.insertBefore(newEntry, messageDiv);
            jobIndex++;
            messageDiv?.classList.add('hidden');
        }

        function deleteJob(button) {
            const entry = button?.closest('.previous-job');
            if (!entry) return;

            entry.remove();

            // Safe reindexing
            const entries = container.querySelectorAll('.previous-job');
            entries.forEach((entry, index) => {
                entry.querySelectorAll('[name]').forEach(element => {
                    if (element.name) { // Essential null check
                        element.name = element.name.replace(/previous_jobs\[\d+\]/g,
                            `previous_jobs[${index}]`);
                    }
                });
            });

            jobIndex = entries.length;
            document.getElementById('no-jobs-message')
                ?.classList.toggle('hidden', entries.length > 0);
        }

        // Initialize
        document.getElementById('add-job').addEventListener('click', addJob);
    });
</script>
