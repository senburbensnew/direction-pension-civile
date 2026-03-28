<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Suivi des demandes') }}
        </h2>
    </x-slot>
    <div class="px-5 py-8">
        {{-- ===================== MES DEMANDES ===================== --}}
        <fieldset class="border-2 border-gray-200 rounded-lg mt-8 pl-3 ml-1 mr-1">
            <legend class="text-lg font-semibold ml-4 px-4 text-gray-700 bg-white rounded-full border shadow-sm">
                <i class="fas fa-tachometer-alt mr-2 text-gray-400"></i> Mes demandes
            </legend>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="py-6 px-2">
                <div class="mx-auto sm:px-6 lg:px-8">
                    <form method="GET" action="{{ route('personal.dashboard') }}" class="flex flex-wrap gap-4 mb-6 items-end">
                        {{-- Status filter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                            <select name="status_id" class="mt-1 inline-block w-auto min-w-[12rem] rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Tous les statuts</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Type demande filter --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande</label>
                            <select name="type" class="mt-1 inline-block w-auto min-w-[12rem] rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Tous les types</option>
                                @foreach ($typesDemandes as $type)
                                    <option value="{{ $type->code }}"
                                        {{ request('type') == $type->code ? 'selected' : '' }}>
                                        {{ $type->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-end">
                            <button type="submit"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Filtrer
                            </button>
                        </div>

                    </form>

                    <table class="w-full text-sm text-left text-body">
                        <thead class="bg-gray-100 border-b border-t border-default-medium">
                            <tr>
                                <th class="px-6 py-3 text-blue-900 font-bold">Details</th>
                                {{-- <th class="px-6 py-3 text-blue-900 font-bold">Code</th> --}}
                                <th class="px-6 py-3 text-blue-900 font-bold">Categorie</th>
                                <th class="px-6 py-3 text-blue-900 font-bold">Derniere mise a jour</th>
                                <th class="px-6 py-3 text-blue-900 font-bold">Date de soumission</th>
                                <th class="px-6 py-3 text-blue-900 font-bold">Date d'expiration</th>
                                <th class="px-6 py-3 text-blue-900 font-bold">Statut</th>
                                <th class="px-6 py-3 text-blue-900 font-bold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($demandes as $demande)
                                <tr class="bg-neutral-primary-soft border-default hover:bg-neutral-secondary-medium">
                                    <td class="px-6 py-4 text-blue-900 font-bold">{{ $demande->title ?? '--' }}</td>
                                    {{-- <td class="px-6 py-4">{{ $demande->code }}</td> --}}
                                    <td class="px-6 py-4">{{ $demande->type }}</td>
                                    <td class="px-6 py-4">{{ $demande->updated_at }}</td>
                                    <td class="px-6 py-4">{{ $demande->submitted_at ?? '--' }}</td>
                                    <td class="px-6 py-4">{{ $demande->expires_at ?? '--' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-sm rounded-full
                                            @switch($demande->status->code)

                                                @case('BROUILLON')
                                                    bg-gray-100 text-gray-800
                                                    @break

                                                @case('SOUMISE')
                                                    bg-blue-100 text-blue-800
                                                    @break

                                                @case('EN_ATTENTE')
                                                    bg-yellow-100 text-yellow-800
                                                    @break

                                                @case('EN_COURS')
                                                    bg-indigo-100 text-indigo-800
                                                    @break

                                                @case('APPROUVEE')
                                                    bg-green-100 text-green-800
                                                    @break

                                                @case('FINALISEE')
                                                    bg-green-200 text-green-900
                                                    @break

                                                @case('REJETEE')
                                                    bg-red-100 text-red-800
                                                    @break

                                                @case('ANNULEE')
                                                    bg-gray-200 text-gray-600
                                                    @break

                                                @default
                                                    bg-gray-100 text-gray-800
                                            @endswitch
                                        ">
                                            {{ $demande->status->label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('personal.request.authenticated-user-request.show', $demande->id) }}"
                                            class="bg-yellow-500 text-white px-2 py-1 rounded text-sm hover:bg-yellow-600">
                                                voir
                                            </a>

                                            @if ($demande->canBeEditedByUser())
                                                @php
                                                    $editRoutes = [
                                                        'DEMANDE_VIREMENT_BANCAIRE' => 'demandes.virements.create',
                                                        'DEMANDE_ATTESTATION'       => 'demandes.attestations.create',
                                                        'DEMANDE_TRANSFERT_CHEQUE'  => 'demandes.transfert-cheque.create',
                                                        'DEMANDE_ARRET_PAIEMENT'    => 'demandes.arret-paiement.create',
                                                        'DEMANDE_REINSERTION'       => 'demandes.demande-reinsertion.create',
                                                        'DEMANDE_ARRET_VIREMENT'    => 'demandes.demande-arret-virement.create',
                                                        'DEMANDE_PREUVE_EXISTENCE'  => 'demandes.preuve-existence.create',
                                                        'DEMANDE_ETAT_CARRIERE'     => 'demandes.demande-etat-carriere.create',
                                                        'DEMANDE_ADHESION'          => 'demandes.demande-adhesion.create',
                                                        'DEMANDE_PENSION'           => 'demandes.demande-pension-standard.create',
                                                        'DEMANDE_PENSION_REVERSION' => 'demandes.demande-pension-reversion.create',
                                                    ];
                                                    $editRoute = $editRoutes[$demande->type] ?? null;
                                                @endphp
                                                @if($editRoute)
                                                    <a href="{{ route($editRoute, $demande->id) }}" class="bg-blue-500 text-white px-2 py-1 rounded text-sm hover:bg-blue-600">
                                                        {{ $demande->isDraft() ? 'reprendre' : 'modifier' }}
                                                    </a>
                                                @endif
                                            @endif

                                            @if ($demande->isDraft())
                                                <button type="button"
                                                    onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: '{{ route('demandes.destroy', $demande->id) }}', title: '{{ addslashes($demande->title) }}' } }))"
                                                    class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600">
                                                    supprimer
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr class="bg-neutral-primary-soft hover:bg-neutral-secondary-medium">
                                    <td colspan="8" class="p-4 text-center align-middle text-gray-500">
                                        Aucune demande
                                    </td>
                                </tr>
                                @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </fieldset>
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
            <h3 class="text-lg font-semibold text-gray-900">Supprimer la demande</h3>
        </div>
        <p class="text-sm text-gray-600 mb-1">Demande : <strong x-text="title"></strong></p>
        <p class="text-sm text-gray-600 mb-6">
            Êtes-vous sûr de vouloir supprimer cette demande ? Cette action est <strong>irréversible</strong> et supprimera tous les fichiers associés.
        </p>
        <div class="flex justify-end gap-3">
            <button type="button" @click="open = false"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md text-sm transition-colors">
                Annuler
            </button>
            <form :action="deleteUrl" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md text-sm transition-colors">
                    Supprimer définitivement
                </button>
            </form>
        </div>
    </div>
</div>

</x-app-layout>
