@props(['pensionCategories', 'demande' => null, 'isDemandeReadyForSubmission' => false])
<div class="max-w-6xl mx-auto p-6 m-2">
    <!-- Form Section -->
    <div id="form-section" class="max-w-7xl mx-auto bg-white p-6 shadow-md rounded-lg relative m-2">
        <div class="flex flex-col md:flex-row justify-between items-center mb-12 gap-8">
            <div class="text-center mb-6 flex flex-col md:flex-row items-center justify-center w-full gap-4 md:gap-8">
                <!-- Visible on all screens -->
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                    class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 order-1 md:order-none">

                <div class="px-4 order-2 md:order-none">
                    <h2 class="text-lg md:text-xl font-bold mb-1">Direction de la Pension Civile (DPC)</h2>
                    <h3 class="text-base md:text-lg font-semibold mb-2">Formulaire de doléances / Demande de transfert de chèques
                    </h3>
                    <p class="text-sm md:text-gray-600">Service de Comptabilité</p>
                </div>

                <!-- Hidden on mobile, visible on md+ screens -->
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" alt="Logo"
                    class="w-16 h-16 md:w-24 md:h-24 object-cover shrink-0 order-1 md:order-none hidden md:block">
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($demande)
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded text-sm">
                Brouillon en cours — dernière sauvegarde {{ $demande->updated_at->diffForHumans() }}
            </div>
        @endif

        <form method="POST" action="{{ route('demandes.transfert-cheque.store') }}" id="check-transfert-form">
            @csrf
            <input type="hidden" name="demande_id" value="{{ $demande?->id }}">
            <input type="hidden" name="action" id="action-input" value="draft">

            {{-- Titre personnalisé --}}
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Titre personnalisé <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $demande?->title ?? '') }}"
                    placeholder="ex : Transfert de chèque — 2026"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fiscal_year" class="block text-sm font-medium text-gray-700 mb-1">
                            Année fiscale
                        </label>
                        <input type="text" name="annee_fiscale" id="annee_fiscale" value="{{ old('annee_fiscale', $demande?->data['annee_fiscale'] ?? '') }}"
                            placeholder="ex:2025/2026" class="w-full rounded-md @error('annee_fiscale') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('annee_fiscale')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Date & Month Section -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <legend class="text-sm font-medium text-gray-700 mb-2">Période de transfert</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="mois_debut" class="block text-sm font-medium text-gray-700 mb-1">
                            À partir du mois de *
                        </label>
                        <input type="month" name="mois_debut" id="mois_debut" value="{{ old('mois_debut', $demande?->data['mois_debut'] ?? '') }}"
                            class="w-full rounded-md @error('mois_debut') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('mois_debut')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="date_demande" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de demande *
                        </label>
                        <input type="date" name="date_demande" id="date_demande" value="{{ old('date_demande', $demande?->data['date_demande'] ?? '') }}"
                            class="w-full rounded-md @error('date_demande') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('date_demande')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Pension Regime Section -->
            <fieldset
                class="shadow-md rounded-lg p-5 border mb-6 @error('categorie_pension__id') border-red-500 @else border-gray-300 @enderror">
                <legend class="text-sm font-medium text-gray-700 mb-2">Régime de Pension *</legend>
                <div class="flex space-x-4">
                    @foreach ($pensionCategories as $type)
                        <div class="flex items-center">
                            <input type="radio" name="categorie_pension_id"
                                value="{{ $type['id'] }}"
                                class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                @checked(old('categorie_pension_id', $demande?->data['categorie_pension_id'] ?? '') == $type['id'])>
                            <label class="ml-2 text-sm text-gray-700">
                                {{ $type['name'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
                @error('categorie_pension_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <!-- Pension Information -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="code_pension" class="block text-sm font-medium text-gray-700 mb-1">
                            Code pension *
                        </label>
                        <input type="text" name="code_pension" id="code_pension"
                            value="{{ old('code_pension', $demande?->data['code_pension'] ?? auth()->user()->pension_code) }}"

                            class="w-full rounded-md @error('code_pension') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            >
                        @error('code_pension')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="montant" class="block text-sm font-medium text-gray-700 mb-1">
                            Montant (en Gdes) *
                        </label>
                        <input type="number" name="montant" id="montant" value="{{ old('montant', $demande?->data['montant'] ?? '') }}"
                            class="w-full rounded-md @error('montant') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            step="0.01" min="0">
                        @error('montant')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Personal Information -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <legend class="text-sm font-medium text-gray-700 mb-2">Informations personnelles</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom *
                        </label>
                        <input type="text" name="nom" id="nom" value="{{ old('nom', $demande?->data['nom'] ?? '') }}"
                            class="w-full rounded-md @error('nom') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('nom')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">
                            Prénom *
                        </label>
                        <input type="text" name="prenom" id="prenom" value="{{ old('prenom', $demande?->data['prenom'] ?? '') }}"
                            class="w-full rounded-md @error('prenom') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('prenom')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="nom_jeune_fille" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom de Jeune Fille
                        </label>
                        <input type="text" name="nom_jeune_fille" id="nom_jeune_fille"
                            value="{{ old('nom_jeune_fille', $demande?->data['nom_jeune_fille'] ?? '') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('nom_jeune_fille') border-red-500 @else border-gray-300 @enderror">
                        @error('nom_jeune_fille')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Contact Information -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">
                            NIF *
                        </label>
                        <input placeholder="998-369-226-3" type="text" name="nif" id="nif" value="{{ old('nif', $demande?->data['nif'] ?? auth()->user()->nif) }}"
                            class="w-full rounded-md @error('nif') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            >
                        @error('nif')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="ninu" class="block text-sm font-medium text-gray-700 mb-1">
                            NINU *
                        </label>
                        <input type="text" name="ninu" id="ninu" value="{{ old('ninu', $demande?->data['ninu'] ?? '') }}"
                            class="w-full rounded-md @error('ninu') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('ninu')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">
                            Adresse *
                        </label>
                        <input type="text" name="adresse" id="adresse" value="{{ old('adresse', $demande?->data['adresse'] ?? '') }}"
                            class="w-full rounded-md @error('adresse') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('adresse')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">
                            Téléphone *
                        </label>
                        <input placeholder="+509 XXXX-XXXX" type="tel" name="telephone" id="telephone" value="{{ old('telephone', $demande?->data['telephone'] ?? '') }}"
                            class="w-full rounded-md @error('telephone') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500"
                            >
                        @error('telephone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Courriel
                        </label>
                        <input placeholder="nom.prenom@example.com" type="email" name="email" id="email" value="{{ old('email', $demande?->data['email'] ?? '') }}"
                            class="w-full rounded-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @else border-gray-300 @enderror">
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </fieldset>

            <!-- Transfer Details -->
            <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <legend class="text-sm font-medium text-gray-700 mb-2">Détails du transfert</legend>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="from" class="block text-sm font-medium text-gray-700 mb-1">
                            De *
                        </label>
                        <input type="date" name="de" id="de" value="{{ old('de', $demande?->data['de'] ?? '') }}"
                            class="w-full rounded-md @error('de') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('de')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="to" class="block text-sm font-medium text-gray-700 mb-1">
                            À *
                        </label>
                        <input type="date" name="a" id="a" value="{{ old('a', $demande?->data['a'] ?? '') }}"
                            class="w-full rounded-md @error('a') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">
                        @error('a')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <label for="raison_transfert" class="block text-sm font-medium text-gray-700 mb-1">
                        Motif du transfert *
                    </label>
                    <textarea name="raison_transfert" id="raison_transfert" rows="3"
                        class="w-full rounded-md @error('raison_transfert') border-red-500 @else border-gray-300 @enderror focus:border-blue-500 focus:ring-blue-500">{{ old('raison_transfert', $demande?->data['raison_transfert'] ?? '') }}</textarea>
                    @error('raison_transfert')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </fieldset>

            <!-- Signature Section -->
{{--             <fieldset class="shadow-md rounded-lg p-5 border mb-6">
                <div class="text-left mb-4">
                    <p class="font-semibold text-gray-600 pt-5">
                        Fait à <span contenteditable="true">Port-au-Prince</span>, le
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
            </fieldset> --}}

            <!-- Submit Section -->
            <div class="mt-8 flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('action-input').value='draft'; document.getElementById('check-transfert-form').submit();"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300 transition-colors">
                    Sauvegarder en brouillon
                </button>
                <button type="button"
                    onclick="document.getElementById('action-input').value='submit'; document.getElementById('check-transfert-form').submit();"
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
            document.getElementById("check-transfert-form").addEventListener("submit", function(event) {
                let editableText = document.getElementById("editableText").innerText.trim();
                document.getElementById("hiddenInput").value = editableText;
            });
        });
    </script>
@endpush
