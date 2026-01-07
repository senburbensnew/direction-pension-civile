@extends('layouts.main')

@section('title', 'Demande d\'état de carrière')

@section('css')
<style>
    .input {
        @apply mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500;
    }
    
    .input-error {
        @apply border-red-500 focus:border-red-500 focus:ring-red-500;
    }
    
    .error-message {
        @apply text-red-600 text-sm mt-1;
    }
</style>
@endsection

@section('content')
<div class="m-5 max-w-6xl mx-auto p-6 bg-white rounded-lg shadow-md">
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

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            <strong>Veuillez corriger les erreurs suivantes :</strong>
            <ul class="list-disc list-inside mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('demandes.demande-etat-carriere.store') }}" method="POST" enctype="multipart/form-data" id="demandeForm">
        @csrf

        {{-- ================= INFORMATIONS PERSONNELLES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                <span class="mr-2">1.</span> Informations personnelles
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom *</label>
                    <input type="text" name="lastname" value="{{ old('lastname') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('lastname') input-error @enderror">
                    @error('lastname')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Prénom(s) *</label>
                    <input type="text" name="firstname" value="{{ old('firstname') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('firstname') input-error @enderror" >
                    @error('firstname')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom de jeune fille</label>
                    <input type="text" name="maiden_name" value="{{ old('maiden_name') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('maiden_name') input-error @enderror">
                    @error('maiden_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date de naissance *</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" 
                           max="{{ date('Y-m-d') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('birth_date') input-error @enderror" >
                    @error('birth_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Lieu de naissance *</label>
                    <input type="text" name="birth_place" value="{{ old('birth_place') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('birth_place') input-error @enderror" >
                    @error('birth_place')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">État civil *</label>
                    <select name="civil_status" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('civil_status') input-error @enderror" >
                        <option value="">Sélectionner</option>
                        <option value="celibataire" {{ old('civil_status') == 'celibataire' ? 'selected' : '' }}>Célibataire</option>
                        <option value="marie" {{ old('civil_status') == 'marie' ? 'selected' : '' }}>Marié(e)</option>
                        <option value="veuf" {{ old('civil_status') == 'veuf' ? 'selected' : '' }}>Veuf(ve)</option>
                        <option value="divorce" {{ old('civil_status') == 'divorce' ? 'selected' : '' }}>Divorcé(e)</option>
                    </select>
                    @error('civil_status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= IDENTIFICATION ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                <span class="mr-2">2.</span> Informations d'identification
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Matricule / Numéro pensionné *</label>
                    <input type="text" name="registration_number" value="{{ old('registration_number') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('registration_number') input-error @enderror" >
                    @error('registration_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">NIF / CIN *</label>
                    <input type="text" name="identity_number" value="{{ old('identity_number') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('identity_number') input-error @enderror" >
                    @error('identity_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= INFORMATIONS PROFESSIONNELLES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                <span class="mr-2">3.</span> Informations professionnelles
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Statut *</label>
                    <select name="status" class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('status') input-error @enderror" >
                        <option value="">Sélectionner</option>
                        <option value="fonctionnaire" {{ old('status') == 'fonctionnaire' ? 'selected' : '' }}>Fonctionnaire</option>
                        <option value="contractuel" {{ old('status') == 'contractuel' ? 'selected' : '' }}>Contractuel</option>
                        <option value="salarie" {{ old('status') == 'salarie' ? 'selected' : '' }}>Salarié</option>
                        <option value="pensionne" {{ old('status') == 'pensionne' ? 'selected' : '' }}>Pensionné</option>
                    </select>
                    @error('status')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Administration / Employeur *</label>
                    <input type="text" name="employer" value="{{ old('employer') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('employer') input-error @enderror" >
                    @error('employer')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Fonction ou grade *</label>
                    <input type="text" name="position" value="{{ old('position') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('position') input-error @enderror" >
                    @error('position')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date début de service *</label>
                    <input type="date" name="service_start_date" value="{{ old('service_start_date') }}" 
                           max="{{ date('Y-m-d') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('service_start_date') input-error @enderror" >
                    @error('service_start_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date fin de service</label>
                    <input type="date" name="service_end_date" value="{{ old('service_end_date') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('service_end_date') input-error @enderror">
                    @error('service_end_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Numéro de dossier</label>
                    <input type="text" name="file_number" value="{{ old('file_number') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('file_number') input-error @enderror">
                    @error('file_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= COORDONNÉES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                <span class="mr-2">4.</span> Coordonnées du demandeur
            </h3>
            
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Adresse *</label>
                    <input type="text" name="address" value="{{ old('address') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('address') input-error @enderror" >
                    @error('address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Téléphone *</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone') input-error @enderror" >
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" 
                           class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') input-error @enderror" >
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= PIÈCES JOINTES ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                <span class="mr-2">5.</span> Pièces jointes
            </h3>
            <p class="text-sm text-gray-600 mb-4">Format accepté : PDF, JPG, PNG. Taille max : 5Mo par fichier.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">
                        Copie de la CIN ou passeport *
                        <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="identity_copy" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('identity_copy') input-error @enderror"
                           >
                    @error('identity_copy')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lettre de nomination</label>
                    <input type="file" name="appointment_letter" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Bulletins de salaire (3 derniers mois)</label>
                    <input type="file" name="salary_slips" multiple 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Documents relatifs à la carrière</label>
                    <input type="file" name="career_documents" multiple 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Acte de mariage ou décès (le cas échéant)</label>
                    <input type="file" name="marriage_or_death_certificate" 
                           accept=".pdf,.jpg,.jpeg,.png" 
                           class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
            </div>
        </div>

        {{-- ================= OBJET & MOTIF ================= --}}
        <div class="mb-8 p-4 border border-gray-200 rounded-lg">
            <h3 class="text-lg font-semibold mb-4  flex items-center">
                <span class="mr-2">6.</span> Objet de la demande
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Objet</label>
                    <input type="text" name="subject" value="Demande d'état de carrière" 
                           readonly class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Motif *</label>
                    <textarea name="reason" rows="5" 
                              class="input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reason') input-error @enderror" 
                              placeholder="Précisez le motif de votre demande..." >{{ old('reason') }}</textarea>
                    @error('reason')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- ================= MENTIONS ET CONSENTEMENT ================= --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-start">
                <input type="checkbox" name="consent" id="consent" 
                       class="mt-1 mr-2" >
                <label for="consent" class="text-sm text-gray-700">
                    Je certifie sur l'honneur l'exactitude des informations fournies et j'accepte que mes données personnelles soient traitées dans le cadre de cette demande. *
                </label>
            </div>
            @error('consent')
                <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        {{-- ================= BOUTONS ================= --}}
        <div class="flex flex-col sm:flex-row gap-4 mt-8">
            <button type="submit" 
                    class="px-6 py-3 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-200 flex-1">
                <span class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Soumettre la demande
                </span>
            </button>
        </div>
    </form>
    
    <div class="mt-6 pt-6 border-t border-gray-200 text-sm text-gray-500">
        <p class="mb-1"><span class="text-red-500">*</span> Champs obligatoires</p>
        <p>Votre demande sera traitée dans les meilleurs délais. Vous recevrez un accusé de réception par email.</p>
    </div>
</div>
@endsection