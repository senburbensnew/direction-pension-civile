@php
    $routePrefix = $routePrefix ?? 'formalites';
    $filtre = $filtre ?? 'en_attente';
    $filtreLabels = [
        'en_attente' => 'En attente',
        'acceptees' => 'Acceptées',
        'refusees' => 'Refusées',
        'tous' => 'Toutes',
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="dossier-stat">
        <p class="dossier-stat__label">Vue</p>
        <p class="dossier-stat__value">{{ $filtreLabels[$filtre] ?? $filtre }}</p>
    </div>
    <div class="dossier-stat border-l-4 border-l-[#173052]">
        <p class="dossier-stat__label">Résultats</p>
        <p class="dossier-stat__value text-2xl">{{ $demandes->total() }}</p>
    </div>
</div>

<div class="mb-4 flex flex-wrap gap-2">
    @foreach($filtreLabels as $key => $label)
        <a href="{{ route($routePrefix.'.comptes-demandes.index', array_merge(request()->only('q'), ['filtre' => $key])) }}"
           @class([
               'dossier-filter',
               'dossier-filter--active' => $filtre === $key,
               'dossier-filter--idle' => $filtre !== $key,
           ])>
            {{ $label }}
        </a>
    @endforeach
</div>

<form method="GET" class="dossier-form-bar mb-6">
    <input type="hidden" name="filtre" value="{{ $filtre }}">
    <div class="flex-1 min-w-[12rem]">
        <label for="comptes-q" class="block text-xs font-medium uppercase tracking-wide text-gray-500 mb-1">Recherche</label>
        <input id="comptes-q" type="text" name="q" value="{{ request('q') }}"
               placeholder="Référence, NIF, code pension, e-mail…"
               class="dossier-field">
    </div>
    <button type="submit" class="dossier-btn-primary">
        <i class="fas fa-search text-xs" aria-hidden="true"></i>
        Rechercher
    </button>
    @if(request()->filled('q'))
        <a href="{{ route($routePrefix.'.comptes-demandes.index', ['filtre' => $filtre]) }}"
           class="dossier-btn-secondary">
            Effacer
        </a>
    @endif
</form>

<section class="dossier-card overflow-hidden">
    <div class="dossier-card-head">
        <h3>Liste des demandes</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="dossier-table w-full min-w-[960px] text-sm">
            <thead>
                <tr>
                    <th>Dossier</th>
                    <th>Demandeur</th>
                    <th>Identifiants</th>
                    <th>Téléphone</th>
                    <th>Déposée</th>
                    <th>Statut</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($demandes as $d)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="text-gray-800">{{ $d->libellePublic() }}</td>
                        <td>
                            <p class="font-medium text-gray-900">{{ $d->displayName() }}</p>
                            @if($d->is_mineur)
                                <p class="text-xs text-amber-700 mt-0.5">Pensionné mineur</p>
                            @endif
                        </td>
                        <td class="text-xs text-gray-600 space-y-0.5">
                            <p>{{ $d->nif ?: '—' }}</p>
                            @if($d->pension_code)
                                <p>{{ $d->pension_code }}</p>
                            @endif
                            <p class="text-gray-400">{{ $d->email ?: '—' }}</p>
                        </td>
                        <td class="text-gray-700 whitespace-nowrap">{{ $d->telephone ?: '—' }}</td>
                        <td class="text-gray-500 whitespace-nowrap">{{ $d->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $d->statusBadgeClass() }}">
                                {{ $d->statusLabel() }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route($routePrefix.'.comptes-demandes.show', $d) }}"
                               class="dossier-btn-primary text-xs px-2.5 py-1.5">
                                Ouvrir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="max-w-sm">
                                <div class="w-14 h-14 mb-3 bg-gray-100 flex items-center justify-center border border-gray-200">
                                    <i class="fas fa-inbox text-xl text-gray-300" aria-hidden="true"></i>
                                </div>
                                <p class="font-semibold text-gray-700">Aucune demande</p>
                                <p class="text-sm text-gray-500 mt-1">Aucun dossier pour ce filtre ou cette recherche.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($demandes->hasPages())
    <div class="mt-6">
        {{ $demandes->links() }}
    </div>
@endif
