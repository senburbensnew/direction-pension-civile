<x-app-layout>
    @switch($requestType)
        @case('bankTransferRequest')
            <x-slot name="header">
                <!-- Header remains unchanged -->
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Détails de la demande #{{ $request->code }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <a href=""
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
                        </a>
                        <a href="{{ route('personal.dashboard') }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10M3 14h10m5-4v8m-9-6h10M3 10l5 5m0 0l5-5" />
                            </svg>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </x-slot>

            <div class="py-8">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <nav class="text-sm text-gray-500 flex items-center mb-5">
                        <a href="{{ route('personal.dashboard') }}" class="hover:underline">Dashboard</a>
                        <span class="mx-2">/</span>
                        @if (url()->previous() !== url()->current())
                            <a href="{{ url()->previous() }}" class="hover:underline">Page précédente</a>
                        @endif
                        <span class="mx-2">/</span>
                        <span class="text-gray-700 font-semibold">Détails de la demande</span>
                    </nav>

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
                                        Dernière mise à jour : {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <!-- Main Content Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <!-- Left Column - Personal Information -->
                                <div class="lg:col-span-1 space-y-6">
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">État civil</h3>
                                        <dl class="space-y-3">
                                            <div class="flex justify-start items-center">
                                                @if ($request->profile_photo)
                                                    <img src="{{ asset('storage/' . $request->profile_photo) }}"
                                                        alt="Photo de profil" class="w-20 h-20 rounded-full object-cover mr-4">
                                                @endif
                                                <div>
                                                    <dt class="text-sm text-gray-500">Nom complet</dt>
                                                    <dd class="font-medium">{{ $request->full_name }}</dd>
                                                </div>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->nif }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Date de naissance</dt>
                                                <dd class="font-medium">{{ $request->birth_date->format('d/m/Y') }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">État civil</dt>
                                                <dd class="font-medium">{{ ucfirst($request->civilStatus->name) }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Genre</dt>
                                                <dd class="font-medium">
                                                    {{ $request->gender->name }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de la mère</dt>
                                                <dd class="font-medium">{{ $request->mother_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Signature</dt>
                                                <dd class="font-medium">
                                                    @if ($request->pensioner_signature)
                                                        <img src="{{ asset('storage/' . $request->pensioner_signature) }}"
                                                            alt="Signature" class="w-full h-full object-cover mr-4">
                                                    @endif
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">{{ $request->address }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Ville</dt>
                                                <dd class="font-medium">{{ $request->city }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ $request->phone }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Right Column - Professional & Bank Details -->
                                <div class="lg:col-span-2 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant de l'allocation</dt>
                                            <dd class="text-2xl font-bold text-blue-600">
                                                {{ number_format($request->allocation_amount, 2, ',', ' ') }} HTG
                                            </dd>
                                        </div>
                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Type de pension</dt>
                                            <dd class="font-medium">{{ ucfirst($request->pensionType->name) }}</dd>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Détails de pension</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Catégorie de pension</dt>
                                                <dd class="font-medium">{{ ucfirst($request->pensionCategory->name) }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Code pensionnaire</dt>
                                                <dd class="font-medium">{{ $request->pensioner_code }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées bancaires</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de la banque</dt>
                                                <dd class="font-medium">{{ $request->bank_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro de compte</dt>
                                                <dd class="font-medium">{{ $request->account_number }}</dd>
                                            </div>
                                            <div class="md:col-span-2">
                                                <dt class="text-sm text-gray-500">Titulaire du compte</dt>
                                                <dd class="font-medium">{{ $request->account_name }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Metadata -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Métadonnées</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Date de création</dt>
                                                <dd class="font-medium">{{ $request->created_at->format('d/m/Y H:i') }}</dd>
                                            </div>
                                            @if ($request->photo_path)
                                                <div class="md:col-span-2">
                                                    <dt class="text-sm text-gray-500">Photo</dt>
                                                    <dd class="mt-2">
                                                        <img src="{{ asset('storage/' . $request->photo_path) }}"
                                                            alt="Photo du demandeur"
                                                            class="w-32 h-32 object-cover rounded-lg border">
                                                    </dd>
                                                </div>
                                            @endif
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- History -->
                        <div class="mt-8 px-6">
                            <h3 class="ml-1 text-xl font-semibold mb-4 text-gray-800">Historique de la demande</h3>

                            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                                @forelse($requestHistories as $history)
                                    <div class="p-4 border-b border-gray-100 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ $history->event_type }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $history->event_date }}
                                                    </span>
                                                </div>

                                                {{--                                             @if ($history->request_data)
                                                                        <div class="text-sm text-gray-600 mt-1">
                                                                            @foreach (json_decode($history->request_data, true) as $key => $value)
                                                                                <p class="break-words">
                                                                                    <span
                                                                                        class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                                                </p>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif --}}
                                            </div>

                                            <div class="text-right">
                                                <p class="text-sm text-gray-500">
                                                    @if ($history->creator)
                                                        Par {{ $history->creator->name }}
                                                    @else
                                                        Système
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    #{{ $history->request_type }}
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
                </div>
            </div>
        @break

        @case('checkTransferRequest')
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Détails de la demande #{{ $request->code }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <a href=""
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
                        </a>
                        <a href="{{ route('personal.dashboard') }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10M3 14h10m5-4v8m-9-6h10M3 10l5 5m0 0l5-5" />
                            </svg>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </x-slot>

            <div class="py-8">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <nav class="text-sm text-gray-500 flex items-center mb-5">
                        <a href="{{ route('personal.dashboard') }}" class="hover:underline">Dashboard</a>
                        <span class="mx-2">/</span>
                        @if (url()->previous() !== url()->current())
                            <a href="{{ url()->previous() }}" class="hover:underline">Page précédente</a>
                        @endif
                        <span class="mx-2">/</span>
                        <span class="text-gray-700 font-semibold">Détails de la demande</span>
                    </nav>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour : {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <div class="lg:col-span-1 space-y-6">
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identité</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">{{ $request->lastname }} {{ $request->firstname }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de jeune fille</dt>
                                                <dd class="font-medium">{{ $request->maiden_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Signature</dt>
                                                <dd class="font-medium border-red-600">
                                                    @if ($request->pensioner_signature)
                                                        <img src="{{ asset('storage/' . $request->pensioner_signature) }}"
                                                            alt="Signature" class="w-full h-full object-cover mr-4">
                                                    @endif
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identifiants officiels</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->nif }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">{{ $request->ninu }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">{{ $request->address }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ $request->phone }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Email</dt>
                                                <dd class="font-medium">{{ $request->email }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <div class="lg:col-span-2 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant du transfert</dt>
                                            <dd class="text-2xl font-bold text-blue-600">{{ $request->amount }} HTG</dd>
                                        </div>
                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Date de la demande</dt>
                                            <dd class="font-medium">{{ $request->request_date }}</dd>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Calendrier fiscal</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Année fiscale</dt>
                                                <dd class="font-medium">{{ $request->fiscal_year }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Mois de début</dt>
                                                <dd class="font-medium">{{ $request->start_month }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Contexte du transfert</h3>
                                        <div>
                                            <dt class="text-sm text-gray-500">Raison du transfert</dt>
                                            <dd class="font-medium mt-1 text-gray-700 leading-relaxed">
                                                {{ $request->transfer_reason }}
                                            </dd>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Métadonnées</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Dernière mise à jour</dt>
                                                <dd class="font-medium">{{ $request->updated_at->format('d/m/Y H:i') }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- History -->
                        <div class="mt-8 px-6">
                            <h3 class="ml-1 text-xl font-semibold mb-4 text-gray-800">Historique de la demande</h3>

                            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                                @forelse($requestHistories as $history)
                                    <div class="p-4 border-b border-gray-100 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ $history->event_type }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $history->event_date }}
                                                    </span>
                                                </div>

                                                {{--                                             @if ($history->request_data)
                                                                        <div class="text-sm text-gray-600 mt-1">
                                                                            @foreach (json_decode($history->request_data, true) as $key => $value)
                                                                                <p class="break-words">
                                                                                    <span
                                                                                        class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                                                </p>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif --}}
                                            </div>

                                            <div class="text-right">
                                                <p class="text-sm text-gray-500">
                                                    @if ($history->creator)
                                                        Par {{ $history->creator->name }}
                                                    @else
                                                        Système
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    #{{ $history->request_type }}
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
                </div>
            </div>
        @break

        @case('paymentStopRequest')
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Détails de la demande #{{ $request->code }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <a href=""
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
                        </a>
                        <a href="{{ route('personal.dashboard') }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10M3 14h10m5-4v8m-9-6h10M3 10l5 5m0 0l5-5" />
                            </svg>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </x-slot>

            <div class="py-8">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <nav class="text-sm text-gray-500 flex items-center mb-5">
                        <a href="{{ route('personal.dashboard') }}" class="hover:underline">Dashboard</a>
                        <span class="mx-2">/</span>
                        @if (url()->previous() !== url()->current())
                            <a href="{{ url()->previous() }}" class="hover:underline">Page précédente</a>
                        @endif
                        <span class="mx-2">/</span>
                        <span class="text-gray-700 font-semibold">Détails de la demande</span>
                    </nav>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour : {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <div class="lg:col-span-1 space-y-6">
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identité</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom complet</dt>
                                                <dd class="font-medium">{{ $request->lastname }} {{ $request->firstname }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Nom de jeune fille</dt>
                                                <dd class="font-medium">{{ $request->maiden_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Signature</dt>
                                                <dd class="font-medium">
                                                    @if ($request->pensioner_signature)
                                                        <img src="{{ asset('storage/' . $request->pensioner_signature) }}"
                                                            alt="Signature" class="w-full h-full object-cover mr-4">
                                                    @endif
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identifiants officiels</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->nif }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">NINU</dt>
                                                <dd class="font-medium">{{ $request->ninu }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse</dt>
                                                <dd class="font-medium">{{ $request->address }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ $request->phone }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Email</dt>
                                                <dd class="font-medium">{{ $request->email }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <div class="lg:col-span-2 space-y-6">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant du transfert</dt>
                                            <dd class="text-2xl font-bold text-blue-600">{{ $request->amount }} HTG</dd>
                                        </div>
                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Date de la demande</dt>
                                            <dd class="font-medium">{{ $request->request_date }}</dd>
                                        </div>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Calendrier fiscal</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Année fiscale</dt>
                                                <dd class="font-medium">{{ $request->fiscal_year }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Mois de début</dt>
                                                <dd class="font-medium">{{ $request->start_month }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Métadonnées</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Code de la demande</dt>
                                                <dd class="font-medium">#{{ $request->code }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Dernière mise à jour</dt>
                                                <dd class="font-medium">{{ $request->updated_at->format('d/m/Y H:i') }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- History -->
                        <div class="mt-8 px-6">
                            <h3 class="ml-1 text-xl font-semibold mb-4 text-gray-800">Historique de la demande</h3>

                            <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                                @forelse($requestHistories as $history)
                                    <div class="p-4 border-b border-gray-100 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span class="text-sm font-medium text-gray-700">
                                                        {{ $history->event_type }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $history->event_date }}
                                                    </span>
                                                </div>

                                                {{--                                             @if ($history->request_data)
                                                                        <div class="text-sm text-gray-600 mt-1">
                                                                            @foreach (json_decode($history->request_data, true) as $key => $value)
                                                                                <p class="break-words">
                                                                                    <span
                                                                                        class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                                    {{ is_array($value) ? json_encode($value) : $value }}
                                                                                </p>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif --}}
                                            </div>

                                            <div class="text-right">
                                                <p class="text-sm text-gray-500">
                                                    @if ($history->creator)
                                                        Par {{ $history->creator->name }}
                                                    @else
                                                        Système
                                                    @endif
                                                </p>
                                                <p class="text-xs text-gray-400">
                                                    #{{ $history->request_type }}
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
                </div>
            </div>
        @break

        @case('existenceProofRequest')
            <x-slot name="header">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        Détails de la demande #{{ $request->code }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <a href=""
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
                        </a>
                        <a href="{{ route('personal.dashboard') }}"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h10M3 14h10m5-4v8m-9-6h10M3 10l5 5m0 0l5-5" />
                            </svg>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </x-slot>

            <div class="py-8">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <nav class="text-sm text-gray-500 flex items-center mb-5">
                        <a href="{{ route('personal.dashboard') }}" class="hover:underline">Dashboard</a>
                        <span class="mx-2">/</span>
                        @if (url()->previous() !== url()->current())
                            <a href="{{ url()->previous() }}" class="hover:underline">Page précédente</a>
                        @endif
                        <span class="mx-2">/</span>
                        <span class="text-gray-700 font-semibold">Détails de la demande de preuve d'existence </span>
                    </nav>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="mb-6 p-4 rounded-lg {{ App\Models\Status::getStatusStyle($request->status->name) }}">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <span class="font-semibold">Statut actuel :</span>
                                        {{ $request->status->name }}
                                    </div>
                                    <span class="text-sm">
                                        Dernière mise à jour : {{ $request->updated_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                                <!-- Left Column -->
                                <div class="lg:col-span-1 space-y-6">
                                    <!-- Personal Information -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Informations personnelles</h3>
                                        <div class="flex items-center mb-4">
                                            @if ($request->profile_photo)
                                                <img src="{{ asset('storage/' . $request->profile_photo) }}"
                                                    alt="Photo de profil" class="w-20 h-20 rounded-full object-cover mr-4">
                                            @endif
                                            <div>
                                                <p class="font-medium text-lg">{{ $request->lastname }}
                                                    {{ $request->firstname }}</p>
                                                <p class="text-sm text-gray-500">Né(e) le
                                                    {{ $request->birth_date }}</p>
                                            </div>
                                        </div>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Genre</dt>
                                                <dd class="font-medium">{{ $request->gender->name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">État civil</dt>
                                                <dd class="font-medium">{{ $request->civilStatus->name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Signature</dt>
                                                <dd class="font-medium">
                                                    @if ($request->pensioner_signature)
                                                        <img src="{{ asset('storage/' . $request->pensioner_signature) }}"
                                                            alt="Signature" class="w-full h-full object-cover mr-4">
                                                    @endif
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Official IDs -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Identifiants officiels</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro d'identification</dt>
                                                <dd class="font-medium">{{ $request->id_number }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">NIF</dt>
                                                <dd class="font-medium">{{ $request->nif }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Contact Information -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Coordonnées</h3>
                                        <dl class="space-y-3">
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse physique</dt>
                                                <dd class="font-medium">{{ $request->address }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Adresse postale</dt>
                                                <dd class="font-medium">{{ $request->postal_address }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Téléphone</dt>
                                                <dd class="font-medium">{{ $request->phone }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="lg:col-span-2 space-y-6">
                                    <!-- Pension Summary -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="p-4 bg-blue-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Montant</dt>
                                            <dd class="text-2xl font-bold text-blue-600">
                                                {{ number_format($request->pension_amount, 0, ',', ' ') }} HTG</dd>
                                        </div>
                                        <div class="p-4 bg-indigo-50 rounded-lg">
                                            <dt class="text-sm text-gray-500">Date de la demande</dt>
                                            <dd class="font-medium">{{ $request->created_at->format('d/m/Y H:i') }}</dd>
                                        </div>
                                    </div>

                                    <!-- Pension Details -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Détails</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Nature</dt>
                                                <dd class="font-medium">{{ $request->pensionCategory->name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Date de début</dt>
                                                <dd class="font-medium">{{ $request->pension_start_date }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Date de fin</dt>
                                                <dd class="font-medium">
                                                    {{ $request->pension_end_date ? $request->pension_end_date : 'N/A' }}
                                                </dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Date de derniere preuve/mandat</dt>
                                                <dd class="font-medium">
                                                    {{ $request->last_proof_mandate_date ? $request->last_proof_mandate_date : 'N/A' }}
                                                </dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <!-- Monitor Information -->
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Information de suivi</h3>
                                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-sm text-gray-500">Numéro de monitor</dt>
                                                <dd class="font-medium">{{ $request->monitor_number }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-sm text-gray-500">Date de monitorat</dt>
                                                <dd class="font-medium">{{ $request->monitor_date }}</dd>
                                            </div>
                                        </dl>
                                    </div>

                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <h3 class="text-lg font-semibold mb-3 text-gray-700">Liste des dependants</h3>
                                        <div class="space-y-2">
                                            @forelse ($request->dependants as $dependant)
                                                <div class="bg-white p-3 rounded-md border border-gray-200">
                                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                                                        <div>
                                                            <div class="text-gray-500 font-medium">Nom</div>
                                                            <div class="text-gray-900">{{ $dependant->name }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-gray-500 font-medium">Relation</div>
                                                            <div class="text-gray-900">{{ $dependant->relation }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-gray-500 font-medium">Naissance</div>
                                                            <div class="text-gray-900">
                                                                {{ \Carbon\Carbon::parse($dependant->birth_date)->format('d/m/Y') }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="text-gray-500 font-medium">Genre</div>
                                                            <div class="text-gray-900">
                                                                @switch($dependant->gender_id)
                                                                    @case(1)
                                                                        Homme
                                                                    @break

                                                                    @case(2)
                                                                        Femme
                                                                    @break

                                                                    @default
                                                                        Autre
                                                                @endswitch
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @empty
                                                    <div class="text-gray-500 py-2">
                                                        Aucun dependant
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- History -->
                            <div class="mt-8 px-6">
                                <h3 class="ml-1 text-xl font-semibold mb-4 text-gray-800">Historique de la demande</h3>

                                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                                    @forelse($requestHistories as $history)
                                        <div class="p-4 border-b border-gray-100 last:border-b-0">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1">
                                                    <div class="flex items-center gap-3 mb-2">
                                                        <span class="text-sm font-medium text-gray-700">
                                                            {{ $history->event_type }}
                                                        </span>
                                                        <span class="text-xs text-gray-500">
                                                            {{ $history->event_date }}
                                                        </span>
                                                    </div>

                                                    {{--                                             @if ($history->request_data)
                                                                            <div class="text-sm text-gray-600 mt-1">
                                                                                @foreach (json_decode($history->request_data, true) as $key => $value)
                                                                                    <p class="break-words">
                                                                                        <span
                                                                                            class="font-medium">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                                                        {{ is_array($value) ? json_encode($value) : $value }}
                                                                                    </p>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif --}}
                                                </div>

                                                <div class="text-right">
                                                    <p class="text-sm text-gray-500">
                                                        @if ($history->creator)
                                                            Par {{ $history->creator->name }}
                                                        @else
                                                            Système
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-gray-400">
                                                        #{{ $history->request_type }}
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
                    </div>
                @break

                @default
            @endswitch
    </x-app-layout>
