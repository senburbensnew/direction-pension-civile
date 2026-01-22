@extends('layouts.main')

@section('title', 'Demande d\'état de carrière')

@section('content')
<div class="m-5 max-w-6xl mx-auto p-6 bg-white rounded-lg shadow-md my-5">
    <div class="mb-8">
        <!-- En-tête avec logos -->
        <div class="flex flex-col md:flex-row justify-around items-center mb-6 gap-4">
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" 
                 alt="Logo de la Direction de la Pension Civile"
                 class="w-16 h-16 md:w-20 md:h-20 object-contain">
            
            <div class="text-center">
                <h1 class="text-lg md:text-xl font-bold text-gray-800">Direction de la Pension Civile (DPC)</h1>
                <h2 class="text-xl md:text-2xl font-semibold mt-2">Demande d'état de carrière</h2>
                <p class="text-gray-600 mt-1">Formulaire de demande</p>
            </div>
            
            <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" 
                 alt="Logo" 
                 class="w-16 h-16 md:w-20 md:h-20 object-contain hidden md:block">
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('demandes.demande-etat-carriere.store') }}" method="POST" enctype="multipart/form-data" id="demandeForm">
        @csrf

        {{-- ================= INFORMATIONS PERSONNELLES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                Informations personnelles
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom *</label>
                    <input type="text" name="nom" value="{{ old('nom') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nom') input-error @enderror">
                    @error('nom')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Prénom(s) *</label>
                    <input type="text" name="prenom" value="{{ old('prenom') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('prenom') input-error @enderror" >
                    @error('prenom')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom de jeune fille</label>
                    <input type="text" name="nom_jeune_fille" value="{{ old('nom_jeune_fille') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nom_jeune_fille') input-error @enderror">
                    @error('nom_jeune_fille')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de naissance *</label>
                    <input type="date" name="date_naissance" value="{{ old('date_naissance') }}" 
                           max="{{ date('Y-m-d') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_naissance') input-error @enderror" >
                    @error('date_naissance')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lieu de naissance *</label>
                    <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('lieu_naissance') input-error @enderror" >
                    @error('lieu_naissance')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">État civil *</label>
                    <select name="etat_civil" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('etat_civil') input-error @enderror" >
                        <option value="">Sélectionner</option>
                        <option value="celibataire" {{ old('etat_civil') == 'celibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="marie" {{ old('etat_civil') == 'marie' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="veuf" {{ old('etat_civil') == 'veuf' ? 'selected' : '' }}>Veuf(ve)</option>
                        <option value="divorce" {{ old('etat_civil') == 'divorce' ? 'selected' : '' }}>Divorcé(e)</option>
                    </select>
                    @error('etat_civil')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= IDENTIFICATION ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                Informations d'identification
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIF / NINU *</label>
                    <input type="text" name="nif_ninu" value="{{ old('nif_ninu') }}" 
                    placeholder="000-000-000-0"
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nif_ninu') input-error @enderror" >
                    @error('nif_ninu')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">CIN *</label>
                    
                    <input type="text" name="cin" value="{{ old('cin') }}" 
                    placeholder="000-000-000-0"
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('cin') input-error @enderror" >
                    @error('cin')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= INFORMATIONS PROFESSIONNELLES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                Informations professionnelles
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Statut *</label>
                    <select name="statut" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('statut') input-error @enderror" >
                        <option value="">Sélectionner</option>
                        <option value="fonctionnaire" {{ old('statut') == 'fonctionnaire' ? 'selected' : '' }}>Fonctionnaire</option>
                        <option value="contractuel" {{ old('statut') == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                        <option value="salarie" {{ old('statut') == 'salarie' ? 'selected' : '' }}>Salarié</option>
                        <option value="pensionne" {{ old('statut') == 'pensionne' ? 'selected' : '' }}>Pensionné</option>
                    </select>
                    @error('statut')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Administration / Employeur *</label>
                    <input type="text" name="employeur" value="{{ old('employeur') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('employeur') input-error @enderror" >
                    @error('employeur')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fonction ou grade *</label>
                    <input type="text" name="fonction" value="{{ old('fonction') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('fonction') input-error @enderror" >
                    @error('fonction')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date début de service *</label>
                    <input type="date" name="date_debut_service" value="{{ old('date_debut_service') }}" 
                           max="{{ date('Y-m-d') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_debut_service') input-error @enderror" >
                    @error('date_debut_service')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date fin de service</label>
                    <input type="date" name="date_fin_service" value="{{ old('date_fin_service') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('date_fin_service') input-error @enderror">
                    @error('date_fin_service')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Numéro de dossier</label>
                    <input type="text" name="numero_dossier" value="{{ old('numero_dossier') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('numero_dossier') input-error @enderror">
                    @error('numero_dossier')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= COORDONNÉES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                Coordonnées du demandeur
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Adresse *</label>
                    <input type="text" name="adresse" value="{{ old('adresse') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('adresse') input-error @enderror" >
                    @error('adresse')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Téléphone *</label>
                    <input type="tel" name="telephone" value="{{ old('telephone') }}" 
                    placeholder="+509XXXXXXXX"
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('telephone') input-error @enderror" >
                    @error('telephone')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') input-error @enderror" >
                    @error('email')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= PIÈCES JOINTES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                Pièces jointes
            </h3>
            <p class="text-sm text-gray-600 mb-4">Format accepté : PDF, JPG, PNG. Taille max : 5Mo par fichier.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Copie de la CIN ou passeport *
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="copie_piece_identite" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('copie_piece_identite') input-error @enderror"
                           >
                    @error('copie_piece_identite')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lettre de nomination</label>
                    <input type="file" name="lettre_nomination" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('lettre_nomination') input-error @enderror">
                    @error('lettre_nomination')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bulletins de salaire (3 derniers mois)</label>
                    <input type="file" name="bulletins_salaire[]" multiple 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('bulletins_salaire') input-error @enderror">
                    @error('bulletins_salaire')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Documents relatifs à la carrière</label>
                    <input type="file" name="documents_carriere[]" multiple 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('documents_carriere') input-error @enderror">
                    @error('documents_carriere')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Acte de mariage ou décès (le cas échéant)</label>
                    <input type="file" name="acte_mariage_acte_deces" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('acte_mariage_acte_deces') input-error @enderror">
                    @error('acte_mariage_acte_deces')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= OBJET & MOTIF ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                Objet de la demande
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Objet</label>
                    <input type="text" name="sujet" value="Demande d'état de carrière" 
                           readonly class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Motif *</label>
                    <textarea 
                        name="raison" 
                        rows="5" 
                        class="mt-1 block w-full rounded-md border border-gray-300 shadow-sm
                            focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50
                            h-40 resize-y @error('raison') border-red-500 @enderror"
                        placeholder="Précisez le motif de votre demande...">{{ old('raison') }}</textarea>
                    @error('raison')
                        <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </div>

        {{-- ================= MENTIONS ET CONSENTEMENT ================= --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-start">
                <input type="checkbox" name="consentement" id="consent" 
                       class="mt-1 mr-2" >
                <label for="consentement" class="text-sm text-gray-700">
                    Je certifie sur l'honneur l'exactitude des informations fournies et j'accepte que mes données personnelles soient traitées dans le cadre de cette demande. *
                </label>
            </div>
            @error('consentement')
                <span class="mt-1 text-sm text-red-600">{{ $message }}</span>
            @enderror
        </div>

        {{-- ================= BOUTONS ================= --}}
        <div class="flex justify-end mt-8 gap-4">
            <button type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-200">
                <span class="flex items-center justify-center">
                    Soumettre
                </span>
            </button>
        </div>

    </form>
</div>
@endsection