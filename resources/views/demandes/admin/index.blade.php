@extends('layouts.admin')

@section('title', 'Gestion des dossiers')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Dossiers</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $demandes->total() }} dossier(s) au total</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Transferts en attente de réception ─────────────────────────────── --}}
    @php
        $pendingWorkflows = \App\Models\DemandeWorkflow::with(['demande', 'fromService', 'toService', 'user'])
            ->where('reception_status', 'pending')
            ->latest()
            ->get();
    @endphp

    @if($pendingWorkflows->isNotEmpty())
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4">
            <h2 class="text-sm font-semibold text-sky-800 mb-3 flex items-center gap-2">
                <i class="fas fa-clock text-sky-500"></i>
                Transferts en attente de réception ({{ $pendingWorkflows->count() }})
            </h2>
            <div class="space-y-2">
                @foreach($pendingWorkflows as $workflow)
                    <div class="bg-white border border-sky-100 rounded-lg px-4 py-3 flex flex-wrap items-center justify-between gap-3">
                        <div class="text-sm">
                            <a href="{{ route('admin.demandes.show', $workflow->demande) }}"
                               class="font-semibold text-gray-800 hover:underline">
                                #{{ $workflow->demande->code ?? $workflow->demande->id }}
                            </a>
                            <span class="text-gray-500 mx-1">·</span>
                            <span class="text-gray-600">
                                De <strong>{{ $workflow->fromService->nom ?? '—' }}</strong>
                                → <strong>{{ $workflow->toService->nom ?? '—' }}</strong>
                            </span>
                            <span class="text-gray-400 ml-2 text-xs">{{ $workflow->created_at->diffForHumans() }}</span>
                            @if($workflow->commentaire)
                                <p class="text-gray-500 text-xs mt-0.5 italic">{{ $workflow->commentaire }}</p>
                            @endif
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('admin.workflows.accepter', $workflow) }}">
                                @csrf
                                <button type="submit"
                                        class="bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                    <i class="fas fa-check mr-1"></i> Accepter
                                </button>
                            </form>
                            <button type="button"
                                    onclick="document.getElementById('refus-modal-{{ $workflow->id }}').classList.remove('hidden')"
                                    class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                                <i class="fas fa-times mr-1"></i> Refuser
                            </button>
                        </div>
                    </div>

                    {{-- Refus modal --}}
                    <div id="refus-modal-{{ $workflow->id }}"
                         class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
                        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                            <h3 class="font-semibold text-gray-800 mb-3">Refuser la réception</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                Dossier <strong>#{{ $workflow->demande->code ?? $workflow->demande->id }}</strong>
                                sera retourné à <strong>{{ $workflow->fromService->nom ?? '—' }}</strong>.
                            </p>
                            <form method="POST" action="{{ route('admin.workflows.refuser', $workflow) }}">
                                @csrf
                                <textarea name="motif" rows="3" placeholder="Motif du refus (optionnel)"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 mb-4"></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button"
                                            onclick="document.getElementById('refus-modal-{{ $workflow->id }}').classList.add('hidden')"
                                            class="text-gray-500 hover:text-gray-700 text-sm px-4 py-2">
                                        Annuler
                                    </button>
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                        Confirmer le refus
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Table des dossiers ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Référence</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Type</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Déposant</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Statut</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Service courant</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Date</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($demandes as $demande)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700">
                            {{ $demande->code ?? '#' . $demande->id }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ str_replace('_', ' ', $demande->type) }}
                        </td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ $demande->user?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($demande->status)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ \App\Models\Status::getStatusStyle($demande->status->code) }}">
                                    {{ $demande->status->label }}
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">
                            {{ $demande->currentService?->nom ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">
                            {{ $demande->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.demandes.show', $demande) }}"
                               class="text-indigo-600 hover:text-indigo-800 text-xs font-medium">
                                Voir →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Aucun dossier</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($demandes->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $demandes->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
