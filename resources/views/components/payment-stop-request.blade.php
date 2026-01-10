@extends('layouts.main')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="bg-white shadow-md rounded-lg p-8">

        {{-- Titre --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold uppercase">
                Formulaire de Demande de Cessation pour motif de Contrat
            </h1>
            <p class="font-semibold mt-2">Direction de la Pension Civile</p>
            <p class="text-gray-600">Service de Comptabilité</p>
        </div>

        {{-- Message succès --}}
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('demandes.arret-paiement.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Exercice / Dates --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Informations Générales</legend>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>Exercice *</label>
                        <input type="text" name="exercice" placeholder="20.. / 20.."
                               value="{{ old('exercice') }}"
                               class="input mt-1 w-full rounded-md border @error('exercice') border-red-500 @enderror">
                        @error('exercice') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>À partir du mois de *</label>
                        <input type="month" name="mois_debut"
                               value="{{ old('mois_debut') }}"
                               class="input mt-1 w-full rounded-md border @error('mois_debut') border-red-500 @enderror">
                        @error('mois_debut') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Date de la demande *</label>
                        <input type="date" name="date_demande"
                               value="{{ old('date_demande') }}"
                               class="input mt-1 w-full rounded-md border @error('date_demande') border-red-500 @enderror">
                        @error('date_demande') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            {{-- Régime de pension --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Régime de Pension</legend>

                <div class="flex gap-8 mt-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="regime_pension" value="civile"
                               {{ old('regime_pension') === 'civile' ? 'checked' : '' }}>
                        Civile
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio" name="regime_pension" value="militaire"
                               {{ old('regime_pension') === 'militaire' ? 'checked' : '' }}>
                        Militaire
                    </label>
                </div>

                @error('regime_pension')
                    <p class="error-message mt-2">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- Informations du pensionné --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Informations du Pensionné</legend>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>Code Pension *</label>
                        <input type="text" name="code_pension"
                               value="{{ old('code_pension') }}"
                               class="input mt-1 w-full rounded-md border @error('code_pension') border-red-500 @enderror">
                        @error('code_pension') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Montant (Gdes) *</label>
                        <input type="number" name="montant"
                               value="{{ old('montant') }}"
                               class="input mt-1 w-full rounded-md border @error('montant') border-red-500 @enderror">
                        @error('montant') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>Nom *</label>
                        <input type="text" name="nom"
                               value="{{ old('nom') }}"
                               class="input mt-1 w-full rounded-md border  @error('nom') border-red-500 @enderror">
                        @error('nom') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Prénom *</label>
                        <input type="text" name="prenom"
                               value="{{ old('prenom') }}"
                               class="input mt-1 w-full rounded-md border  @error('prenom') border-red-500 @enderror">
                        @error('prenom') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Nom de jeune fille</label>
                        <input type="text" name="nom_jeune_fille"
                               value="{{ old('nom_jeune_fille') }}"
                               class="input mt-1 w-full rounded-md border  @error('nom_jeune_fille') border-red-500 @enderror">
                        @error('nom_jeune_fille') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>NIF *</label>
                        <input type="text" name="nif"
                               value="{{ old('nif') }}"
                               class="input mt-1 w-full rounded-md border  @error('nif') border-red-500 @enderror">
                        @error('nif') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>NINU</label>
                        <input type="text" name="ninu"
                               value="{{ old('ninu') }}"
                               class="input mt-1 w-full rounded-md border  @error('ninu') border-red-500 @enderror">
                        @error('ninu') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Téléphone (WhatsApp)</label>
                        <input type="text" name="telephone"
                               value="{{ old('telephone') }}"
                               class="input mt-1 w-full rounded-md border  @error('telephone') border-red-500 @enderror">
                        @error('telephone') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label>Adresse</label>
                        <input type="text" name="adresse"
                               value="{{ old('adresse') }}"
                               class="input mt-1 w-full rounded-md border  @error('adresse') border-red-500 @enderror">
                        @error('adresse') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Courriel</label>
                        <input type="email" name="email"
                               value="{{ old('email') }}"
                               class="input mt-1 w-full rounded-md border  @error('email') border-red-500 @enderror">
                         @error('email') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            {{-- Période de cessation --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Période de Cessation</legend>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label>De *</label>
                        <input type="date" name="periode_debut"
                               value="{{ old('periode_debut') }}"
                               class="input mt-1 w-full rounded-md border  @error('periode_debut') border-red-500 @enderror">
                        @error('periode_debut') <p class="error-message">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>À *</label>
                        <input type="date" name="periode_fin"
                               value="{{ old('periode_fin') }}"
                               class="input mt-1 w-full rounded-md border  @error('periode_fin') border-red-500 @enderror">
                        @error('periode_fin') <p class="error-message">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            {{-- Pièces justificatives --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">
                    Pièces justificatives
                </legend>

                <div class="mt-4">
                    <label class="block mb-2 font-medium">
                        Télécharger les documents justificatifs *
                    </label>

                    <input
                        type="file"
                        name="pieces[]"
                        multiple
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-700
                            file:mr-4 file:py-2 file:px-4
                            file:rounded-md file:border-0
                            file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700
                            hover:file:bg-blue-100
                            @error('pieces') border-red-500 @enderror
                            @error('pieces.*') border-red-500 @enderror">

                    <p class="text-sm text-gray-500 mt-2">
                        Formats acceptés : PDF, JPG, PNG — plusieurs fichiers autorisés.
                    </p>

                    @error('pieces')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    @error('pieces.*')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <!-- DECLARATION -->
            <fieldset class="mb-6 p-5 border rounded-lg">
                <label class="flex items-start">
                    <input type="checkbox" name="consentement" value="1"
                           {{ old('consentement') ? 'checked' : '' }}>
                    <span class="ml-2 text-sm">Je certifie l’exactitude des informations</span>
                </label>
                @error('consentement')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- Bouton --}}
            <div class="text-right mt-6">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Soumettre
                </button>
            </div>

            {{-- NB --}}
            <p class="text-sm text-gray-600 mt-4">
                <strong>NB :</strong> Prière de joindre à ce formulaire toutes les pièces justifiant votre requête.
            </p>

        </form>
    </div>
</div>
@endsection
