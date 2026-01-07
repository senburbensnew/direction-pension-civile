<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi de mes demandes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 flex items-center mb-5">
                <a href="{{ route('personal.cart') }}" class="hover:underline">Corbeille</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700 font-semibold">{{ $type }}</span>
            </nav>
            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8"> <!-- Changé à 5 colonnes -->
                <!-- En attente -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\Status::STATUS_PENDING }}
                    </div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                </div>

                <!-- En cours -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\Status::STATUS_IN_PROGRESS }}
                    </div>
                    <div class="text-3xl font-bold text-purple-600">{{ $stats['in_progress'] }}</div>
                </div>

                <!-- Rejeté -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\Status::STATUS_REJECTED }}
                    </div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                </div>

                <!-- Annulé -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\Status::STATUS_CANCELED }}
                    </div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['canceled'] }}</div>
                </div>

                <!-- Approuvé -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\Status::STATUS_APPROVED }}
                    </div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['approved'] }}</div>
                </div>

                <!-- Traité -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\Status::STATUS_COMPLETED }}
                    </div>
                    <div class="text-3xl font-bold text-green-600">{{ $stats['completed'] }}</div>
                </div>
            </div>
            <!-- Liste des demandes -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Demande
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($requests as $request)
                                <tr>
                                    <td class="px-6 py-4">{{ $request->code }}</td>
                                    <td class="px-6 py-4">{{ $type }}</td>
                                    <td class="px-6 py-4">{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="px-2 py-1 text-sm rounded-full 
                                    @switch($request->status->name)
                                        @case('pending')
                                            bg-yellow-100 text-yellow-800
                                            @break
                                        @case('in_progress')
                                            bg-blue-100 text-blue-800
                                            @break
                                            @case('completed')
                                                 bg-green-100 text-green-800
                                                 @break
                                             @default
                                                 bg-gray-100 text-gray-800
                                         @endswitch">
                                            {{ ucfirst(str_replace('_', ' ', $request->status->name)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a class="text-blue-600 hover:text-blue-900 mr-2"
                                            href="{{ route('personal.request.show', ['id' => $request->id, 'requestType' => $requestType]) }}">
                                            Détails
                                        </a>
                                        @if ($request->status === 'pending')
                                            <form action="{{ route('personal.request.cancel', $request->id) }}"
                                                method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Annuler
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        <div class="py-8">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
                                            </svg>
                                            <h3 class="mt-4 text-sm font-medium text-gray-900">Aucune demande trouvée
                                            </h3>
                                            <p class="mt-1 text-sm text-gray-500">Commencez par créer une nouvelle
                                                demande.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Show pagination only if there are results -->
                    @if ($requests->isNotEmpty())
                        <div class="mt-4">
                            {{ $requests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
