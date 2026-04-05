@props(['pensionCategories', 'demande' => null, 'isDemandeReadyForSubmission' => false])
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

        @if ($demande)
            <div class="mb-6 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded text-sm">
                Brouillon en cours — dernière sauvegarde {{ $demande->updated_at->diffForHumans() }}
            </div>
        @endif

        <form action="{{ route('demandes.arret-paiement.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="arret-paiement-form">
            @csrf
            <input type="hidden" name="demande_id" value="{{ $demande?->id }}">
            <input type="hidden" name="action" id="action-input" value="draft">

            {{-- Titre personnalisé --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Titre personnalisé <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $demande?->title ?? '') }}"
                    placeholder="ex : Arrêt de paiement — 2026"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            {{-- Exercice / Dates --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Informations Générales</legend>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>Exercice *</label>
                        <input type="text" name="exercice" placeholder="20.. / 20.."
                               value="{{ old('exercice', $demande?->data['exercice'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border @error('exercice') border-red-500 @enderror">
                        @error('exercice') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>À partir du mois de *</label>
                        <input type="month" name="mois_debut"
                               value="{{ old('mois_debut', $demande?->data['mois_debut'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border @error('mois_debut') border-red-500 @enderror">
                        @error('mois_debut') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Date de la demande *</label>
                        <input type="date" name="date_demande"
                               value="{{ old('date_demande', $demande?->data['date_demande'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border @error('date_demande') border-red-500 @enderror">
                        @error('date_demande') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </fieldset>

            {{-- Régime de pension --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Régime de Pension</legend>

                <div class="flex gap-8 mt-4">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="regime_pension" value="civile"
                               {{ old('regime_pension', $demande?->data['regime_pension'] ?? '') === 'civile' ? 'checked' : '' }}>
                        Civile
                    </label>

                    <label class="flex items-center gap-2">
                        <input type="radio" name="regime_pension" value="militaire"
                               {{ old('regime_pension', $demande?->data['regime_pension'] ?? '') === 'militaire' ? 'checked' : '' }}>
                        Militaire
                    </label>
                </div>

                @error('regime_pension')
                    <p class="mt-1 text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </fieldset>

            {{-- Informations du pensionné --}}
            <fieldset class="border rounded-lg p-5">
                <legend class="font-medium text-gray-700">Informations du Pensionné</legend>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>Code Pension *</label>
                        <input type="text" name="code_pension"
                               value="{{ old('code_pension', $demande?->data['code_pension'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border @error('code_pension') border-red-500 @enderror">
                        @error('code_pension') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Montant (Gdes) *</label>
                        <input type="number" name="montant"
                               value="{{ old('montant', $demande?->data['montant'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border @error('montant') border-red-500 @enderror">
                        @error('montant') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>Nom *</label>
                        <input type="text" name="nom"
                               value="{{ old('nom', $demande?->data['nom'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('nom') border-red-500 @enderror">
                        @error('nom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Prénom *</label>
                        <input type="text" name="prenom"
                               value="{{ old('prenom', $demande?->data['prenom'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('prenom') border-red-500 @enderror">
                        @error('prenom') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Nom de jeune fille</label>
                        <input type="text" name="nom_jeune_fille"
                               value="{{ old('nom_jeune_fille', $demande?->data['nom_jeune_fille'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('nom_jeune_fille') border-red-500 @enderror">
                        @error('nom_jeune_fille') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label>NIF *</label>
                        <input type="text" name="nif"
                        placeholder="345-667-222-5"
                               value="{{ old('nif', $demande?->data['nif'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('nif') border-red-500 @enderror">
                        @error('nif') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>NINU</label>
                        <input type="text" name="ninu"
                               value="{{ old('ninu', $demande?->data['ninu'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('ninu') border-red-500 @enderror">
                        @error('ninu') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Téléphone (WhatsApp)</label>
                        <input type="text" name="telephone"
                        placeholder="+509XXXXXXXX"
                               value="{{ old('telephone', $demande?->data['telephone'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('telephone') border-red-500 @enderror">
                        @error('telephone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label>Adresse</label>
                        <input type="text" name="adresse"
                               value="{{ old('adresse', $demande?->data['adresse'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('adresse') border-red-500 @enderror">
                        @error('adresse') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>Courriel</label>
                        <input type="email" name="email"
                               value="{{ old('email', $demande?->data['email'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('email') border-red-500 @enderror">
                         @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                               value="{{ old('periode_debut', $demande?->data['periode_debut'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('periode_debut') border-red-500 @enderror">
                        @error('periode_debut') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label>À *</label>
                        <input type="date" name="periode_fin"
                               value="{{ old('periode_fin', $demande?->data['periode_fin'] ?? '') }}"
                               class="input mt-1 w-full rounded-md border  @error('periode_fin') border-red-500 @enderror">
                        @error('periode_fin') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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

                    <x-file-input name="pieces[]" accept=".pdf,.jpg,.jpeg,.png" multiple
                        hint="Formats acceptés : PDF, JPG, PNG — plusieurs fichiers autorisés." />

                    @if (!empty($demande?->data['pieces']))
                        <p class="text-sm text-green-700 mt-2">
                            {{ count($demande->data['pieces']) }} fichier(s) déjà uploadé(s). Uploader de nouveaux fichiers les remplacera.
                        </p>
                    @endif

                    @error('pieces')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror

                    @error('pieces.*')
                        <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            {{-- Boutons --}}
            <div class="mt-6 flex items-center gap-2">
                <input type="checkbox" name="is_urgent" id="is_urgent" value="1" class="w-4 h-4 accent-red-600">
                <label for="is_urgent" class="text-sm font-medium text-red-600 cursor-pointer">Marquer comme urgent</label>
            </div>
            <div class="mt-4 flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('action-input').value='draft'; document.getElementById('arret-paiement-form').submit();"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                    Sauvegarder
                </button>
                <button type="button"
                    onclick="document.getElementById('action-input').value='submit'; document.getElementById('arret-paiement-form').submit();"
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
