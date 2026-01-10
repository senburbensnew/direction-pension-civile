@inject('storage', 'Illuminate\Support\Facades\Storage')

<x-app-layout>
    <!-- Common Header -->
    <div class="max-w-7xl mx-auto pt-5 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <nav class="text-sm text-gray-500 flex items-center mb-5">
                <a href="{{ route('personal.dashboard') }}" class="hover:underline">Dashboard</a>
                <span class="mx-2">/</span>
                @if(url()->previous() !== url()->current())
                    <a href="{{ url()->previous() }}" class="hover:underline">Liste</a>
                @endif
                <span class="mx-2">/</span>
                <span class="text-gray-700 font-semibold">Détails de la demande</span>
            </nav>
            <div>
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">#{{ $request->code }}</h2>
                    <div class="flex items-center space-x-4">
                        {{-- <a href=""
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md flex items-center transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 114.95 0 2.5 2.5 0 01-4.95 0M12 15v3m0 0h3m-3 0H9m6-12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Modifier
                        </a>
                        <a href=""
                                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md flex items-center transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 114.95 0 2.5 2.5 0 01-4.95 0M12 15v3m0 0h3m-3 0H9m6-12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Annuler
                        </a> --}}
                        <a href="{{ route('personal.dashboard') }}"
                                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md flex items-center transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 10h10M3 14h10m5-4v8m-9-6h10M3 10l5 5m0 0l5-5" />
                                            </svg>
                                            Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @switch($requestType)
        {{-- Pensionnaire --}}
        @case('bankTransferRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- ========================= -->
                            <!-- Status Banner -->
                            <!-- ========================= -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- ========================= -->
                            <!-- Main Grid -->
                            <!-- ========================= -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- ========================= -->
                                <!-- Left Column -->
                                <!-- ========================= -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- État civil -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">État civil</h3>

                                        <dl class="space-y-3">

                                            <div class="flex items-center">
                                                @if(!empty($request->data['profile_photo']))
                                                    <img
                                                        src="{{ asset('storage/' . $request->data['profile_photo']) }}"
                                                        class="w-20 h-20 rounded-full object-cover mr-4"
                                                        alt="Photo de profil">
                                                @endif

                                                <div>
                                                    <dt class="text-sm text-gray-500">Nom complet</dt>
                                                    <dd class="font-medium">
                                                        {{ $request->data['nom_complet'] ?? '-' }}
                                                    </dd>
                                                </div>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nif'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de naissance</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->data['date_naissance'])->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">État civil</dt>
                                                <dd class="font-medium">
                                                    {{ ucfirst($request->civilStatus('statut_civil_id')->name) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Genre</dt>
                                                <dd class="font-medium">
                                                    {{ $request->gender('sexe_id')->name }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de la mère</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_mere'] ?? '-' }}
                                                </dd>
                                            </div>

                                        </dl>
                                    </div>

                                    <!-- Coordonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées</h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['adresse'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Ville</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['ville'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['telephone'] ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- ========================= -->
                                <!-- Right Column -->
                                <!-- ========================= -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Allocation -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant de l'allocation</dt>
                                            <dd class="text-2xl font-bold text-blue-600">
                                                {{ number_format($request->data['montant_allocation'], 2, ',', ' ') }} HTG
                                            </dd>
                                        </div>

                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Type de pension</dt>
                                            <dd class="font-medium">
                                                {{ ucfirst($request->pensionType('type_pension_id')->name) }}
                                            </dd>
                                        </div>
                                    </div>

                                    <!-- Détails pension -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Détails de pension</h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Catégorie de pension</dt>
                                                <dd class="font-medium">
                                                    {{ ucfirst($request->pensionCategory('categorie_pension_id')->name) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['code_pension'] ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Coordonnées bancaires -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées bancaires</h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de la banque</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_banque'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro de compte</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['numero_compte'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div class="md:col-span-2">
                                                <dt class="text-sm text-gray-500">Titulaire du compte</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_compte'] ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Métadonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Métadonnées</h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de création</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('certificateRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- Status Banner -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Main Content -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- Left Column -->
                                <div class="lg:col-span-1 space-y-6">
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du pensionnaire
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['prenom'] }}
                                                    {{ $request->data['nom'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nif'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['code_pension'] }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Request Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('checkTransferRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- Status Banner -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- COLONNE GAUCHE -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- Identité -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identité</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom'] }} {{ $request->data['prenom'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de jeune fille</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_jeune_fille'] ?? '—' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Identifiants -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Identifiants officiels
                                        </h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->data['nif'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">{{ $request->data['ninu'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">{{ $request->data['code_pension'] }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Coordonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Coordonnées
                                        </h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">{{ $request->data['adresse'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ $request->data['telephone'] }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Email</dt>
                                                <dd class="font-medium">{{ $request->data['email'] }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                <!-- COLONNE DROITE -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Résumé -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant du transfert</dt>
                                            <dd class="text-2xl font-bold text-blue-600">
                                                {{ number_format($request->data['montant'], 0, ',', ' ') }} HTG
                                            </dd>
                                        </div>

                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Date de la demande</dt>
                                            <dd class="font-medium">
                                                {{ \Carbon\Carbon::parse($request->data['date_demande'])->format('d/m/Y') }}
                                            </dd>
                                        </div>
                                    </div>

                                    <!-- Calendrier fiscal -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Calendrier fiscal
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Année fiscale</dt>
                                                <dd class="font-medium">{{ $request->data['annee_fiscale'] }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Mois de début</dt>
                                                <dd class="font-medium">{{ $request->data['mois_debut'] }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Catégorie de pension</dt>
                                                <dd class="font-medium">
                                                    {{ $request->pensionCategory('categorie_pension_id')->name }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Période -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Période du transfert
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Du</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->data['de'])->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Au</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->data['a'])->format('d/m/Y') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Contexte -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Contexte du transfert
                                        </h3>
                                        <p class="text-gray-700 leading-relaxed">
                                            {{ $request->data['raison_transfert'] }}
                                        </p>
                                    </div>

                                    <!-- Métadonnées -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('paymentStopRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            {{-- ================= STATUS BANNER ================= --}}
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            {{-- ================= MAIN GRID ================= --}}
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                {{-- ================= LEFT COLUMN ================= --}}
                                <div class="lg:col-span-1 space-y-6">

                                    {{-- Pensionnaire --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du pensionnaire
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['prenom'] }} {{ $request->data['nom'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de jeune fille</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_jeune_fille'] ?? '—' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['code_pension'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Régime de pension</dt>
                                                <dd class="font-medium capitalize">
                                                    {{ $request->data['regime_pension'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nif'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['ninu'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['telephone'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Email</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['email'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['adresse'] }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                {{-- ================= RIGHT COLUMN ================= --}}
                                <div class="lg:col-span-2 space-y-6">

                                    {{-- Détails de la demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Exercice</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['exercice'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Mois de début</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->data['mois_debut'])->format('m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Période demandée</dt>
                                                <dd class="font-medium">
                                                    Du {{ \Carbon\Carbon::parse($request->data['periode_debut'])->format('d/m/Y') }}
                                                    au {{ \Carbon\Carbon::parse($request->data['periode_fin'])->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Montant</dt>
                                                <dd class="font-medium">
                                                    {{ number_format($request->data['montant'], 0, ',', ' ') }} GDES
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de la demande</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->date_demande)->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                        </dl>
                                    </div>

                                    {{-- Pièces jointes --}}
                                    @if(!empty($request->data['pieces']))
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Pièces justificatives
                                        </h3>

                                        <ul class="list-disc list-inside space-y-2">
                                            @foreach($request->data['pieces'] as $piece)
                                                <li>
                                                    <a href="{{ Storage::url($piece) }}"
                                                    target="_blank"
                                                    class="text-blue-600 hover:underline">
                                                        {{ basename($piece) }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    @endif

                                    {{-- Métadonnées --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            @break
        @case('reinstateRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- Status Banner -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Main Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- LEFT COLUMN -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- Identity -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Identité
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Prénom</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['prenom'] }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom'] }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Request Info -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Type de demande</dt>
                                                <dd class="font-medium">
                                                    {{ str_replace('_', ' ', $request->type) }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Reason -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Motif de la réinsertion
                                        </h3>

                                        <p class="text-gray-700 leading-relaxed">
                                            {{ $request->data['raison'] }}
                                        </p>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('transferStopRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            {{-- ===================== --}}
                            {{-- BANNIÈRE DE STATUT --}}
                            {{-- ===================== --}}
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            {{-- ===================== --}}
                            {{-- GRILLE PRINCIPALE --}}
                            {{-- ===================== --}}
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                {{-- ===================== --}}
                                {{-- COLONNE GAUCHE --}}
                                {{-- ===================== --}}
                                <div class="lg:col-span-1 space-y-6">

                                    {{-- Type de demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                    {{-- Métadonnées --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                {{-- ===================== --}}
                                {{-- COLONNE DROITE --}}
                                {{-- ===================== --}}
                                <div class="lg:col-span-2 space-y-6">

                                    {{-- Code --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de la demande</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['date'] ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Informations du demandeur --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du demandeur
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom</dt>
                                                <dd class="font-medium">{{ $request->data['nom'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Prénom</dt>
                                                <dd class="font-medium">{{ $request->data['prenom'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ $request->data['telephone'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Courriel</dt>
                                                <dd class="font-medium">{{ $request->data['courriel'] ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Détails arrêt de virement --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Arrêt de virement
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Mois non reçu</dt>
                                                <dd class="font-medium">{{ $request->data['mois_non_recu'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nouveau numéro</dt>
                                                <dd class="font-medium">{{ $request->data['nouveau_numero'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom du compte</dt>
                                                <dd class="font-medium">{{ $request->data['nom_du_compte'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Chèques</dt>
                                                <dd class="font-medium">{{ $request->data['cheques'] ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Informations complémentaires --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations complémentaires
                                        </h3>

                                        <p class="text-gray-700">
                                            {{ $request->data['informations'] ?? 'Aucune information fournie' }}
                                        </p>
                                    </div>

                                    {{-- Motifs --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Motifs
                                        </h3>

                                        @if(!empty($request->data['motifs']))
                                            <ul class="list-disc list-inside space-y-1">
                                                @foreach($request->data['motifs'] as $motif)
                                                    <li class="font-medium">{{ $motif }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-500">Aucun motif renseigné</p>
                                        @endif
                                        </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
            @break
        @case('existenceProofRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- Status Banner -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Main Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- LEFT COLUMN -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- Request Type -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Request Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">
                                                    #{{ $request->code }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Identifiant interne</dt>
                                                <dd class="font-medium">
                                                    {{ $request->id }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        {{-- Fonctionnaire --}}
        @case('careerStateRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            {{-- ================= STATUS BANNER ================= --}}
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            {{-- ================= MAIN GRID ================= --}}
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                {{-- ========== LEFT COLUMN ========== --}}
                                <div class="lg:col-span-1 space-y-6">

                                    {{-- Type de demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                </div>

                                {{-- ========== RIGHT COLUMN ========== --}}
                                <div class="lg:col-span-2 space-y-6">

                                    {{-- Détails de la demande --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- Métadonnées --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    {{-- ================= INFORMATIONS PERSONNELLES ================= --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations personnelles
                                        </h3>

                                        @php
                                            $data = $request->data ?? [];
                                        @endphp

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @foreach([
                                                'nom' => 'Nom',
                                                'prenom' => 'Prénom',
                                                'nom_jeune_fille' => 'Nom de jeune fille',
                                                'date_naissance' => 'Date de naissance',
                                                'lieu_naissance' => 'Lieu de naissance',
                                                'etat_civil' => 'État civil',
                                                'nif_ninu' => 'NIF / NINU',
                                                'cin' => 'CIN',
                                                'statut' => 'Statut',
                                                'employeur' => 'Employeur',
                                                'fonction' => 'Fonction',
                                                'date_debut_service' => 'Début de service',
                                                'date_fin_service' => 'Fin de service',
                                                'numero_dossier' => 'Numéro de dossier',
                                                'adresse' => 'Adresse',
                                                'telephone' => 'Téléphone',
                                                'email' => 'Email',
                                                'raison' => 'Raison de la demande',
                                            ] as $key => $label)
                                                @if(!empty($data[$key]))
                                                    <div>
                                                        <dt class="text-sm text-gray-500">{{ $label }}</dt>
                                                        <dd class="font-medium">
                                                            {{ in_array($key, ['date_naissance','date_debut_service','date_fin_service'])
                                                                ? \Carbon\Carbon::parse($data[$key])->format('d/m/Y')
                                                                : $data[$key]
                                                            }}
                                                        </dd>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </dl>
                                    </div>

                                    {{-- ================= DOCUMENTS ================= --}}
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Documents fournis
                                        </h3>

                                        @php
                                            $documents = $request->data['documents'] ?? [];
                                        @endphp

                                        <div class="space-y-4">
                                            @foreach($documents as $label => $files)
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                                                        {{ ucwords(str_replace('_', ' ', $label)) }}
                                                    </h4>

                                                    <ul class="space-y-1">
                                                        @foreach((array) $files as $file)
                                                            <li>
                                                                <a href="{{ Storage::url($file) }}"
                                                                target="_blank"
                                                                class="text-blue-600 hover:underline text-sm">
                                                                    📄 {{ basename($file) }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('pensionRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- Status Banner -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Main Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- LEFT COLUMN -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- Identité -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Identité du demandeur
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">{{ $request->data['name'] }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->data['nif'] }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Type -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                </div>

                                <!-- RIGHT COLUMN -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- Request Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Documents -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-4 text-gray-700">
                                            Pièces jointes
                                        </h3>

                                        <div class="space-y-4">

                                            @php
                                                $documents = $request->data['documents'] ?? [];
                                            @endphp

                                            @foreach($documents as $label => $files)
                                                @php
                                                    $labels = [
                                                        'career_certificates'   => 'Certificat de carrière',
                                                        'birth_certificates'    => 'Acte de naissance',
                                                        'marriage_certificates' => 'Acte de mariage',
                                                        'divorce_certificate'   => 'Jugement de divorce',
                                                        'medical_certificate'   => 'Certificat médical',
                                                        'tax_id_numbers' => 'matricule fiscal et carte d’identification nationale',
                                                        'check_stub' => 'Souche de chèque ou preuve de paiement',
                                                        'monitor_copy' => 'Copie du Moniteur',
                                                        'photos' => 'photos'
                                                    ];

                                                    $displayLabel = $labels[$label]
                                                        ?? ucwords(str_replace('_', ' ', $label));
                                                @endphp

                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                                                        {{ ucwords(str_replace('_', ' ', $displayLabel)) }}
                                                    </h4>

                                                    <div class="space-y-2">
                                                        @foreach((array) $files as $file)
                                                            <a href="{{ asset('storage/' . $file) }}"
                                                            target="_blank"
                                                            class="flex items-center justify-between bg-white border border-gray-200 rounded-md px-4 py-2 text-sm hover:bg-gray-50">
                                                                <span class="truncate">
                                                                    {{ basename($file) }}
                                                                </span>
                                                                <span class="text-blue-600 font-medium">
                                                                    Voir
                                                                </span>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if(empty($documents))
                                                <p class="text-gray-500 text-sm">
                                                    Aucun document joint
                                                </p>
                                            @endif

                                        </div>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @case('reversionaryPensionRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- ===================== -->
                            <!-- STATUS BANNER -->
                            <!-- ===================== -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- ===================== -->
                            <!-- MAIN GRID -->
                            <!-- ===================== -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- ===================== -->
                                <!-- LEFT COLUMN -->
                                <!-- ===================== -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- TYPE -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                    <!-- METADATA -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="space-y-2">                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                </div>

                                <!-- ===================== -->
                                <!-- RIGHT COLUMN -->
                                <!-- ===================== -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- REQUEST DETAILS -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Détails de la demande
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- ===================== -->
                                    <!-- DOSSIER INFORMATION -->
                                    <!-- ===================== -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations du dossier
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet du défunt</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_complet_defunt'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro de pension</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['numero_pension'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom du bénéficiaire</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['nom_beneficiaire'] ?? '-' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Lien avec le défunt</dt>
                                                <dd class="font-medium capitalize">
                                                    {{ $request->data['relation_defunt'] ?? '-' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- ===================== -->
                                    <!-- DOCUMENTS -->
                                    <!-- ===================== -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Documents fournis
                                        </h3>

                                        @php
                                            $documentLabels = [
                                                'acte_deces' => 'Acte de décès',
                                                'photos_identite' => 'Photos d’identité',
                                                'attestation_scolaires' => 'Attestations scolaires',
                                                'certificat_carriere' => 'Certificat de carrière',
                                                'certificat_non_dissolution' => 'Certificat de non dissolution',
                                                'carte_pension' => 'Carte de pension',
                                                'souche_cheque' => 'Souche de chèque',
                                                'extrait_acte_mariage' => 'Extrait acte de mariage',
                                                'extrait_acte_naissance' => 'Extrait acte de naissance',
                                                'matricule_fiscal' => 'Matricule fiscal',
                                                'carte_electorale' => 'Carte électorale',
                                                'pv_tutelle' => 'PV de tutelle',
                                                'certificat_medical' => 'Certificat médical',
                                                'copie_moniteur' => 'Copie du moniteur',
                                            ];
                                        @endphp

                                        <div class="space-y-4">
                                            @forelse(($request->data['documents'] ?? []) as $key => $files)
                                                <div>
                                                    <h4 class="text-sm font-semibold text-gray-600 mb-2">
                                                        {{ $documentLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                                                    </h4>

                                                    <ul class="space-y-1">
                                                        @foreach((array) $files as $file)
                                                            <li class="flex items-center justify-between text-sm">
                                                                <span class="truncate">
                                                                    {{ basename($file) }}
                                                                </span>

                                                                <a
                                                                    href="{{ \Illuminate\Support\Facades\Storage::url($file) }}"
                                                                    target="_blank"
                                                                    class="text-blue-600 hover:underline"
                                                                >
                                                                    Voir
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @empty
                                                <p class="text-sm text-gray-500">
                                                    Aucun document joint.
                                                </p>
                                            @endforelse
                                        </div>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        {{-- Institution --}}
        @case('adhesionRequest')
            <div class="py-5">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">

                            <!-- ========================= -->
                            <!-- STATUS BANNER -->
                            <!-- ========================= -->
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour :
                                        {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- ========================= -->
                            <!-- MAIN GRID -->
                            <!-- ========================= -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                                <!-- ========================= -->
                                <!-- LEFT COLUMN -->
                                <!-- ========================= -->
                                <div class="lg:col-span-1 space-y-6">

                                    <!-- TYPE -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Type de demande
                                        </h3>
                                        <p class="font-medium">
                                            {{ str_replace('_', ' ', $request->type) }}
                                        </p>
                                    </div>

                                    <!-- METADATA -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Métadonnées
                                        </h3>

                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Créée le</dt>
                                                <dd class="font-medium">
                                                    {{ $request->created_at->format('d/m/Y H:i') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Soumise par</dt>
                                                <dd class="font-medium">
                                                    {{ $request->user?->name ?? 'Système' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- PHOTO -->
                                    @if(!empty($request->data['profile_picture']))
                                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Photo de profil
                                        </h3>

                                        <img
                                            src="{{ Storage::url($request->data['profile_picture']) }}"
                                            class="mx-auto w-32 h-32 rounded-full object-cover border"
                                            alt="Photo de profil"
                                        >
                                    </div>
                                    @endif

                                </div>

                                <!-- ========================= -->
                                <!-- RIGHT COLUMN -->
                                <!-- ========================= -->
                                <div class="lg:col-span-2 space-y-6">

                                    <!-- INFORMATIONS PERSONNELLES -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations personnelles
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Institution</dt>
                                                <dd class="font-medium">{{ $request->data['institution'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['firstname'] ?? '' }}
                                                    {{ $request->data['lastname'] ?? '' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Lieu de naissance</dt>
                                                <dd class="font-medium">{{ $request->data['birth_place'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Date de naissance</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->data['birth_date'])->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->data['nif'] ?? '-' }}</dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">{{ $request->data['ninu'] ?? '-' }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- SITUATION FAMILIALE -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Situation familiale
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Mère</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['mother_firstname'] ?? '' }}
                                                    {{ $request->data['mother_lastname'] ?? '' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Conjoint(e)</dt>
                                                <dd class="font-medium">
                                                    {{ $request->data['spouse_firstname'] ?? '-' }}
                                                    {{ $request->data['spouse_lastname'] ?? '' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- INFOS PROFESSIONNELLES -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Informations professionnelles
                                        </h3>

                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Date d’entrée</dt>
                                                <dd class="font-medium">
                                                    {{ \Carbon\Carbon::parse($request->data['entry_date'])->format('d/m/Y') }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-sm text-gray-500">Salaire actuel</dt>
                                                <dd class="font-medium">
                                                    {{ number_format($request->data['current_salary'], 0, ',', ' ') }} HTG
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- PERSONNES À CHARGE -->
                                    @if(!empty($request->data['dependents']))
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Personnes à charge
                                        </h3>

                                        <div class="space-y-3">
                                            @foreach($request->data['dependents'] as $dependent)
                                                <div class="p-3 bg-white rounded border">
                                                    <p class="font-medium">
                                                        {{ $dependent['firstname'] }} {{ $dependent['lastname'] }}
                                                    </p>
                                                    <p class="text-sm text-gray-500">
                                                        {{ ucfirst($dependent['relation']) }} —
                                                        {{ \Carbon\Carbon::parse($dependent['birthdate'])->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    <!-- EXPÉRIENCES -->
                                    @if(!empty($request->data['previous_jobs']))
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">
                                            Expériences professionnelles
                                        </h3>

                                        <div class="space-y-3">
                                            @foreach($request->data['previous_jobs'] as $job)
                                                <div class="p-3 bg-white rounded border">
                                                    <p class="font-medium">{{ $job['institution'] }}</p>
                                                    <p class="text-sm text-gray-500">
                                                        Du {{ \Carbon\Carbon::parse($job['start_date'])->format('d/m/Y') }}
                                                        au {{ \Carbon\Carbon::parse($job['end_date'])->format('d/m/Y') }}
                                                    </p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            @break
        @default
    @endswitch

    <!-- Request History -->
    <div class="max-w-7xl mx-auto pb-5 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg px-6">
            <h3 class="mt-5 ml-1 text-xl font-semibold mb-4 text-gray-800">
                Historique de la demande
            </h3>

            <div class="rounded-lg shadow-sm border border-gray-100">
                @forelse ($requestHistories as $history)
                    <div class="p-4 border-b border-gray-100 last:border-b-0">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-medium text-gray-700">
                                        {{ $history->statut }}
                                    </span>

                                    <span class="text-xs text-gray-500">
                                        {{ $history->created_at }}
                                    </span>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-sm text-gray-500">
                                    @if ($history->creator())
                                        Par {{ $history->creator()->name }}
                                    @else
                                        Système
                                    @endif
                                </p>

                                <p class="text-xs text-gray-400">
                                    #{{ $history->demande->type }}
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-center text-gray-500">
                        Aucun historique disponible pour cette demande
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $requestHistories->links() }}
            </div>
        </div>
    </div>
</x-app-layout>