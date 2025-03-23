<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Détails de la demande #{{ $request->code }}
            </h2>
            <div class="flex items-center space-x-4">
                {{-- <a href="{{ route('pensionnaire.request.edit', $request->id) }}" --}}
                <a href=""
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md flex items-center transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 114.95 0 2.5 2.5 0 01-4.95 0M12 15v3m0 0h3m-3 0H9m6-12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Modifier
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
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <!-- Status Banner -->
                    <div
                        class="mb-6 p-4 rounded-lg {{ App\Models\BankTransferRequests::getStatusStyle($request->status) }}">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-semibold">Statut actuel :</span>
                                {{ App\Models\BankTransferRequests::getStatusLabels()[$request->status] }}
                            </div>
                            <span class="text-sm">
                                Dernière mise à jour : {{ $request->updated_at->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>

                    <!-- Request Details Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Personal Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Informations personnelles</h3>
                            <div>
                                <label class="text-sm text-gray-500">Nom complet</label>
                                <p class="font-medium">{{ $request->full_name }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">NIF</label>
                                <p class="font-medium">{{ $request->nif }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Date de naissance</label>
                                <p class="font-medium">{{ $request->birth_date->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <!-- Bank Information -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Coordonnées bancaires</h3>
                            <div>
                                <label class="text-sm text-gray-500">Nom de la banque</label>
                                <p class="font-medium">{{ $request->bank_name }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Numéro de compte</label>
                                <p class="font-medium">{{ $request->account_number }}</p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Titulaire du compte</label>
                                <p class="font-medium">{{ $request->account_name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Historique du dossier</h3>
                        <div class="border-l-2 border-gray-200 pl-4 space-y-4">
                            @foreach ($request->history as $event)
                                <div class="relative pb-4">
                                    <div class="absolute w-3 h-3 bg-gray-200 rounded-full -left-[9px]"></div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ $event->title }}</p>
                                        <p class="text-sm text-gray-500">{{ $event->description }}</p>
                                        <time
                                            class="text-xs text-gray-400">{{ $event->created_at->format('d/m/Y H:i') }}</time>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
