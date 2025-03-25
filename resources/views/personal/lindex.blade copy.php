<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi de mes demandes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistiques -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8"> <!-- Changé à 5 colonnes -->
                <!-- En attente -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\BankTransferRequests::getStatusLabels()[App\Models\BankTransferRequests::STATUS_PENDING] }}
                    </div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</div>
                </div>

                <!-- En cours -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\BankTransferRequests::getStatusLabels()[App\Models\BankTransferRequests::STATUS_IN_PROGRESS] }}
                    </div>
                    <div class="text-3xl font-bold text-purple-600">{{ $stats['in_progress'] }}</div>
                </div>

                <!-- Rejeté -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\BankTransferRequests::getStatusLabels()[App\Models\BankTransferRequests::STATUS_REJECTED] }}
                    </div>
                    <div class="text-3xl font-bold text-red-600">{{ $stats['rejected'] }}</div>
                </div>

                <!-- Approuvé -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\BankTransferRequests::getStatusLabels()[App\Models\BankTransferRequests::STATUS_APPROVED] }}
                    </div>
                    <div class="text-3xl font-bold text-blue-600">{{ $stats['approved'] }}</div>
                </div>

                <!-- Traité -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <div class="text-gray-500">
                        {{ App\Models\BankTransferRequests::getStatusLabels()[App\Models\BankTransferRequests::STATUS_COMPLETED] }}
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
                            @forelse ($bankTransferRequests as $request)
                                <tr>
                                    <!-- Existing row content -->
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
                    @if ($bankTransferRequests->isNotEmpty())
                        <div class="mt-4">
                            {{ $bankTransferRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
