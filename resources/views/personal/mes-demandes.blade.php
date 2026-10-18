<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Mes demandes
            </h2>
            <p class="text-sm text-gray-500 mt-0.5 hidden sm:block">
                Suivi de l’ensemble de vos dossiers déposés en ligne
            </p>
        </div>
    </x-slot>

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
        $totalCount = $demandes->count();
        $draftCount = $demandes->filter(fn ($d) => $d->isDraft())->count();
        $activeCount = $totalCount - $draftCount;
    @endphp

    <x-portal-page bottom="pb-6"
         x-data="{ open: false, deleteUrl: '', title: '' }"
         @open-delete-modal.window="open = true; deleteUrl = $event.detail.url; title = $event.detail.title">

        <header class="dossier-header px-5 py-5 sm:px-6 mb-6 border-l-4 border-l-[#173052]">
            <p class="dossier-kicker">Mon espace</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Mes demandes</h1>
            <p class="mt-2 text-sm text-gray-600">Suivi de l’ensemble de vos dossiers déposés en ligne.</p>
        </header>

        @if(auth()->user()?->isProvisionnel())
            <div class="mb-6 dossier-notice dossier-notice--amber p-4 sm:p-5 text-sm text-amber-950 bg-amber-50/80">
                <p class="font-semibold flex items-center gap-2">
                    <i class="fas fa-hourglass-half text-amber-600"></i>
                    Compte provisoire
                </p>
                @if(auth()->user()->demandeCreationCompte)
                    <p class="mt-1 text-amber-900/90">Suivez l’état de votre demande de création de compte.</p>
                @endif
                <div class="mt-3 flex flex-wrap gap-3">
                    <a href="{{ route('compte.provisoire') }}"
                       class="inline-flex items-center gap-1.5 font-semibold text-[#173052] hover:underline">
                        Espace provisoire <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    @if(auth()->user()->demandeCreationCompte)
                        <a href="{{ route('compte.demande.show') }}"
                           class="inline-flex items-center gap-1.5 font-semibold text-[#173052] hover:underline">
                            Ma demande de compte <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="dossier-stat">
                <p class="dossier-stat__label">Total</p>
                <p class="dossier-stat__value text-2xl">{{ $totalCount }}</p>
            </div>
            <div class="dossier-stat border-l-4 border-l-[#173052]">
                <p class="dossier-stat__label">En cours / soumises</p>
                <p class="dossier-stat__value text-2xl">{{ $activeCount }}</p>
            </div>
            <div class="dossier-stat bg-gray-50/80">
                <p class="dossier-stat__label">Brouillons</p>
                <p class="dossier-stat__value text-2xl">{{ $draftCount }}</p>
            </div>
        </div>

        {{-- Filtres (type / étape) — désactivés pour le moment
        <form method="get" action="{{ route('personal.dashboard') }}"
              class="mb-4 flex flex-col lg:flex-row lg:items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex-1 min-w-[12rem]">
                <label for="filter-type" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Type</label>
                <select id="filter-type" name="type"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-[#173052] focus:ring-[#173052]/20">
                    <option value="">Tous les types</option>
                    @foreach ($typesDemandes as $typeDemande)
                        <option value="{{ $typeDemande->code }}" @selected(request('type') === $typeDemande->code)>
                            {{ $typeDemande->label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[12rem]">
                <label for="filter-status" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Étape</label>
                <select id="filter-status" name="step_code"
                        class="w-full rounded-lg border-gray-300 text-sm focus:border-[#173052] focus:ring-[#173052]/20">
                    <option value="">Toutes les étapes</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->code }}" @selected(request('step_code') === $status->code)>
                            {{ $status->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-[#173052] text-white hover:bg-[#1e3d6b] transition-colors">
                    <i class="fas fa-filter text-xs"></i>
                    Filtrer
                </button>
                @if(request()->hasAny(['type', 'step_code']))
                    <a href="{{ route('personal.dashboard') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                        Réinitialiser
                    </a>
                @endif
            </div>
        </form>
        --}}

        {{-- Table --}}
        <section class="dossier-card overflow-hidden">
            <div class="dossier-card-head">
                <h3>Liste des dossiers</h3>
                {{-- @if(request()->hasAny(['type', 'step_code']))
                    <span class="text-xs text-gray-500">{{ $totalCount }} résultat{{ $totalCount > 1 ? 's' : '' }}</span>
                @endif --}}
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] text-sm text-left">
                    <thead class="bg-[#173052] text-white text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-5 py-3.5 font-semibold">Dossier</th>
                            <th class="px-5 py-3.5 font-semibold">Type</th>
                            <th class="px-5 py-3.5 font-semibold">Dernière MAJ</th>
                            <th class="px-5 py-3.5 font-semibold">Soumission</th>
                            <th class="px-5 py-3.5 font-semibold">Expiration</th>
                            <th class="px-5 py-3.5 font-semibold">Statut</th>
                            <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($demandes as $demande)
                            <tr class="hover:bg-gray-50/90 transition-colors">
                                <td class="px-5 py-4 align-top">
                                    <p class="font-semibold text-gray-900 leading-snug">
                                        {{ $demande->title ?? '—' }}
                                    </p>
                                    @if($demande->isUrgent())
                                        <span class="inline-flex mt-1.5 text-[10px] font-bold uppercase tracking-wide text-red-700 bg-red-100 px-1.5 py-0.5 rounded">
                                            Urgent
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 align-top text-gray-700">
                                    {{ \App\Enums\TypeDemandeEnum::tryFrom((string) $demande->type)?->label() ?? $demande->type }}
                                </td>
                                <td class="px-5 py-4 align-top text-gray-600 whitespace-nowrap">
                                    {{ $demande->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 align-top text-gray-600 whitespace-nowrap">
                                    {{ $demande->created_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                                <td class="px-5 py-4 align-top text-gray-600 whitespace-nowrap">
                                    {{ $demande->expires_at ? $demande->expires_at->format('d/m/Y') : '—' }}
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $demande->statutBadgeClass() }}">
                                        {{ $demande->statutAffiche() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 align-top">
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        <a href="{{ route('personal.request.authenticated-user-request.show', $demande->id) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium bg-[#173052] text-white hover:bg-[#1e3d6b] transition-colors">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>

                                        @if ($demande->canBeEditedByUser())
                                            @php $editRoute = $editRoutes[$demande->type] ?? null; @endphp
                                            @if($editRoute)
                                                <a href="{{ route($editRoute, $demande->id) }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
                                                    <i class="fas fa-pen"></i>
                                                    {{ $demande->isDraft() ? 'Reprendre' : 'Modifier' }}
                                                </a>
                                            @endif
                                        @endif

                                        @if ($demande->isDraft())
                                            <button type="button"
                                                onclick="window.dispatchEvent(new CustomEvent('open-delete-modal', { detail: { url: '{{ route('demandes.destroy', $demande->id) }}', title: {{ Js::from($demande->title ?? '') }} } }))"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors">
                                                <i class="fas fa-trash-alt"></i> Supprimer
                                            </button>
                                        @endif

                                        @if ($demande->canBeCancelledByUser())
                                            <form method="POST" action="{{ route('demandes.rencontre.annuler', $demande) }}"
                                                  onsubmit="return confirm('Annuler ce rendez-vous ? Le créneau redeviendra disponible.')">
                                                @csrf
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium bg-red-600 text-white hover:bg-red-700 transition-colors">
                                                    <i class="fas fa-ban"></i> Annuler
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <div class="max-w-sm">
                                        <div class="w-14 h-14 mb-3 bg-gray-100 border border-gray-200 flex items-center justify-center">
                                            <i class="fas fa-inbox text-xl text-gray-300"></i>
                                        </div>
                                        <p class="font-semibold text-gray-700">Aucune demande</p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            Vos demandes déposées apparaîtront ici.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Delete modal --}}
        <div x-show="open" x-cloak
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6" @click.stop>
                <div class="flex items-start gap-4 mb-6">
                    <div class="flex-shrink-0 w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-trash-alt text-red-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Supprimer la demande</h3>
                        <p class="text-sm text-gray-500 mt-1">Action irréversible — fichiers inclus.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mb-6">
                    Dossier : <strong x-text="title"></strong>
                </p>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false"
                            class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium">
                        Annuler
                    </button>
                    <form :action="deleteUrl" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium">
                            Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </x-portal-page>
</x-app-layout>
