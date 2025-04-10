@extends('layouts.main')

@section('content')
    <style>
        .input-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-sm text-red-600;
        }

        .remove-child {
            @apply text-red-600 hover:text-red-800 cursor-pointer;
        }
    </style>

    <div class="max-w-6xl mx-auto p-6 m-2 bg-white">
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Institutions</span> >
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

            <form action="#" method="POST" enctype="multipart/form-data">
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
                                    <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
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
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Spouse Information -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="spouse_lastname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Nom du conjoint *
                                    </label>
                                    <input type="text" id="spouse_lastname" name="spouse_lastname"
                                        class="w-full rounded-md border-gray-300 @error('spouse_lastname') input-error @enderror"
                                        value="{{ old('spouse_lastname') }}">
                                    @error('spouse_lastname')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label for="spouse_firstname" class="block text-sm font-medium text-gray-700 mb-1">
                                        Prénom du conjoint *
                                    </label>
                                    <input type="text" id="spouse_firstname" name="spouse_firstname"
                                        class="w-full rounded-md border-gray-300 @error('spouse_firstname') input-error @enderror"
                                        value="{{ old('spouse_firstname') }}">
                                    @error('spouse_firstname')
                                        <p class="error-message">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Dependents Section -->
                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Enfants à charge
                                </label>
                                <div id="dependents-container">
                                    <div class="dependent grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                        <input type="text" name="dependents[0][lastname]"
                                            placeholder="Nom de l'enfant" class="rounded-md border-gray-300">
                                        <input type="text" name="dependents[0][firstname]"
                                            placeholder="Prénom de l'enfant" class="rounded-md border-gray-300">
                                        <input type="date" name="dependents[0][birthdate]"
                                            placeholder="Date de naissance" class="rounded-md border-gray-300">
                                        <select name="dependents[0][relation]" class="rounded-md border-gray-300">
                                            <option value="fils">Fils</option>
                                            <option value="fille">Fille</option>
                                        </select>
                                    </div>
                                </div>
                                <button type="button" id="add-dependent"
                                    class="text-blue-600 text-sm hover:text-blue-800">
                                    + Ajouter un enfant
                                </button>
                            </div>
                        </div>

                        <!-- Right Column - Profile Picture -->
                        <div class="ml-4">
                            <x-profile-picture :showLabel="true" />
                            @error('profile_picture')
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
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
                                <p class="error-message">{{ $message }}</p>
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
                                <p class="error-message">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Previous Employment Section -->
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Emplois antérieurs (nom de l'institution, date debut, date fin)
                        </label>
                        <div id="previous-jobs-container">
                            <div class="previous-job grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <input type="text" name="previous_jobs[0][institution]"
                                    placeholder="Nom de l'institution" class="rounded-md border-gray-300">
                                <input type="date" name="previous_jobs[0][start_date]" placeholder="Date début"
                                    class="rounded-md border-gray-300">
                                <input type="date" name="previous_jobs[0][end_date]" placeholder="Date fin"
                                    class="rounded-md border-gray-300">
                            </div>
                        </div>
                        <button type="button" id="add-job" class="text-blue-600 text-sm hover:text-blue-800">
                            + Ajouter un emploi antérieur
                        </button>
                    </div>
                </fieldset>

                <!-- Signature Section -->
                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Cotisant Signature -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Signature du cotisant *
                            </label>
                            <div class="border rounded-lg p-3 bg-gray-50">
                                <x-signature-pad name="cotisant_signature" />
                            </div>
                            @error('cotisant_signature')
                                <p class="error-message mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- DRH Signature -->
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Direction des Ressources Humaines *
                            </label>
                            <div class="border rounded-lg p-3 bg-gray-50">
                                <x-signature-pad name="drh_signature" disablePad=true />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Pour la validation de l'institution
                            </p>
                            @error('drh_signature')
                                <p class="error-message mt-1">{{ $message }}</p>
                            @enderror
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
                </fieldset>

                <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                    <div class="space-y-4">
                        <div class="text-center text-sm text-gray-600 mb-4">
                            <p>RESERVE A LA DIRECTION DE LA PENSION CIVILE</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Augmentation (Gdes)
                                </label>
                                <input readonly type="number" name="augmentation_amount" step="0.01"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('augmentation_amount') input-error @enderror"
                                    value="{{ old('augmentation_amount') }}">
                                @error('augmentation_amount')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de l'augmentation (jj/mm/aaaa)
                                </label>
                                <input readonly type="date" name="augmentation_date"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('augmentation_date') input-error @enderror"
                                    value="{{ old('augmentation_date') }}">
                                @error('augmentation_date')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de cessation (jj/mm/aaaa)
                                </label>
                                <input readonly type="date" name="cessation_date"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('cessation_date') input-error @enderror"
                                    value="{{ old('cessation_date') }}">
                                @error('cessation_date')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de réintégration (jj/mm/aaaa)
                                </label>
                                <input readonly type="date" name="reintegration_date"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('reintegration_date') input-error @enderror"
                                    value="{{ old('reintegration_date') }}">
                                @error('reintegration_date')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Nouveau salaire (Gdes)
                                </label>
                                <input readonly type="number" name="new_salary" step="0.01"
                                    class="bg-gray-100  w-full rounded-md border-gray-300 @error('new_salary') input-error @enderror"
                                    value="{{ old('new_salary') }}">
                                @error('new_salary')
                                    <p class="error-message">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de fin de serv. Ou de carr. (jj/mm/aaaa)
                                </label>
                                <input readonly type="date" name="end_of_service_date"
                                    class="bg-gray-100 w-full rounded-md border-gray-300 @error('end_of_service_date') input-error @enderror"
                                    value="{{ old('end_of_service_date') }}">
                                @error('end_of_service_date')
                                    <p class="error-message">{{ $message }}</p>
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
