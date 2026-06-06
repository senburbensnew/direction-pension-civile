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

    {{-- ── Transferts en cours (monitoring, lecture seule) ──────────────────── --}}
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
                <span class="text-xs font-normal text-sky-600">— traités par les agents des services concernés</span>
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
                        <span class="text-xs bg-sky-100 text-sky-700 font-medium px-2.5 py-1 rounded-full flex items-center gap-1">
                            <i class="fas fa-hourglass-half text-xs"></i>
                            En attente
                        </span>
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
