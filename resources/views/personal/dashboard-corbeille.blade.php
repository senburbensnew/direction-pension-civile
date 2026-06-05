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
                                            {{ ucfirst(str_replace('_', ' ', $request->status->code)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a class="text-blue-600 hover:text-blue-900 mr-2"
                                            href="{{ route('personal.request.show', $request->id) }}">
                                            Détails
                                        </a>
                                        @if ($request->status === 'pending')
                                            <button type="button"
                                                onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: '{{ route('demandes.destroy', $request->id) }}', title: '{{ addslashes($request->code ?? '') }}' } }))"
                                                class="text-red-600 hover:text-red-900 text-sm">
                                                Annuler
                                            </button>
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

{{-- Delete confirmation modal --}}
<div x-data="{ open: false, deleteUrl: '', title: '' }"
     x-on:open-delete-modal.window="open = true; deleteUrl = $event.detail.url; title = $event.detail.title"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50">
    <div class="bg-white w-full max-w-sm rounded-lg shadow-xl p-6" @click.stop>
        <div class="flex items-center gap-3 mb-4">
            <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900">Confirmer l'action</h3>
        </div>
        <p class="text-sm text-gray-600 mb-1">Demande : <strong x-text="title"></strong></p>
        <p class="text-sm text-gray-600 mb-6">
            Êtes-vous sûr de vouloir annuler cette demande ? Cette action est <strong>irréversible</strong>.
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="open = false"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm transition-colors">
                Retour
            </button>
            <form :action="deleteUrl" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm transition-colors">
                    Confirmer l'annulation
                </button>
            </form>
        </div>
    </div>
</div>
</x-app-layout>
