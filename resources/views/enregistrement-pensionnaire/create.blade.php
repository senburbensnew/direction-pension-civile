@extends('layouts.main')

@section('content')
<style>
    #enregistrement-pensionnaire-form .input-error {
        @apply border-red-500 focus:border-red-500 focus:ring-red-500;
    }

    #enregistrement-pensionnaire-form .error-message {
        @apply mt-1 text-sm text-red-600;
    }

    .dependent-item {
        @apply p-4 border border-gray-200 rounded-lg mb-4 bg-gray-50;
    }

    .form-section {
        @apply mb-6 p-4 border border-gray-200 rounded-lg;
    }

    .form-group input:valid:not(:placeholder-shown) {
        border-color: #10B981;
    }

    .form-group input:invalid:not(:placeholder-shown) {
        border-color: #EF4444;
    }

    .transition {
        transition: all 0.2s ease-in-out;
    }
</style>

<div id="enregistrement-pensionnaire-form" class="max-w-6xl mx-auto p-6 m-2 bg-white">
    <!-- Breadcrumb -->
    <nav class="text-sm text-gray-600 mb-4">
        <span class="text-gray-800">Pensionnaire</span>
        <span class="mx-2">></span>
        <span class="text-gray-800">Enregistrement Pensionnaire</span>
    </nav>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded mb-6">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('enregistrement-pensionnaire.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-8">
                <!-- Title Section -->
                <div class="order-2 mx-4 md:mx-12 text-center flex-grow">
                    <h1 class="text-xl md:text-2xl font-bold">
                        <span class="underline">Formulaire d'Enregistrement Pensionnaire</span>
                    </h1>
                </div>
            </div>

            <!-- INFORMATIONS DU PENSIONNAIRE -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">


                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- NIF Search -->
<div class="md:col-span-2">
    <div class="flex flex-col sm:flex-row gap-2">
        <!-- Search Input -->
        <div class="flex-1">
            <input type="text" id="search_nif" name="search_nif"
                   placeholder="Entrer NIF ou Code pensionnaire"
                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <!-- Buttons - Icons on mobile, text on larger screens -->
        <div class="flex gap-2">
            <button type="button" id="rechercherPensionnaire" name="rechercherPensionnaire"
                    class="px-3 sm:px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center justify-center gap-1 flex-1 sm:flex-none">
                <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <span class="hidden sm:inline">Rechercher</span>
            </button>
            <button type="button" id="supprimerDemandePension" name="supprimerDemandePension"
                    class="px-3 sm:px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center justify-center gap-1 flex-1 sm:flex-none">
                <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span class="hidden sm:inline">Supprimer</span>
            </button>
        </div>
    </div>
</div>

                    <!-- Photo Upload -->

                    <div class="md:col-span-2">
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-1">
                            Photo d'identité
                        </label>
                        <div class="text-left">
                            {{-- <input type="file" id="photo" name="photo" accept="image/jpeg, image/png, image/gif, image/jpg"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            --}}
                            <x-profile-picture :showLabel="false" />
                            <small class="text-xs text-gray-500 mt-1 block">Formats acceptés : JPEG, PNG, GIF (max 5MB)</small>
                        </div>
                        @error('photo')
                            <p class="error-message mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>
<fieldset class="shadow-md rounded-lg p-6 border mb-6 bg-white">
    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2 bg-white px-4">Informations du Pensionnaire</legend>

    <div class="space-y-6">
        <!-- Section: Informations Personnelles -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-md font-semibold text-blue-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Informations Personnelles
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Code Pensionnaire -->
                <div class="form-group">
                    <label for="code_pensionnaire" class="required block text-sm font-medium text-gray-700 mb-1">
                        Code Pensionnaire
                    </label>
                    <input type="text" id="code_pensionnaire" name="code_pensionnaire"
                           value="{{ old('code_pensionnaire') }}" required
                           placeholder="8-36059"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-50 font-mono @error('code_pensionnaire') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('code_pensionnaire')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- CIN -->
                <div class="form-group">
                    <label for="cin_pensionnaire" class="block text-sm font-medium text-gray-700 mb-1">
                        CIN
                    </label>
                    <input type="text" id="cin_pensionnaire" name="cin_pensionnaire"
                           value="{{ old('cin_pensionnaire') }}"
                           placeholder="Numéro de carte d'identité"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cin_pensionnaire') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('cin_pensionnaire')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nom -->
                <div class="form-group">
                    <label for="nom" class="required block text-sm font-medium text-gray-700 mb-1">
                        Nom
                    </label>
                    <input type="text" id="nom" name="nom"
                           value="{{ old('nom') }}" required
                           placeholder="Antoine"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 uppercase @error('nom') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('nom')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prénom -->
                <div class="form-group">
                    <label for="prenom" class="required block text-sm font-medium text-gray-700 mb-1">
                        Prénom
                    </label>
                    <input type="text" id="prenom" name="prenom"
                           value="{{ old('prenom') }}" required
                           placeholder="WILFRID"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('prenom') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('prenom')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date de Naissance -->
                <div class="form-group">
                    <label for="date_naissance" class="required block text-sm font-medium text-gray-700 mb-1">
                        Date de Naissance
                    </label>
                    <input type="date" id="date_naissance" name="date_naissance"
                           value="{{ old('date_naissance') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_naissance') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('date_naissance')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No Moniteur -->
                <div class="form-group">
                    <label for="no_moniteur" class="block text-sm font-medium text-gray-700 mb-1">
                        No Moniteur
                    </label>
                    <input type="text" id="no_moniteur" name="no_moniteur"
                           value="{{ old('no_moniteur') }}"
                           placeholder="18-B"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono @error('no_moniteur') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('no_moniteur')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Informations de Pension -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <h3 class="text-md font-semibold text-green-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informations de Pension
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Date Liquidation Pension -->
                <div class="form-group">
                    <label for="date_liquidation_pension" class="block text-sm font-medium text-gray-700 mb-1">
                        Date Liquidation Pension
                    </label>
                    <input type="date" id="date_liquidation_pension" name="date_liquidation_pension"
                           value="{{ old('date_liquidation_pension') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_liquidation_pension') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('date_liquidation_pension')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Pension -->
                <div class="form-group">
                    <label for="date_pension" class="required block text-sm font-medium text-gray-700 mb-1">
                        Date Pension
                    </label>
                    <input type="date" id="date_pension" name="date_pension"
                           value="{{ old('date_pension') }}" required
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_pension') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('date_pension')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Statut Pension -->
                <div class="form-group">
                    <label for="statut_pension" class="required block text-sm font-medium text-gray-700 mb-1">
                        Statut Pension
                    </label>
                    <select id="statut_pension" name="statut_pension" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('statut_pension') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="EN PENSION" {{ old('statut_pension', 'EN PENSION') == 'EN PENSION' ? 'selected' : '' }}>EN PENSION</option>
                        <option value="RETRAITE" {{ old('statut_pension') == 'RETRAITE' ? 'selected' : '' }}>RETRAITE</option>
                        <option value="SUSPENDU" {{ old('statut_pension') == 'SUSPENDU' ? 'selected' : '' }}>SUSPENDU</option>
                        <option value="DECEDE" {{ old('statut_pension') == 'DECEDE' ? 'selected' : '' }}>DÉCÉDÉ</option>
                    </select>
                    @error('statut_pension')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mode Paiement -->
                <div class="form-group">
                    <label for="mode_paiement" class="required block text-sm font-medium text-gray-700 mb-1">
                        Mode de Paiement
                    </label>
                    <select id="mode_paiement" name="mode_paiement" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('mode_paiement') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="CHEQUES" {{ old('mode_paiement', 'CHEQUES') == 'CHEQUES' ? 'selected' : '' }}>CHÈQUES</option>
                        <option value="VIREMENT" {{ old('mode_paiement') == 'VIREMENT' ? 'selected' : '' }}>VIREMENT</option>
                        <option value="ESPECES" {{ old('mode_paiement') == 'ESPECES' ? 'selected' : '' }}>ESPÈCES</option>
                        <option value="COMPENSATION" {{ old('mode_paiement') == 'COMPENSATION' ? 'selected' : '' }}>COMPENSATION</option>
                    </select>
                    @error('mode_paiement')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Aval -->
                <div class="form-group">
                    <label for="aval" class="required block text-sm font-medium text-gray-700 mb-1">
                        Aval
                    </label>
                    <div class="relative">
                        <input type="text"  id="aval" name="aval"
                               value="{{ old('aval', '0') }}" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('aval') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    </div>
                    @error('aval')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Montant Pension -->
                <div class="form-group">
                    <label for="montant_pension" class="required block text-sm font-medium text-gray-700 mb-1">
                        Montant Pension (HTG)
                    </label>
                    <div class="relative">
                        <input type="number" step="0.01" id="montant_pension" name="montant_pension"
                               value="{{ old('montant_pension', '13136.53') }}" required
                               class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('montant_pension') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    </div>
                    @error('montant_pension')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Personne de Contact -->
                <div class="form-group">
                    <label for="personne_contact" class="required block text-sm font-medium text-gray-700 mb-1">
                        Personne de Contact
                    </label>
                    <input type="text" id="personne_contact" name="personne_contact"
                           value="{{ old('personne_contact') }}" required
                           placeholder="Nom de la personne à contacter"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('personne_contact') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('personne_contact')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mandataire -->
                <div class="form-group">
                    <label for="mandataire" class="block text-sm font-medium text-gray-700 mb-1">
                        Mandataire
                    </label>
                    <input type="text" id="mandataire" name="mandataire"
                           value="{{ old('mandataire') }}"
                           placeholder="Nom du mandataire"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('mandataire') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('mandataire')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Section: Classification et Localisation -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <h3 class="text-md font-semibold text-purple-800 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Classification et Localisation
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Localité -->
                <div class="form-group">
                    <label for="localite" class="required block text-sm font-medium text-gray-700 mb-1">
                        Localité
                    </label>
                    <select id="localite" name="localite" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('localite') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="GRAND COMMIS" {{ old('localite') == 'GRAND COMMIS' ? 'selected' : '' }}>GC GRAND COMMIS</option>
                        <option value="PE" {{ old('localite') == 'PE' ? 'selected' : '' }}>PE</option>
                        <option value="PP PORT-AU-PRINCE" {{ old('localite') == 'PP PORT-AU-PRINCE' ? 'selected' : '' }}>PP PORT-AU-PRINCE</option>
                        <option value="PP DELMAS" {{ old('localite') == 'PP DELMAS' ? 'selected' : '' }}>PP DELMAS</option>
                        <option value="PP PETION-VILLE" {{ old('localite') == 'PP PETION-VILLE' ? 'selected' : '' }}>PP PETION-VILLE</option>
                        <option value="PP CARREFOUR" {{ old('localite') == 'PP CARREFOUR' ? 'selected' : '' }}>PP CARREFOUR</option>
                        <option value="PP PLAINE" {{ old('localite') == 'PP PLAINE' ? 'selected' : '' }}>PP PLAINE</option>
                        <option value="PP LEOGANE" {{ old('localite') == 'PP LEOGANE' ? 'selected' : '' }}>PP LEOGANE</option>
                        <option value="WI HINCHE" {{ old('localite') == 'WI HINCHE' ? 'selected' : '' }}>WI HINCHE</option>
                        <option value="XA AQUIN" {{ old('localite') == 'XA AQUIN' ? 'selected' : '' }}>XA AQUIN</option>
                        <option value="XC CAYES" {{ old('localite') == 'XC CAYES' ? 'selected' : '' }}>XC CAYES</option>
                        <option value="XF FORT-LIBERTE" {{ old('localite') == 'XF FORT-LIBERTE' ? 'selected' : '' }}>XF FORT-LIBERTE</option>
                        <option value="XG GONAIVES" {{ old('localite') == 'XG GONAIVES' ? 'selected' : '' }}>XG GONAIVES</option>
                        <option value="XH CAP-HAITIEN" {{ old('localite') == 'XH CAP-HAITIEN' ? 'selected' : '' }}>XH CAP-HAITIEN</option>
                        <option value="XJ JEREMIE" {{ old('localite') == 'XJ JEREMIE' ? 'selected' : '' }}>XJ JEREMIE</option>
                        <option value="XL JACMEL" {{ old('localite') == 'XL JACMEL' ? 'selected' : '' }}>XL JACMEL</option>
                        <option value="XM MIRAGOANE" {{ old('localite') == 'XM MIRAGOANE' ? 'selected' : '' }}>XM MIRAGOANE</option>
                        <option value="XP PETIT-GOAVE" {{ old('localite') == 'XP PETIT-GOAVE' ? 'selected' : '' }}>XP PETIT-GOAVE</option>
                        <option value="XS SAINT-MARC" {{ old('localite') == 'XS SAINT-MARC' ? 'selected' : '' }}>XS SAINT-MARC</option>
                        <option value="XX PORT-DE-PAIX" {{ old('localite') == 'XX PORT-DE-PAIX' ? 'selected' : '' }}>XX PORT-DE-PAIX</option>
                        <option value="PX PENSION SPECIALE" {{ old('localite') == 'PX PENSION SPECIALE' ? 'selected' : '' }}>PX PENSION SPECIALE</option>
                        <option value="CANADA" {{ old('localite') == 'CANADA' ? 'selected' : '' }}>CANADA</option>
                        <option value="USA" {{ old('localite') == 'USA' ? 'selected' : '' }}>USA</option>
                        <option value="FRANCE" {{ old('localite') == 'FRANCE' ? 'selected' : '' }}>FRANCE</option>
                        <option value="AUTRES" {{ old('localite') == 'AUTRES' ? 'selected' : '' }}>AUTRES</option>
                    </select>
                    @error('localite')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Statut Matrimonial -->
                <div class="form-group">
                    <label for="statut_matrimonial" class="required block text-sm font-medium text-gray-700 mb-1">
                        Statut Matrimonial
                    </label>
                    <select id="statut_matrimonial" name="statut_matrimonial" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('statut_matrimonial') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="Marié(e)" {{ old('statut_matrimonial') == 'Marié(e)' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="Célibataire" {{ old('statut_matrimonial') == 'Célibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="Veuf/Veuve" {{ old('statut_matrimonial') == 'Veuf/Veuve' ? 'selected' : '' }}>Veuf/Veuve</option>
                        <option value="Concubinage" {{ old('statut_matrimonial') == 'Concubinage' ? 'selected' : '' }}>Concubinage</option>
                        <option value="Séparé(e)" {{ old('statut_matrimonial') == 'Séparé(e)' ? 'selected' : '' }}>Séparé(e)</option>
                    </select>
                    @error('statut_matrimonial')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catégorie de Pension -->
                <div class="form-group">
                    <label for="cat_pension" class="block text-sm font-medium text-gray-700 mb-1">
                        Catégorie de Pension
                    </label>
                    <select id="cat_pension" name="cat_pension"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cat_pension') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="Pension Civile" {{ old('cat_pension') == 'Pension Civile' ? 'selected' : '' }}>Pension Civile</option>
                        <option value="Pension Militaire" {{ old('cat_pension') == 'Pension Militaire' ? 'selected' : '' }}>Pension Militaire</option>
                        <option value="Pension BNDAI" {{ old('cat_pension') == 'Pension BNDAI' ? 'selected' : '' }}>Pension BNDAI</option>
                        <option value="Pension Minoterie" {{ old('cat_pension') == 'Pension Minoterie' ? 'selected' : '' }}>Pension Minoterie</option>
                        <option value="Selection Nationale" {{ old('cat_pension') == 'Selection Nationale' ? 'selected' : '' }}>Selection Nationale</option>
                    </select>
                    @error('cat_pension')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nature Pension -->
                <div class="form-group">
                    <label for="type_pension" class="required block text-sm font-medium text-gray-700 mb-1">
                        Nature Pension
                    </label>
                    <select id="type_pension" name="type_pension" required
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('type_pension') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="25 ANS DE SERVICE" {{ old('type_pension') == '25 ANS DE SERVICE' ? 'selected' : '' }}>25 ANS DE SERVICE</option>
                        <option value="30 ANS DE SERVICE" {{ old('type_pension') == '30 ANS DE SERVICE' ? 'selected' : '' }}>30 ANS DE SERVICE</option>
                        <option value="10 ANS DE SERVICE/INFIRME" {{ old('type_pension') == '10 ANS DE SERVICE/INFIRME' ? 'selected' : '' }}>10 ANS DE SERVICE/INFIRME</option>
                        <option value="REVERSIBILITE / VEUF(VE)" {{ old('type_pension') == 'REVERSIBILITE / VEUF(VE)' ? 'selected' : '' }}>REVERSIBILITE / VEUF(VE)</option>
                        <option value="REVERSIBILITE / MINEUR" {{ old('type_pension') == 'REVERSIBILITE / MINEUR' ? 'selected' : '' }}>REVERSIBILITE / MINEUR</option>
                        <option value="REVERSIBILITE / MAJEUR" {{ old('type_pension') == 'REVERSIBILITE / MAJEUR' ? 'selected' : '' }}>REVERSIBILITE / MAJEUR</option>
                        <option value="REVERSIBILITE / PERE" {{ old('type_pension') == 'REVERSIBILITE / PERE' ? 'selected' : '' }}>REVERSIBILITE / PERE</option>
                        <option value="REVERSIBILITE / MERE" {{ old('type_pension') == 'REVERSIBILITE / MERE' ? 'selected' : '' }}>REVERSIBILITE / MERE</option>
                        <option value="40 ANS DE SERVICE" {{ old('type_pension') == '40 ANS DE SERVICE' ? 'selected' : '' }}>40 ANS DE SERVICE</option>
                        <option value="PENSION SPECIALE RETRAITE ANTICIPEE" {{ old('type_pension') == 'PENSION SPECIALE RETRAITE ANTICIPEE' ? 'selected' : '' }}>PENSION SPECIALE RETRAITE ANTICIPEE</option>
                        <option value="DECEDE(E)" {{ old('type_pension') == 'DECEDE(E)' ? 'selected' : '' }}>DECEDE(E)</option>
                        <option value="EN FONCTION" {{ old('type_pension') == 'EN FONCTION' ? 'selected' : '' }}>EN FONCTION</option>
                        <option value="HANDICAPE(E)" {{ old('type_pension') == 'HANDICAPE(E)' ? 'selected' : '' }}>HANDICAPE(E)</option>
                        <option value="PERTE DE DROIT" {{ old('type_pension') == 'PERTE DE DROIT' ? 'selected' : '' }}>PERTE DE DROIT</option>
                        <option value="PENSION SPECIALE" {{ old('type_pension') == 'PENSION SPECIALE' ? 'selected' : '' }}>PENSION SPECIALE</option>
                        <option value="MONTANT TRANSFERE" {{ old('type_pension') == 'MONTANT TRANSFERE' ? 'selected' : '' }}>MONTANT TRANSFERE</option>
                        <option value="20 ANS DE SERVICE" {{ old('type_pension') == '20 ANS DE SERVICE' ? 'selected' : '' }}>20 ANS DE SERVICE</option>
                    </select>
                    @error('type_pension')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Plan Assurance -->
                <div class="form-group">
                    <label for="plan_assurance" class="block text-sm font-medium text-gray-700 mb-1">
                        Plan Assurance
                    </label>
                    <select id="plan_assurance" name="plan_assurance"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('plan_assurance') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="Plan 1" {{ old('plan_assurance') == 'Plan 1' ? 'selected' : '' }}>Plan 1</option>
                        <option value="Plan 2" {{ old('plan_assurance') == 'Plan 2' ? 'selected' : '' }}>Plan 2</option>
                        <option value="Plan 3" {{ old('plan_assurance') == 'Plan 3' ? 'selected' : '' }}>Plan 3</option>
                    </select>
                    @error('plan_assurance')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</fieldset>
<!-- PERSONNES À CHARGE -->
<fieldset class="shadow-md rounded-lg p-6 border mb-6 bg-white">
    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2 bg-white px-4">Dépendants</legend>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="text-sm text-blue-800 font-medium">Informations sur les personnes dépendantes</p>
                <p class="text-xs text-blue-600 mt-1">
                    Ajoutez les personnes dépendantes du pensionnaire (conjoint, enfants, parents, etc.).
                    Ces informations sont importantes pour les droits aux assurances et prestations.
                </p>
            </div>
        </div>
    </div>

    <div id="dependants-container">
        <!-- Existing Dependant Item -->
        <div class="dependent-item bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-semibold text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personne dépendante #1
                </h4>
                <button type="button" onclick="removeDependant(this)"
                        class="text-red-600 hover:text-red-800 transition-colors duration-200 flex items-center text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- NIF -->
                <div class="form-group">
                    <label for="dependant_nif_0" class="block text-sm font-medium text-gray-700 mb-1">
                        NIF
                    </label>
                    <input type="text" id="dependant_nif_0" name="dependant_nif[]"
                           value="{{ old('dependant_nif.0') }}"
                           placeholder="NIF de la personne"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dependant_nif.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('dependant_nif.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Nom -->
                <div class="form-group">
                    <label for="dependant_nom_0" class="block text-sm font-medium text-gray-700 mb-1">
                        Nom
                    </label>
                    <input type="text" id="dependant_nom_0" name="dependant_nom[]"
                           value="{{ old('dependant_nom.0') }}"
                           placeholder="Nom de famille"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dependant_nom.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('dependant_nom.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Prénom -->
                <div class="form-group">
                    <label for="dependant_prenom_0" class="block text-sm font-medium text-gray-700 mb-1">
                        Prénom
                    </label>
                    <input type="text" id="dependant_prenom_0" name="dependant_prenom[]"
                           value="{{ old('dependant_prenom.0') }}"
                           placeholder="Prénom"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dependant_prenom.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('dependant_prenom.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Lien de parenté -->
                <div class="form-group">
                    <label for="dependant_lien_0" class="block text-sm font-medium text-gray-700 mb-1">
                        Lien de parenté
                    </label>
                    <select id="dependant_lien_0" name="dependant_lien[]"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dependant_lien.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner</option>
                        <option value="Conjoint" {{ old('dependant_lien.0') == 'Conjoint' ? 'selected' : '' }}>Conjoint(e)</option>
                        <option value="Enfant" {{ old('dependant_lien.0') == 'Enfant' ? 'selected' : '' }}>Enfant</option>
                        <option value="Pere" {{ old('dependant_lien.0') == 'Pere' ? 'selected' : '' }}>Père</option>
                        <option value="Mere" {{ old('dependant_lien.0') == 'Mere' ? 'selected' : '' }}>Mère</option>
                        <option value="Beau-pere" {{ old('dependant_lien.0') == 'Beau-pere' ? 'selected' : '' }}>Beau-père</option>
                        <option value="Belle-mere" {{ old('dependant_lien.0') == 'Belle-mere' ? 'selected' : '' }}>Belle-mère</option>
                        <option value="Grand-pere" {{ old('dependant_lien.0') == 'Grand-pere' ? 'selected' : '' }}>Grand-père</option>
                        <option value="Grand-mere" {{ old('dependant_lien.0') == 'Grand-mere' ? 'selected' : '' }}>Grand-mère</option>
                        <option value="Frere" {{ old('dependant_lien.0') == 'Frere' ? 'selected' : '' }}>Frère</option>
                        <option value="Soeur" {{ old('dependant_lien.0') == 'Soeur' ? 'selected' : '' }}>Sœur</option>
                        <option value="Neveu" {{ old('dependant_lien.0') == 'Neveu' ? 'selected' : '' }}>Neveu</option>
                        <option value="Niece" {{ old('dependant_lien.0') == 'Niece' ? 'selected' : '' }}>Nièce</option>
                        <option value="Oncle" {{ old('dependant_lien.0') == 'Oncle' ? 'selected' : '' }}>Oncle</option>
                        <option value="Tante" {{ old('dependant_lien.0') == 'Tante' ? 'selected' : '' }}>Tante</option>
                        <option value="Cousin" {{ old('dependant_lien.0') == 'Cousin' ? 'selected' : '' }}>Cousin</option>
                        <option value="Cousine" {{ old('dependant_lien.0') == 'Cousine' ? 'selected' : '' }}>Cousine</option>
                        <option value="Autre" {{ old('dependant_lien.0') == 'Autre' ? 'selected' : '' }}>Autre</option>
                    </select>
                    @error('dependant_lien.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Assurance -->
                <div class="form-group">
                    <label for="assurance_0" class="block text-sm font-medium text-gray-700 mb-1">
                        Couverture d'Assurance
                    </label>
                    <select id="assurance_0" name="assurance[]"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('assurance.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                        <option value="">Sélectionner (Optionnel)</option>
                        <option value="Oui" {{ old('assurance.0') == 'Oui' ? 'selected' : '' }}>Oui - Couvert par l'assurance</option>
                        <option value="Non" {{ old('assurance.0') == 'Non' ? 'selected' : '' }}>Non - Non couvert</option>
                    </select>
                    @error('assurance.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date de naissance -->
                <div class="form-group">
                    <label for="dependant_naissance_0" class="block text-sm font-medium text-gray-700 mb-1">
                        Date de Naissance
                    </label>
                    <input type="date" id="dependant_naissance_0" name="dependant_naissance[]"
                           value="{{ old('dependant_naissance.0') }}"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dependant_naissance.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">
                    @error('dependant_naissance.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="form-group">
                    <label for="dependant_notes_0" class="block text-sm font-medium text-gray-700 mb-1">
                        Notes supplémentaires (Optionnel)
                    </label>
                    <textarea id="dependant_notes_0" name="dependant_notes[]" rows="2"
                              placeholder="Informations complémentaires sur cette personne..."
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('dependant_notes.0') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror">{{ old('dependant_notes.0') }}</textarea>
                    @error('dependant_notes.0')
                        <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-start items-center mt-6">
        <button type="button" onclick="addDependant()"
                class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 flex items-center font-medium">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Ajouter une personne à charge
        </button>

        <div class="text-sm text-gray-500 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span id="dependant-count">1</span> personne(s) à charge ajoutée(s)
        </div>
    </div>
</fieldset>


<fieldset class="shadow-md rounded-lg p-6 border mb-6 bg-white">
    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2 bg-white px-4">Coordonnées</legend>

    <div class="space-y-6">
        <!-- Adresse Section -->
        <div class="form-group">
            <label for="adresse" class="required block text-sm font-medium text-gray-700 mb-2">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Adresse Complète
                </div>
            </label>
            <textarea
                id="adresse"
                name="adresse"
                rows="3"
                required
                placeholder="Entrez l'adresse complète du pensionnaire..."
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200 @error('adresse') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                oninput="updateCharacterCount('adresse', 'adresse-count')"
            >{{ old('adresse') }}</textarea>

            <div class="flex justify-between items-center mt-1">
                <small class="text-xs text-gray-500">
                    <span id="adresse-count">0</span> caractères
                </small>
                @error('adresse')
                    <p class="error-message text-sm text-red-600">{{ $message }}</p>
                @else
                    <small class="text-xs text-gray-500">
                        Champ obligatoire
                    </small>
                @enderror
            </div>
        </div>

        <!-- Téléphone et Email en ligne sur desktop -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Téléphone -->
            <div class="form-group">
                <label for="telephone" class="required block text-sm font-medium text-gray-700 mb-2">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        Téléphone
                    </div>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">+509</span>
                    </div>
                    <input
                        type="tel"
                        id="telephone"
                        name="telephone"
                        pattern="[0-9]{8}"
                        required
                        placeholder=""
                        value="{{ old('telephone') }}"
                        class="w-full pl-16 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200 @error('telephone') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                        oninput="formatPhoneNumber(this)"
                        maxlength="8"
                    />
                </div>
                <div class="flex justify-between items-center mt-1">
                    <small class="text-xs text-gray-500">
                        Format: 8 chiffres (ex: 36451234)
                    </small>
                    @error('telephone')
                        <p class="error-message text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Adresse Email
                    </div>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="pensionnaire@example.com"
                    value="{{ old('email') }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                    onblur="validateEmail(this)"
                />
                <div class="mt-1">
                    @error('email')
                        <p class="error-message text-sm text-red-600">{{ $message }}</p>
                    @else
                        <small class="text-xs text-gray-500">
                            Optionnel - Format: exemple@domain.com
                        </small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Informations supplémentaires -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm text-blue-800 font-medium">Informations de contact</p>
                    <p class="text-xs text-blue-600 mt-1">
                        Assurez-vous que les coordonnées sont à jour pour faciliter la communication concernant les paiements et les informations importantes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</fieldset>

<!-- PRISE DE RENDEZ-VOUS -->
<fieldset class="shadow-md rounded-lg p-6 border mb-6 bg-white">
    <legend class="text-lg font-semibold text-gray-800 mb-4 px-2 bg-white px-4">Prise de Rendez-vous</legend>

    <div class="space-y-6">
        <!-- Information Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <div>
                    <p class="text-sm text-blue-800 font-medium">Planification d'appel</p>
                    <p class="text-xs text-blue-600 mt-1">
                        Choisissez une date et une heure pour que nous puissions vous appeler afin de finaliser votre dossier.
                        Nos horaires d'appel sont du lundi au vendredi de 8h00 à 16h00.
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Date du Rendez-vous -->
            <div class="form-group">
                <label for="rendezvous_date" class="required block text-sm font-medium text-gray-700 mb-2">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Date souhaitée
                    </div>
                </label>
                <input
                    type="date"
                    id="rendezvous_date"
                    name="rendezvous_date"
                    required
                    min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200 @error('rendezvous_date') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                    onchange="validateRendezvousDate()"
                />
                <div class="mt-1">
                    @error('rendezvous_date')
                        <p class="error-message text-sm text-red-600">{{ $message }}</p>
                    @else
                        <small class="text-xs text-gray-500">
                            Sélectionnez une date à partir de demain
                        </small>
                    @enderror
                </div>
            </div>

            <!-- Heure du Rendez-vous -->
            <div class="form-group">
                <label for="rendezvous_time" class="required block text-sm font-medium text-gray-700 mb-2">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Heure souhaitée
                    </div>
                </label>
                <select
                    id="rendezvous_time"
                    name="rendezvous_time"
                    required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200 @error('rendezvous_time') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                >
                    <option value="">Sélectionnez une heure</option>
                    <option value="08:00">08:00 - 08:30</option>
                    <option value="08:30">08:30 - 09:00</option>
                    <option value="09:00">09:00 - 09:30</option>
                    <option value="09:30">09:30 - 10:00</option>
                    <option value="10:00">10:00 - 10:30</option>
                    <option value="10:30">10:30 - 11:00</option>
                    <option value="11:00">11:00 - 11:30</option>
                    <option value="11:30">11:30 - 12:00</option>
                    <option value="13:00">13:00 - 13:30</option>
                    <option value="13:30">13:30 - 14:00</option>
                    <option value="14:00">14:00 - 14:30</option>
                    <option value="14:30">14:30 - 15:00</option>
                    <option value="15:00">15:00 - 15:30</option>
                    <option value="15:30">15:30 - 16:00</option>
                </select>
                <div class="mt-1">
                    @error('rendezvous_time')
                        <p class="error-message text-sm text-red-600">{{ $message }}</p>
                    @else
                        <small class="text-xs text-gray-500">
                            Horaires disponibles: 8h00 - 12h00 et 13h00 - 16h00
                        </small>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Informations complémentaires -->
{{--         <div class="form-group">
            <label for="rendezvous_notes" class="block text-sm font-medium text-gray-700 mb-2">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Informations complémentaires (Optionnel)
                </div>
            </label>
            <textarea
                id="rendezvous_notes"
                name="rendezvous_notes"
                rows="3"
                placeholder="Précisez toute information utile pour l'appel (préférence de langue, sujet spécifique à aborder, etc.)"
                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 transition duration-200 @error('rendezvous_notes') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
            >{{ old('rendezvous_notes') }}</textarea>
            @error('rendezvous_notes')
                <p class="error-message text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div> --}}

        <!-- Confirmation de disponibilité -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm text-green-800 font-medium">Confirmation de rendez-vous</p>
                    <p class="text-xs text-green-600 mt-1">
                        Vous recevrez un SMS ou un email de confirmation dans les 24 heures suivant la soumission de votre demande.
                        En cas d'indisponibilité à la date et heure choisies, nous vous contacterons pour proposer une alternative.
                    </p>
                </div>
            </div>
        </div>
    </div>
</fieldset>

            <!-- DÉCLARATION ET SOUMISSION -->
            <div class="form-section">
                <div class="form-group mb-6">
                    <div class="flex items-center">
                        <input type="checkbox" id="declaration" name="declaration" required
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 @error('declaration') input-error @enderror">
                        <label for="declaration" class="ml-2 block text-sm text-gray-900 required">
                            Je certifie l'exactitude des informations fournies
                        </label>
                    </div>
                    @error('declaration')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4 justify-start">
                    <button type="reset"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 transition-colors duration-200">
                        Effacer
                    </button>
                    <button type="submit" id="enregistrerDemandePension" name="enregistrerDemandePension"
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    let dependantCount = 1;

    function addDependant() {
        dependantCount++;
        const container = document.getElementById("dependants-container");
        const newItem = document.createElement("div");
        newItem.className = "dependent-item bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4";
        newItem.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-semibold text-gray-700 flex items-center">
                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Personne dépendante #${dependantCount}
                </h4>
                <button type="button" onclick="removeDependant(this)"
                        class="text-red-600 hover:text-red-800 transition-colors duration-200 flex items-center text-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Supprimer
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- NIF -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        NIF
                    </label>
                    <input type="text" name="dependant_nif[]"
                           placeholder="NIF de la personne"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Nom -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nom
                    </label>
                    <input type="text" name="dependant_nom[]"
                           placeholder="Nom de famille"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Prénom -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Prénom
                    </label>
                    <input type="text" name="dependant_prenom[]"
                           placeholder="Prénom"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Lien de parenté -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Lien de parenté
                    </label>
                    <select name="dependant_lien[]"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner</option>
                        <option value="Conjoint">Conjoint(e)</option>
                        <option value="Enfant">Enfant</option>
                        <option value="Pere">Père</option>
                        <option value="Mere">Mère</option>
                        <option value="Beau-pere">Beau-père</option>
                        <option value="Belle-mere">Belle-mère</option>
                        <option value="Grand-pere">Grand-père</option>
                        <option value="Grand-mere">Grand-mère</option>
                        <option value="Frere">Frère</option>
                        <option value="Soeur">Sœur</option>
                        <option value="Neveu">Neveu</option>
                        <option value="Niece">Nièce</option>
                        <option value="Oncle">Oncle</option>
                        <option value="Tante">Tante</option>
                        <option value="Cousin">Cousin</option>
                        <option value="Cousine">Cousine</option>
                        <option value="Autre">Autre</option>
                    </select>
                </div>

                <!-- Assurance -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Couverture d'Assurance
                    </label>
                    <select name="assurance[]"
                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Sélectionner (Optionnel)</option>
                        <option value="Oui">Oui - Couvert par l'assurance</option>
                        <option value="Non">Non - Non couvert</option>
                    </select>
                </div>

                <!-- Date de naissance -->
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Date de Naissance
                    </label>
                    <input type="date" name="dependant_naissance[]"
                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>

            <!-- Additional Information -->
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="form-group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Notes supplémentaires (Optionnel)
                    </label>
                    <textarea name="dependant_notes[]" rows="2"
                              placeholder="Informations complémentaires sur cette personne..."
                              class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newItem);
        updateDependantCount();
    }

    function removeDependant(button) {
        const item = button.closest('.dependent-item');
        if (item) {
            item.remove();
            dependantCount = Math.max(1, dependantCount - 1); // Never go below 1
            updateDependantCount();
            renumberDependants();
        }
    }

    function updateDependantCount() {
        const countElement = document.getElementById('dependant-count');
        if (countElement) {
            const currentCount = document.querySelectorAll('.dependent-item').length;
            countElement.textContent = currentCount;
        }
    }

    function renumberDependants() {
        const dependants = document.querySelectorAll('.dependent-item');
        dependants.forEach((item, index) => {
            const title = item.querySelector('h4');
            if (title) {
                title.textContent = `Personne à Charge #${index + 1}`;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize dependant count
        updateDependantCount();

        // Auto-completion for nom_complet field
        const nomField = document.querySelector('input[name="nom"]');
        const prenomField = document.querySelector('input[name="prenom"]');
        const nomCompletField = document.querySelector('input[name="nom_complet"]');

        function updateNomComplet() {
            const nom = nomField?.value || '';
            const prenom = prenomField?.value || '';

            if (nomCompletField) {
                nomCompletField.value = `${nom} ${prenom}`.trim();
            }
        }

        if (nomField && prenomField && nomCompletField) {
            nomField.addEventListener('input', updateNomComplet);
            prenomField.addEventListener('input', updateNomComplet);
        }

        // Real-time validation for required fields
        const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                if (!this.value) {
                    this.classList.add('border-red-500');
                } else {
                    this.classList.remove('border-red-500');
                }
            });
        });

        // Search pensionnaire functionality
        const searchButton = document.getElementById('rechercherPensionnaire');
        const searchInput = document.getElementById('search_nif');

        if (searchButton && searchInput) {
            searchButton.addEventListener('click', function() {
                const searchValue = searchInput.value.trim();
                if (searchValue) {
                    // In a real application, you would make an AJAX request here
                    fetch(`/api/pensionnaires/search?q=${encodeURIComponent(searchValue)}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Populate form fields with returned data
                                populateForm(data.pensionnaire);
                            } else {
                                alert('Pensionnaire non trouvé');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Erreur lors de la recherche');
                        });
                } else {
                    alert('Veuillez entrer un NIF ou code pensionnaire');
                }
            });
        }

        // Delete enregistrement-pensionnaire functionality
        const deleteButton = document.getElementById('supprimerDemandePension');
        if (deleteButton) {
            deleteButton.addEventListener('click', function() {
                if (confirm('Êtes-vous sûr de vouloir supprimer cette formalité ?')) {
                    // Implementation for delete action
                    const searchValue = searchInput.value.trim();
                    if (searchValue) {
                        // Make DELETE request
                        console.log('Delete formalité for:', searchValue);
                    }
                }
            });
        }

        // File upload validation
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                    const maxSize = 5 * 1024 * 1024; // 5MB

                    if (!validTypes.includes(file.type)) {
                        alert('Format de fichier non supporté. Veuillez utiliser JPEG, PNG ou GIF.');
                        this.value = '';
                        return;
                    }

                    if (file.size > maxSize) {
                        alert('Le fichier est trop volumineux. Taille maximale: 5MB.');
                        this.value = '';
                        return;
                    }
                }
            });
        }

        // Form submission validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const declaration = document.getElementById('declaration');
                if (!declaration.checked) {
                    e.preventDefault();
                    alert('Veuillez certifier l\'exactitude des informations fournies');
                    declaration.focus();
                }
            });
        }

        // Character counter for address
        updateCharacterCount('adresse', 'adresse-count');

        // Format existing phone number if any
        const phoneInput = document.getElementById('telephone');
        if (phoneInput && phoneInput.value) {
            formatPhoneNumber(phoneInput);
        }

        // Validate existing email if any
        const emailInput = document.getElementById('email');
        if (emailInput && emailInput.value) {
            validateEmail(emailInput);
        }

        // Convert text dates to proper date inputs on page load
        const dateFields = {
            'date_naissance': '07-SEP-51',
            'date_liquidation_pension': '13-MAR-25',
            'date_pension': '31-DEC-24'
        };

        Object.keys(dateFields).forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !field.value) {
                const convertedDate = convertTextToDate(dateFields[fieldId]);
                if (convertedDate) {
                    field.value = convertedDate;
                }
            }
        });

        // Auto-format currency input
        const montantInput = document.getElementById('montant_pension');
        if (montantInput) {
            montantInput.addEventListener('blur', function() {
                if (this.value) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });
        }

        // Auto-uppercase for last name
        const nomInput = document.getElementById('nom');
        if (nomInput) {
            nomInput.addEventListener('blur', function() {
                this.value = this.value.toUpperCase();
            });
        }
    });

    function populateForm(pensionnaire) {
        // Helper function to populate form fields from search results
        const fields = ['nom', 'prenom', 'nom_complet', 'date_naissance', 'lieu_naissance', 'sexe'];
        fields.forEach(field => {
            const element = document.querySelector(`[name="${field}"]`);
            if (element && pensionnaire[field]) {
                element.value = pensionnaire[field];
            }
        });
    }

    // Character counter for address
    function updateCharacterCount(textareaId, countId) {
        const textarea = document.getElementById(textareaId);
        const countElement = document.getElementById(countId);
        if (textarea && countElement) {
            countElement.textContent = textarea.value.length;
        }
    }

    // Phone number formatting
    function formatPhoneNumber(input) {
        // Remove non-numeric characters
        let value = input.value.replace(/\D/g, '');

        // Limit to 8 digits
        if (value.length > 8) {
            value = value.substring(0, 8);
        }

        input.value = value;

        // Add visual validation
        if (value.length === 8) {
            input.classList.remove('border-red-500');
            input.classList.add('border-green-500');
        } else {
            input.classList.remove('border-green-500');
        }
    }

    // Email validation
    function validateEmail(input) {
        const email = input.value;
        if (email === '') return; // Skip validation if empty (optional field)

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (emailRegex.test(email)) {
            input.classList.remove('border-red-500');
            input.classList.add('border-green-500');
        } else {
            input.classList.remove('border-green-500');
            input.classList.add('border-red-500');
        }
    }

    // Date conversion helper
    function convertTextToDate(textDate) {
        if (!textDate) return '';

        const parts = textDate.split('-');
        if (parts.length === 3) {
            const day = parts[0];
            const month = getMonthNumber(parts[1]);
            const year = parts[2].length === 2 ? '19' + parts[2] : parts[2];

            if (month && day && year) {
                return `${year}-${month}-${day.padStart(2, '0')}`;
            }
        }
        return '';
    }

    function getMonthNumber(monthStr) {
        const months = {
            'JAN': '01', 'FEB': '02', 'MAR': '03', 'APR': '04', 'MAY': '05', 'JUN': '06',
            'JUL': '07', 'AUG': '08', 'SEP': '09', 'OCT': '10', 'NOV': '11', 'DEC': '12'
        };
        return months[monthStr.toUpperCase()] || null;
    }
</script>
@endsection
