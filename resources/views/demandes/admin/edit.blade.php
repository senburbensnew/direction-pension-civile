@extends('layouts.admin')

@section('title', 'Dossier #' . ($demande->code ?? $demande->id))

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 flex items-center gap-1">
        <a href="{{ route('admin.demandes.index') }}" class="hover:underline">Dossiers</a>
        <span>/</span>
        <span class="text-gray-700 font-medium">{{ $demande->code ?? '#' . $demande->id }}</span>
    </nav>

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

    {{-- ── Provenance du dossier (visible Direction) ──────────────────────── --}}
    @role('direction')
    @php
        $lastIncoming = $demande->workflows()
            ->with(['fromService', 'toService', 'user'])
            ->where('reception_status', 'accepted')
            ->whereNotNull('from_service_id')
            ->latest()
            ->first();
    @endphp
    @if($lastIncoming)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-arrow-right text-amber-600 text-sm"></i>
            </div>
            <div class="text-sm leading-relaxed">
                <p class="font-semibold text-amber-900">
                    Dossier transmis par
                    <span class="text-amber-700">{{ $lastIncoming->fromService->nom }}</span>
                </p>
                <p class="text-amber-700 mt-0.5">
                    Déclenché par
                    <span class="font-medium">{{ $lastIncoming->user?->name ?? '—' }}</span>
                    le {{ $lastIncoming->created_at->format('d/m/Y à H:i') }}
                    <span class="text-amber-500 ml-1">({{ $lastIncoming->created_at->diffForHumans() }})</span>
                </p>
                @if($lastIncoming->commentaire)
                    <p class="text-amber-600 italic mt-1">"{{ $lastIncoming->commentaire }}"</p>
                @endif
            </div>
        </div>
    @endif
    @endrole

    {{-- ── Transfert en attente de réception ──────────────────────────────── --}}
    @php
        $pendingWorkflow = $demande->workflows()
            ->with(['fromService', 'toService', 'user'])
            ->where('reception_status', 'pending')
            ->latest()
            ->first();
    @endphp

    @if($pendingWorkflow)
        <div class="bg-sky-50 border-2 border-sky-300 rounded-xl p-5" x-data="{ showRefus: false }">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-paper-plane text-sky-600"></i>
                </div>
                <div class="flex-1">
                    <h3 class="font-semibold text-sky-900">Transfert en attente de réception</h3>
                    <p class="text-sm text-sky-700 mt-0.5">
                        Ce dossier a été transféré
                        @if($pendingWorkflow->fromService)
                            depuis <strong>{{ $pendingWorkflow->fromService->nom }}</strong>
                        @endif
                        vers <strong>{{ $pendingWorkflow->toService->nom ?? '—' }}</strong>
                        par <strong>{{ $pendingWorkflow->user?->name ?? '—' }}</strong>
                        <span class="text-sky-500 text-xs ml-1">({{ $pendingWorkflow->created_at->diffForHumans() }})</span>
                    </p>
                    @if($pendingWorkflow->commentaire)
                        <p class="text-sm text-sky-600 mt-1 italic">"{{ $pendingWorkflow->commentaire }}"</p>
                    @endif

                    <div class="flex flex-wrap gap-3 mt-4">
                        {{-- Accept --}}
                        <form method="POST" action="{{ route('admin.workflows.accepter', $pendingWorkflow) }}">
                            @csrf
                            <button type="submit"
                                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors flex items-center gap-2">
                                <i class="fas fa-check-circle"></i>
                                Confirmer la réception
                            </button>
                        </form>

                        {{-- Refuse --}}
                        <button type="button"
                                @click="showRefus = true"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors flex items-center gap-2">
                            <i class="fas fa-times-circle"></i>
                            Refuser le transfert
                        </button>
                    </div>

                    {{-- Refus form (inline) --}}
                    <div x-show="showRefus" x-cloak class="mt-4 bg-white border border-red-200 rounded-lg p-4">
                        <p class="text-sm font-semibold text-red-700 mb-2">
                            Le dossier sera retourné à {{ $pendingWorkflow->fromService->nom ?? 'l\'expéditeur' }}.
                        </p>
                        <form method="POST" action="{{ route('admin.workflows.refuser', $pendingWorkflow) }}">
                            @csrf
                            <textarea name="motif" rows="2" placeholder="Motif du refus (optionnel)"
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-300 mb-3"></textarea>
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                    Confirmer le refus
                                </button>
                                <button type="button" @click="showRefus = false"
                                        class="text-gray-500 hover:text-gray-700 text-sm px-4 py-2">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Info principale ─────────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-lg font-bold text-gray-800">
                    {{ str_replace('_', ' ', $demande->type ?? '—') }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5 font-mono">{{ $demande->code ?? '#' . $demande->id }}</p>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                @if($demande->is_urgent)
                    <span class="bg-red-100 text-red-700 text-xs font-semibold px-2 py-1 rounded-full">
                        <i class="fas fa-bolt mr-1"></i> Urgent
                    </span>
                @endif
                @if($demande->status)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                        {{ \App\Models\Status::getStatusStyle($demande->status->code) }}">
                        {{ $demande->status->label }}
                    </span>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="text-gray-500">Déposant</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $demande->user?->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Service courant</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $demande->currentService?->nom ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Catégorie</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $demande->categorieLabel() ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Soumis le</dt>
                <dd class="font-medium text-gray-800 mt-0.5">{{ $demande->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            @if($demande->annotation)
                <div class="sm:col-span-2">
                    <dt class="text-gray-500">Annotation Direction</dt>
                    <dd class="mt-0.5 text-gray-800 bg-yellow-50 border border-yellow-200 rounded-lg px-3 py-2">
                        {{ $demande->annotation }}
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    {{-- ── Décision finale Direction ───────────────────────────────────────── --}}
    @role('direction')
        @php
            $directionServiceId = \App\Models\Service::where('code', \App\Models\Service::DIRECTION)->value('id');
            $isAtDirection = $demande->current_service_id === $directionServiceId;
            $isClosed = in_array($demande->status?->code, ['APPROUVEE', 'FINALISEE', 'REJETEE', 'ANNULEE']);
            $canFinalize = $demande->workflows()
                ->where('reception_status', 'accepted')
                ->whereNotNull('from_service_id')
                ->exists();
        @endphp
        @if($isAtDirection && !$isClosed)
            <div class="bg-blue-50 border-2 border-blue-300 rounded-xl p-5"
                 x-data="{ panel: null }">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-gavel text-blue-600"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-blue-900">Décision finale — Direction générale</h3>
                        <p class="text-sm text-blue-700 mt-0.5 mb-3">Choisissez l'action à effectuer sur ce dossier.</p>

                        @if(!$canFinalize)
                            <div class="flex items-start gap-2 bg-yellow-50 border border-yellow-300 rounded-lg px-3 py-2 mb-3">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 text-sm flex-shrink-0"></i>
                                <p class="text-xs text-yellow-800">
                                    L'approbation et la clôture nécessitent que le dossier ait d'abord été
                                    traité et acheminé par un autre service. Le rejet et l'annulation restent possibles.
                                </p>
                            </div>
                        @endif

                        {{-- Boutons d'action --}}
                        <div class="flex flex-wrap gap-2 mt-2">
                            <button @click="panel = panel === 'approuver' ? null : 'approuver'"
                                    :class="{ 'opacity-40 cursor-not-allowed': {{ $canFinalize ? 'false' : 'true' }} }"
                                    {{ !$canFinalize ? 'disabled' : '' }}
                                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                <i class="fas fa-check-circle"></i> Approuver
                            </button>
                            <button @click="panel = panel === 'cloturer' ? null : 'cloturer'"
                                    :class="{ 'opacity-40 cursor-not-allowed': {{ $canFinalize ? 'false' : 'true' }} }"
                                    {{ !$canFinalize ? 'disabled' : '' }}
                                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                <i class="fas fa-flag-checkered"></i> Clôturer
                            </button>
                            <button @click="panel = panel === 'rejeter' ? null : 'rejeter'"
                                    class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                <i class="fas fa-times-circle"></i> Rejeter
                            </button>
                            <button @click="panel = panel === 'annuler' ? null : 'annuler'"
                                    class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded-lg flex items-center gap-2 transition-colors">
                                <i class="fas fa-ban"></i> Annuler
                            </button>
                        </div>

                        {{-- Panel Approuver --}}
                        <div x-show="panel === 'approuver'" x-cloak class="mt-4 bg-white border border-blue-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-blue-800 mb-3">Le dossier sera marqué comme <strong>approuvé</strong>. L'usager sera notifié.</p>
                            <form method="POST" action="{{ route('admin.demandes.approuver', $demande) }}">
                                @csrf
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Confirmer l'approbation</button>
                                    <button type="button" @click="panel = null" class="text-gray-500 hover:text-gray-700 text-sm px-4 py-2">Annuler</button>
                                </div>
                            </form>
                        </div>

                        {{-- Panel Clôturer --}}
                        <div x-show="panel === 'cloturer'" x-cloak class="mt-4 bg-white border border-green-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-green-800 mb-3">Le dossier sera <strong>clôturé définitivement</strong>. L'usager sera notifié.</p>
                            <form method="POST" action="{{ route('admin.demandes.cloturer', $demande) }}">
                                @csrf
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Confirmer la clôture</button>
                                    <button type="button" @click="panel = null" class="text-gray-500 hover:text-gray-700 text-sm px-4 py-2">Annuler</button>
                                </div>
                            </form>
                        </div>

                        {{-- Panel Rejeter --}}
                        <div x-show="panel === 'rejeter'" x-cloak class="mt-4 bg-white border border-orange-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-orange-700 mb-2">Le dossier sera <strong>rejeté</strong>. L'usager sera notifié.</p>
                            <form method="POST" action="{{ route('admin.demandes.rejeter', $demande) }}">
                                @csrf
                                <textarea name="motif" rows="2" placeholder="Motif du rejet (optionnel)"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-orange-300"></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium px-4 py-2 rounded-lg">Confirmer le rejet</button>
                                    <button type="button" @click="panel = null" class="text-gray-500 hover:text-gray-700 text-sm px-4 py-2">Annuler</button>
                                </div>
                            </form>
                        </div>

                        {{-- Panel Annuler --}}
                        <div x-show="panel === 'annuler'" x-cloak class="mt-4 bg-white border border-gray-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Le dossier sera <strong>annulé</strong>. L'usager sera notifié.</p>
                            <form method="POST" action="{{ route('admin.demandes.annuler', $demande) }}">
                                @csrf
                                <textarea name="motif" rows="2" placeholder="Motif de l'annulation (optionnel)"
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-gray-300"></textarea>
                                <div class="flex gap-2">
                                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Confirmer l'annulation</button>
                                    <button type="button" @click="panel = null" class="text-gray-500 hover:text-gray-700 text-sm px-4 py-2">Fermer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endrole

    {{-- ── Historique des transferts ────────────────────────────────────────── --}}
    @php $workflows = $demande->workflows()->with(['fromService','toService','user','receptionBy'])->latest()->get(); @endphp
    @if($workflows->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Historique des transferts</h2>
            <div class="space-y-3">
                @foreach($workflows as $wf)
                    @php
                        $wfIconClass = $wf->isAccepted()
                            ? 'bg-green-100 text-green-600'
                            : ($wf->isRefused() ? 'bg-red-100 text-red-600' : 'bg-sky-100 text-sky-600');
                        $wfIcon = $wf->isAccepted() ? 'fa-check' : ($wf->isRefused() ? 'fa-times' : 'fa-clock');
                    @endphp
                    <div class="flex items-start gap-3 text-sm">
                        <div class="mt-1 w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 {{ $wfIconClass }}">
                            <i class="fas {{ $wfIcon }} text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-700">
                                <span class="font-medium">{{ $wf->fromService?->nom ?? 'Soumission' }}</span>
                                → <span class="font-medium">{{ $wf->toService?->nom ?? '—' }}</span>
                            </p>
                            <p class="text-gray-400 text-xs mt-0.5">
                                Par {{ $wf->user?->name ?? '—' }} · {{ $wf->created_at->format('d/m/Y H:i') }}
                                @if($wf->commentaire)
                                    · <em>{{ $wf->commentaire }}</em>
                                @endif
                            </p>
                            @if($wf->isRefused())
                                <p class="text-red-600 text-xs mt-0.5">
                                    Refusé par {{ $wf->receptionBy?->name ?? '—' }}
                                    @if($wf->reception_motif) · "{{ $wf->reception_motif }}" @endif
                                </p>
                            @elseif($wf->isAccepted() && $wf->reception_at)
                                <p class="text-green-600 text-xs mt-0.5">
                                    Réceptionné par {{ $wf->receptionBy?->name ?? '—' }}
                                    le {{ $wf->reception_at->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Historique des statuts ──────────────────────────────────────────── --}}
    @php $histories = $demande->histories()->with('changer')->latest()->get(); @endphp
    @if($histories->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Historique des statuts</h2>
            <div class="space-y-2">
                @foreach($histories as $h)
                    <div class="text-sm flex gap-3">
                        <span class="text-gray-400 w-28 flex-shrink-0 text-xs pt-0.5">
                            {{ $h->created_at->format('d/m/Y H:i') }}
                        </span>
                        <div>
                            <span class="font-medium text-gray-700">{{ $h->statut }}</span>
                            @if($h->commentaire)
                                <span class="text-gray-500"> — {{ $h->commentaire }}</span>
                            @endif
                            @if($h->changer)
                                <span class="text-gray-400 text-xs ml-1">par {{ $h->changer->name }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
