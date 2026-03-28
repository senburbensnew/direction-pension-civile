@extends('layouts.main')

@section('content')
    <div class="max-w-6xl mx-auto p-6">
        <div id="form-section" class="mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <div class="flex justify-center gap-5">
                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" 
                    alt="Logo de la Direction de la Pension Civile"
                    class="w-16 h-16 md:w-20 md:h-20 object-contain" />

                    <div class="text-center mb-8">
                        <h1 class="text-xl font-bold mb-2">MINISTERE DE L'ECONOMIE ET DES FINANCES (MEF)</h1>
                        <p class="text-gray-600">DIRECTION DE LA PENSION CIVILE (DPC)</p>
                        <p class="text-gray-600">FICHE INDIVIDUELLE DES EMPLOYES COTISANT AU FONDS DE PENSION</p>
                    </div>

                    <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" 
                    alt="Logo de la Direction de la Pension Civile"
                    class="w-16 h-16 md:w-20 md:h-20 object-contain" />
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-100 p-4 text-sm text-red-700">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form class="mt-5" action="{{ route('demandes.demande-adhesion.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">
                                Titre personnalisé de la demande
                            </label>
                            <input
                                id="title"
                                type="text"
                                name="title"
                                value="{{ old('title', data_get($demande, 'title', '')) }}"
                                placeholder=""
                                {{ $demande && !empty($demande->title) ? 'readonly' : '' }}
                                class="mt-1 block w-full rounded-md shadow-sm {{ $demande && !empty($demande->title) ? 'border-gray-200 bg-gray-100' : 'border-gray-300' }}
                                    @error('title') border-red-500 focus:border-red-500 focus:ring-red-500
                                    @else border-gray-300 focus:border-blue-500 focus:ring-blue-500
                                    @enderror"
                            />
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @if($demande)
                            <input
                                type="hidden"
                                name="demande_id"
                                value="{{ $demande->id }}"
                            >
                        @endif
                </div>

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
                                     value="{{ old('institution', data_get($demande, 'data.institution', '')) }}"
                                    >
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
                                        value="{{ old('lastname', data_get($demande, 'data.lastname', '')) }}">
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
                                        value="{{ old('firstname', data_get($demande, 'data.firstname', '')) }}">
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
                                         value="{{ old('mother_lastname', data_get($demande, 'data.mother_lastname', '')) }}">
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
                                        value="{{ old('mother_firstname', data_get($demande, 'data.mother_firstname', '')) }}">
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
                                        value="{{ old('birth_place', data_get($demande, 'data.birth_place', '')) }}">
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
                                        value="{{ old('birth_date', data_get($demande, 'data.birth_date', '')) }}">
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
                                        placeholder="000-000-000-0"
                                        class="w-full rounded-md border-gray-300 @error('nif') input-error @enderror"
                                         data-inputmask="'mask': '999-999-9999'" value="{{ old('nif', data_get($demande, 'data.nif', '')) }}">
                                    @error('nif')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">
                                        NINU *
                                    </label>
                                    <input type="text" id="ninu" name="ninu"
                                    placeholder="000-000-000-0"
                                        class="w-full rounded-md border-gray-300 @error('ninu') input-error @enderror"
                                        data-inputmask="'mask': '9999-9999-9999'" value="{{ old('ninu', data_get($demande, 'data.ninu', '')) }}">
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
                                            <option value="{{ $gender['id'] }}" @selected(old('gender_id', data_get($demande, 'data.gender_id')) == $gender['id'])>
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
                                            <option value="{{ $status['id'] }}" @selected(old('civil_status_id', data_get($demande, 'data.civil_status_id')) == $status['id'])>
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
                                        value="{{ old('spouse_lastname', data_get($demande, 'data.spouse_lastname', '')) }}">
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
                                        value="{{ old('spouse_firstname', data_get($demande, 'data.spouse_firstname', '')) }}">
                                    @error('spouse_firstname')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Profile Picture -->
                        <div class="ml-4">
                            <x-profile-picture :showLabel="true" />
                                        @if($demande)
                                            @foreach ($demande->documents as $document)
                                                @if($document->type == 'profile_photo')
                                                    <p class="mb-2 text-sm text-gray-600 flex justify-center items-center">
                                                        <a href="{{ Storage::url($document->path) }}" target="_blank" class="text-blue-600 underline">
                                                            {{-- {{ $document->original_name }} --}}
                                                            Voir photo
                                                        </a>
                                                                
                                                        <button type="button"
                                                                class="text-red-500 hover:text-red-700 p-2"
                                                                onclick="deleteDocument({{ $document->id }})">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </p>
                                                @endif
                                            @endforeach
                                        @endif                            
                            @error('profile_picture')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Dependents Section -->
                    @php
                        // Decode dependents from JSON field when editing
                        $dbDependents = data_get($demande, 'data.dependents', []);
                    @endphp

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Enfants à charge
                        </label>

                        <div id="dependents-container">
                            <!-- Template for JS-added dependents -->
                            <template id="dependentTemplate">
                                <div class="dependent-entry grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">

                                    <div>
                                        <input type="text"
                                            name="dependents[__index__][lastname]"
                                            placeholder="Nom de l'enfant"
                                            class="w-full rounded-md border-gray-300">
                                    </div>

                                    <div>
                                        <input type="text"
                                            name="dependents[__index__][firstname]"
                                            placeholder="Prénom de l'enfant"
                                            class="w-full rounded-md border-gray-300">
                                    </div>

                                    <div>
                                        <input type="date"
                                            name="dependents[__index__][birthdate]"
                                            class="w-full rounded-md border-gray-300">
                                    </div>

                                    <div>
                                        <select name="dependents[__index__][relation]"
                                            class="w-full rounded-md border-gray-300">
                                            <option value="fils">Fils</option>
                                            <option value="fille">Fille</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center justify-end">
                                            <button type="button"  data-action="delete-dependent"
                                                class="text-red-600 hover:text-red-900">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
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
                                class="p-4 text-center text-gray-500
                                {{ count(old('dependents', [])) || count($dbDependents) ? 'hidden' : '' }}">
                                Aucun enfant enregistré.
                            </div>

                            <!-- ========================= -->
                            <!-- OLD INPUT (VALIDATION) -->
                            <!-- ========================= -->
                            @foreach (old('dependents', []) as $index => $dependent)
                                <div class="dependent-entry grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">

                                    <div>
                                        <input type="text"
                                            name="dependents[{{ $index }}][lastname]"
                                            value="{{ old("dependents.$index.lastname") }}"
                                            placeholder="Nom de l'enfant"
                                            class="w-full border rounded px-2 py-1 @error("dependents.$index.lastname") border-red-500 @enderror">
                                        @error("dependents.$index.lastname")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <input type="text"
                                            name="dependents[{{ $index }}][firstname]"
                                            value="{{ old("dependents.$index.firstname") }}"
                                            placeholder="Prénom de l'enfant"
                                            class="w-full border rounded px-2 py-1 @error("dependents.$index.firstname") border-red-500 @enderror">
                                        @error("dependents.$index.firstname")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <input type="date"
                                            name="dependents[{{ $index }}][birthdate]"
                                            value="{{ old("dependents.$index.birthdate") }}"
                                            class="w-full border rounded px-2 py-1 @error("dependents.$index.birthdate") border-red-500 @enderror">
                                        @error("dependents.$index.birthdate")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <select name="dependents[{{ $index }}][relation]"
                                            class="w-full border rounded px-2 py-1 @error("dependents.$index.relation") border-red-500 @enderror">
                                            <option value="fils" {{ old("dependents.$index.relation") === 'fils' ? 'selected' : '' }}>Fils</option>
                                            <option value="fille" {{ old("dependents.$index.relation") === 'fille' ? 'selected' : '' }}>Fille</option>
                                        </select>
                                        @error("dependents.$index.relation")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="flex items-center justify-end">
                                            <button type="button"  data-action="delete-dependent"
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

                            <!-- ========================= -->
                            <!-- DB JSON PREFILL (EDIT) -->
                            <!-- ========================= -->
                            @if (!old('dependents') && count($dbDependents))
                                @foreach ($dbDependents as $index => $dependent)
                                    <div class="dependent-entry grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">

                                        <div class="md:col-span-1">
                                            <input type="text"
                                                name="dependents[{{ $index }}][lastname]"
                                                value="{{ $dependent['lastname'] ?? '' }}"
                                                placeholder="Nom de l'enfant"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.lastname') border-red-500 @enderror">
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                        </div>

                                        <div class="md:col-span-1">
                                            <input type="text"
                                                name="dependents[{{ $index }}][firstname]"
                                                value="{{ $dependent['firstname'] ?? '' }}"
                                                placeholder="Prénom de l'enfant"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.lastname') border-red-500 @enderror">
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                        </div>

                                        <div class="md:col-span-1">
                                            <input type="date"
                                                name="dependents[{{ $index }}][birthdate]"
                                                value="{{ $dependent['birthdate'] ?? '' }}"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.lastname') border-red-500 @enderror">
                                                <div class="validation-message text-red-500 text-xs mt-1"></div>
                                        </div>

                                        <div class="md:col-span-1">
                                            <select name="dependents[{{ $index }}][relation]"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.relation') border-red-500 @enderror">
                                                    <option value="fils" {{ ($dependent['relation'] ?? '') === 'fils' ? 'selected' : '' }}>Fils</option>
                                                    <option value="fille" {{ ($dependent['relation'] ?? '') === 'fille' ? 'selected' : '' }}>Fille</option>
                                            </select>
                                            <div class="validation-message text-red-500 text-xs mt-1"></div>
                                        </div>

                                        <div class="flex items-center justify-end">
                                            <button type="button"  data-action="delete-dependent"
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
                            @endif
                        </div>

                        <button type="button" id="add-dependent"
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            + Ajouter un enfant
                        </button>
                    </div>
                </fieldset>

                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <legend class="text-lg font-semibold mb-4">Informations Professionnelles</legend>

                    @php
                        // Decode previous jobs from JSON field when editing
                        $dbPreviousJobs = isset($demande) && data_get($demande, 'data.previous_jobs')
                            ? data_get($demande, 'data.previous_jobs')
                            : [];
                    @endphp

                    <!-- Emplois antérieurs -->
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Emplois antérieurs dans d'autres institutions
                        </label>

                        <div id="previous-jobs-container">
                            <!-- Template for JS-added jobs -->
                            <template id="jobTemplate">
                                <div class="previous-job grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">

                                    <div>
                                        <input type="text"
                                            name="previous_jobs[__index__][institution]"
                                            placeholder="Nom de l'institution"
                                            class="w-full rounded-md border-gray-300">
                                    </div>

                                    <div>
                                        <input type="date"
                                            name="previous_jobs[__index__][start_date]"
                                            class="w-full rounded-md border-gray-300">
                                    </div>

                                    <div>
                                        <input type="date"
                                            name="previous_jobs[__index__][end_date]"
                                            class="w-full rounded-md border-gray-300">
                                    </div>

                                    <div class="flex items-center justify-end">
                                        <button type="button"  data-action="delete-job"
                                                class="text-red-600 hover:text-red-900">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
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
                                class="p-4 text-center text-gray-500
                                {{ count(old('previous_jobs', [])) || count($dbPreviousJobs) ? 'hidden' : '' }}">
                                Aucun emploi antérieur enregistré.
                            </div>

                            <!-- ========================= -->
                            <!-- OLD INPUT (VALIDATION) -->
                            <!-- ========================= -->
                            @foreach (old('previous_jobs', []) as $index => $job)
                                <div class="previous-job grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">

                                    <div>
                                        <input type="text"
                                            name="previous_jobs[{{ $index }}][institution]"
                                            value="{{ old("previous_jobs.$index.institution") }}"
                                            placeholder="Nom de l'institution"
                                            class="w-full rounded-md border-gray-300 px-2 py-1 @error("previous_jobs.$index.institution") border-red-500 @enderror">
                                        @error("previous_jobs.$index.institution")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <input type="date"
                                            name="previous_jobs[{{ $index }}][start_date]"
                                            value="{{ old("previous_jobs.$index.start_date") }}"
                                            class="w-full rounded-md border-gray-300 px-2 py-1 @error("previous_jobs.$index.start_date") border-red-500 @enderror">
                                        @error("previous_jobs.$index.start_date")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div>
                                        <input type="date"
                                            name="previous_jobs[{{ $index }}][end_date]"
                                            value="{{ old("previous_jobs.$index.end_date") }}"
                                            class="w-full rounded-md border-gray-300 px-2 py-1 @error("previous_jobs.$index.end_date") border-red-500 @enderror">
                                        @error("previous_jobs.$index.end_date")
                                            <span class="text-red-500 text-xs">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="flex items-center justify-end">
                                            <button type="button"  data-action="delete-job"
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

                            <!-- ========================= -->
                            <!-- DB JSON PREFILL (EDIT) -->
                            <!-- ========================= -->
                            @if (!old('previous_jobs') && count($dbPreviousJobs))
                                @foreach ($dbPreviousJobs as $index => $job)
                                    <div class="previous-job grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">

                                        <div>
                                            <input type="text"
                                                name="previous_jobs[{{ $index }}][institution]"
                                                value="{{ $job['institution'] ?? '' }}"
                                                placeholder="Nom de l'institution"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.lastname') border-red-500 @enderror">
                                        </div>

                                        <div>
                                            <input type="date"
                                                name="previous_jobs[{{ $index }}][start_date]"
                                                value="{{ $job['start_date'] ?? '' }}"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.lastname') border-red-500 @enderror">
                                        </div>

                                        <div>
                                            <input type="date"
                                                name="previous_jobs[{{ $index }}][end_date]"
                                                value="{{ $job['end_date'] ?? '' }}"
                                                class="w-full rounded-md border-gray-300 @error('dependents.$index.lastname') border-red-500 @enderror">
                                        </div>

                                        <div class="flex items-center justify-end">
                                            <button type="button"  data-action="delete-job"
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
                            @endif
                        </div>

                        <button type="button" id="add-job"
                            class="mt-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            + Ajouter un emploi
                        </button>
                    </div>

                    <!-- Autres champs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="entry_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Date d'entrée en fonction dans l'organisme actuel *
                            </label>
                            <input type="date" id="entry_date" name="entry_date"
                                class="w-full rounded-md border-gray-300 @error('entry_date') input-error @enderror"
                                value="{{ old('entry_date', data_get($demande, 'data.entry_date', '')) }}">
                            @error('entry_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="current_salary" class="block text-sm font-medium text-gray-700 mb-1">
                                Salaire mensuel actuel (brut) (Gdes) *
                            </label>
                            <input type="number" id="current_salary" name="current_salary" step="0.01"
                                class="w-full rounded-md border-gray-300 @error('current_salary') input-error @enderror"
                                value="{{ old('current_salary', data_get($demande, 'data.current_salary', '')) }}">
                            @error('current_salary')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </fieldset>

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
                <div class="mt-8 flex gap-5 justify-end">
                        @if (!$demande || $demande->isDraft())  
                            <button
                                type="submit"
                                name="action"
                                value="draft"
                                class="inline-flex items-center justify-center p-2 border border-transparent text-base font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                                Sauvegarder
                            </button>
                        @endif
                        @if (!$demande || $demande->isDraft())
                                <button
                                    type="submit"
                                    name="action"
                                    value="submit"
                                    class="inline-flex items-center justify-center p-2 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                    Soumettre
                                </button>
                        @endif
                </div>
            </form>
            <form id="delete-document-form" method="POST" style="display:none;">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
@endsection

<script>
        function deleteDocument(documentId) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            const form = document.getElementById('delete-document-form');
            form.action = `/demandedocument/${documentId}`;
            form.submit();
        }
    // Dependents Section Script
    document.addEventListener('DOMContentLoaded', function () {

        const container = document.getElementById('dependents-container');
        const messageDiv = document.getElementById('no-dependents-message');

        // ✅ FIX: count what already exists in DOM
        let dependentCount = container.querySelectorAll('.dependent-entry').length;

        // Event delegation for delete
        container.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('[data-action="delete-dependent"]');
            if (deleteBtn) {
                deleteDependent(deleteBtn);
            }
        });

        function addDependent() {
            const template = document.getElementById('dependentTemplate');
            const newIndex = dependentCount;

            const newEntry = document.importNode(template.content, true);

            newEntry.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/__index__/g, newIndex);
            });

            container.insertBefore(newEntry, messageDiv);
            dependentCount++;
            messageDiv.classList.add('hidden');
        }

        function deleteDependent(button) {
            const entry = button.closest('.dependent-entry');
            if (!entry) return;

            entry.remove();

            // Reindex remaining entries
            const entries = container.querySelectorAll('.dependent-entry');
            entries.forEach((entry, index) => {
                entry.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/dependents\[\d+]/g, `dependents[${index}]`);
                });
            });

            dependentCount = entries.length;
            messageDiv.classList.toggle('hidden', dependentCount > 0);
        }

        document.getElementById('add-dependent')
            .addEventListener('click', addDependent);
    });

    // Previous Jobs Section Script
    document.addEventListener('DOMContentLoaded', function () {

        const container = document.getElementById('previous-jobs-container');
        const messageDiv = document.getElementById('no-jobs-message');
        const addBtn = document.getElementById('add-job');

        // ✅ FIX: count existing jobs in DOM (old + DB)
        let jobIndex = container.querySelectorAll('.previous-job').length;

        // Event delegation for delete
        container.addEventListener('click', function (e) {
            const deleteBtn = e.target.closest('[data-action="delete-job"]');
            if (deleteBtn) {
                deleteJob(deleteBtn);
            }
        });

        function addJob() {
            const template = document.getElementById('jobTemplate');

            const newEntry = document.importNode(template.content, true);

            newEntry.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/__index__/g, jobIndex);
            });

            container.insertBefore(newEntry, messageDiv);
            jobIndex++;
            messageDiv.classList.add('hidden');
        }

        function deleteJob(button) {
            const entry = button.closest('.previous-job');
            if (!entry) return;

            entry.remove();

            // Reindex remaining jobs
            const entries = container.querySelectorAll('.previous-job');
            entries.forEach((entry, index) => {
                entry.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(
                        /previous_jobs\[\d+]/g,
                        `previous_jobs[${index}]`
                    );
                });
            });

            jobIndex = entries.length;
            messageDiv.classList.toggle('hidden', jobIndex > 0);
        }

        addBtn.addEventListener('click', addJob);
    });
</script>
