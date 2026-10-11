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

@if(session('success'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        <i class="fas fa-check-circle mt-0.5 text-green-600" aria-hidden="true"></i>
        <p>{{ session('success') }}</p>
    </div>
@endif
@if(session('error'))
    <div class="mb-6 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <i class="fas fa-exclamation-circle mt-0.5 text-red-500" aria-hidden="true"></i>
        <p>{{ session('error') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <div class="rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Vue</p>
        <p class="text-lg font-bold text-[#173052] mt-1">{{ $filtreLabels[$filtre] ?? $filtre }}</p>
    </div>
    <div class="rounded-xl border border-sky-200 bg-sky-50/80 px-5 py-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-sky-700">Résultats</p>
        <p class="text-2xl font-bold text-sky-900 mt-1">{{ $demandes->total() }}</p>
    </div>
</div>

<div class="mb-4 flex flex-wrap gap-2">
    @foreach($filtreLabels as $key => $label)
        <a href="{{ route($routePrefix.'.comptes-demandes.index', array_merge(request()->only('q'), ['filtre' => $key])) }}"
           class="inline-flex items-center px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors {{ $filtre === $key ? 'bg-[#173052] text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:border-[#173052]/30 hover:text-[#173052]' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<form method="GET"
      class="mb-6 flex flex-col sm:flex-row sm:items-end gap-3 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
    <input type="hidden" name="filtre" value="{{ $filtre }}">
    <div class="flex-1 min-w-[12rem]">
        <label for="comptes-q" class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Recherche</label>
        <input id="comptes-q" type="text" name="q" value="{{ request('q') }}"
               placeholder="Référence, NIF, code pension, e-mail…"
               class="w-full rounded-lg border-gray-300 text-sm focus:border-[#173052] focus:ring-[#173052]/20">
    </div>
    <button type="submit"
            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg text-sm font-medium bg-[#173052] text-white hover:bg-[#1e3d6b] transition-colors">
        <i class="fas fa-search text-xs"></i>
        Rechercher
    </button>
    @if(request()->filled('q'))
        <a href="{{ route($routePrefix.'.comptes-demandes.index', ['filtre' => $filtre]) }}"
           class="inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-medium border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
            Effacer
        </a>
    @endif
</form>

<div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/80">
        <h3 class="text-sm font-semibold text-gray-700">
            <i class="fas fa-user-plus text-gray-400 mr-2"></i>
            Liste des demandes
        </h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[960px] text-sm text-left">
            <thead class="bg-[#173052] text-white text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-5 py-3.5 font-semibold">Dossier</th>
                    <th class="px-5 py-3.5 font-semibold">Demandeur</th>
                    <th class="px-5 py-3.5 font-semibold">Identifiants</th>
                    <th class="px-5 py-3.5 font-semibold">Téléphone</th>
                    <th class="px-5 py-3.5 font-semibold">Déposée</th>
                    <th class="px-5 py-3.5 font-semibold">Statut</th>
                    <th class="px-5 py-3.5 font-semibold text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($demandes as $d)
                    <tr class="hover:bg-gray-50/80 transition-colors">
                        <td class="px-5 py-4 align-top text-gray-800">{{ $d->libellePublic() }}</td>
                        <td class="px-5 py-4 align-top">
                            <p class="font-medium text-gray-900">{{ $d->displayName() }}</p>
                            @if($d->is_mineur)
                                <p class="text-xs text-amber-700 mt-0.5">Pensionné mineur</p>
                            @endif
                        </td>
                        <td class="px-5 py-4 align-top text-xs text-gray-600 space-y-0.5">
                            <p>{{ $d->nif ?: '—' }}</p>
                            @if($d->pension_code)
                                <p>{{ $d->pension_code }}</p>
                            @endif
                            <p class="text-gray-400">{{ $d->email ?: '—' }}</p>
                        </td>
                        <td class="px-5 py-4 align-top text-gray-700 whitespace-nowrap">{{ $d->telephone ?: '—' }}</td>
                        <td class="px-5 py-4 align-top text-gray-500 whitespace-nowrap">{{ $d->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4 align-top">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $d->statusBadgeClass() }}">
                                {{ $d->statusLabel() }}
                            </span>
                        </td>
                        <td class="px-5 py-4 align-top text-right">
                            <a href="{{ route($routePrefix.'.comptes-demandes.show', $d) }}"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium bg-[#173052] text-white hover:bg-[#1e3d6b] transition-colors">
                                <i class="fas fa-eye"></i> Ouvrir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="max-w-sm mx-auto">
                                <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-inbox text-xl text-gray-300"></i>
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
</div>

@if($demandes->hasPages())
    <div class="mt-6">
        {{ $demandes->links() }}
    </div>
@endif
