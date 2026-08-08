<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $type }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <nav class="text-sm text-gray-500 flex items-center mb-5">
                <a href="{{ route('personal.cart') }}" class="hover:underline text-blue-600">Corbeille</a>
                <span class="mx-2">/</span>
                <span class="text-gray-700 font-semibold">{{ $type }}</span>
            </nav>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <p class="text-xs text-gray-500 mb-4">
                        Dossiers de votre service. Ouvrez un dossier pour le traiter selon le circuit défini.
                    </p>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">N° Demande</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Étape (circuit)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($requests as $req)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-mono text-sm font-medium text-gray-800">{{ $req->code }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ str_replace('_', ' ', $req->type) }}</td>
                                    <td class="px-6 py-4">
                                        @if($req->currentStep)
                                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ \App\Models\WorkflowStep::getStatusStyle($req->currentStep->code) }}">
                                                {{ $req->currentStep->nom }}
                                            </span>
                                            @if($req->is_urgent)
                                                <span class="ml-1 inline-flex px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-100 text-red-700">Urgent</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $req->submitted_at?->format('d/m/Y H:i') ?? $req->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4">
                                        <a class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-900 text-sm font-medium"
                                            href="{{ route('personal.request.show', $req->id) }}">
                                            Traiter
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </a>
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
                                            <h3 class="mt-4 text-sm font-medium text-gray-900">Aucun dossier dans ce répertoire</h3>
                                            <p class="mt-1 text-sm text-gray-500">
                                                <a href="{{ route('personal.cart') }}" class="text-blue-600 hover:underline">Retour à la corbeille</a>
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($requests->isNotEmpty())
                        <div class="mt-4">
                            {{ $requests->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
