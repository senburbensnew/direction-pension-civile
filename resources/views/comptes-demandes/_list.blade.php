@php
    $routePrefix = $routePrefix ?? 'formalites';
    $filtre = $filtre ?? 'en_attente';
@endphp

<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Demandes de création de compte</h1>
        <div class="mt-3 flex flex-wrap gap-2 text-xs">
            @foreach(['en_attente' => 'En attente', 'acceptees' => 'Acceptées', 'refusees' => 'Refusées', 'tous' => 'Toutes'] as $key => $label)
                <a href="{{ route($routePrefix.'.comptes-demandes.index', ['filtre' => $key]) }}"
                   class="px-3 py-1 rounded-full {{ $filtre === $key ? 'bg-navy text-white' : 'bg-gray-100 text-gray-600' }}">{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-3 py-1 rounded-full">
        {{ $demandes->total() }} demande(s)
    </span>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
        {{ session('error') }}
    </div>
@endif

<form method="GET" class="flex flex-wrap gap-2">
    <input type="hidden" name="filtre" value="{{ $filtre }}">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Référence, NIF, code pension, e-mail…"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-blue-500">
    <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white text-sm rounded-lg">Filtrer</button>
</form>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Référence</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Demandeur</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Identifiants</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Téléphone</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Déposée</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Statut</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($demandes as $d)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $d->code }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $d->displayName() }}</p>
                        @if($d->is_mineur)
                            <p class="text-xs text-amber-600">Pensionné mineur</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-600">
                        <p>{{ $d->nif ?: '—' }}</p>
                        @if($d->pension_code)
                            <p>{{ $d->pension_code }}</p>
                        @endif
                        <p class="text-gray-400">{{ $d->email ?: '—' }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $d->telephone ?: '—' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $d->created_at?->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex text-xs font-semibold px-2.5 py-1 rounded-full {{ $d->statusBadgeClass() }}">{{ $d->statusLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route($routePrefix.'.comptes-demandes.show', $d) }}" class="text-navy font-semibold text-sm hover:underline">Ouvrir</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500">Aucune demande pour ce filtre.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div>{{ $demandes->links() }}</div>
