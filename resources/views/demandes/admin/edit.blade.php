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
        $lastIncoming = $demande->interactions()
            ->with(['fromService', 'toService', 'initiatedBy'])
            ->where('type', \App\Models\DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', \App\Models\DemandeInteraction::STATUT_ACCEPTE)
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
                    <span class="font-medium">{{ $lastIncoming->initiatedBy?->name ?? '—' }}</span>
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

    {{-- ── Transfert en cours de traitement (lecture seule — géré par l'agent du service) ── --}}
    @php
        $pendingWorkflow = $demande->interactions()
            ->with(['fromService', 'toService', 'initiatedBy'])
            ->where('type', \App\Models\DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', \App\Models\DemandeInteraction::STATUT_EN_ATTENTE)
            ->latest()
            ->first();
    @endphp

    @if($pendingWorkflow)
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-clock text-sky-500 text-sm"></i>
            </div>
            <div class="text-sm leading-relaxed">
                <p class="font-semibold text-sky-900">
                    En attente de réception par
                    <span class="text-sky-700">{{ $pendingWorkflow->toService->nom ?? '—' }}</span>
                </p>
                <p class="text-sky-700 mt-0.5">
                    Transféré depuis <span class="font-medium">{{ $pendingWorkflow->fromService->nom ?? '—' }}</span>
                    par <span class="font-medium">{{ $pendingWorkflow->initiatedBy?->name ?? '—' }}</span>
                    le {{ $pendingWorkflow->created_at->format('d/m/Y à H:i') }}
                    <span class="text-sky-400 ml-1">({{ $pendingWorkflow->created_at->diffForHumans() }})</span>
                </p>
                @if($pendingWorkflow->commentaire)
                    <p class="text-sky-600 italic mt-1">"{{ $pendingWorkflow->commentaire }}"</p>
                @endif
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
                        {{ \App\Models\WorkflowStep::getStatusStyle($demande->currentStep?->code) }}">
                        {{ $demande->currentStep?->nom }}
                    </span>
                @endif
                <a href="{{ route('demande.pdf', $demande) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition">
                    <i class="fas fa-file-pdf text-red-400"></i> Télécharger PDF
                </a>
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

    {{-- ── Affectation agent ───────────────────────────────────────────────── --}}
    @php
        $currentAssignment = $demande->currentAssignment()->with(['agent', 'assignePar'])->first();
        $agentsDisponibles = \App\Models\User::when($demande->current_service_id,
                fn($q) => $q->where('service_id', $demande->current_service_id)
            )->where('is_active', true)->orderBy('name')->get();
        $isClosed = in_array($demande->status?->code, ['APPROUVEE', 'FINALISEE', 'REJETEE', 'ANNULEE']);
    @endphp
    @if(!$isClosed)
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5"
         x-data="{ showForm: false }">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-user-check text-indigo-400"></i> Agent responsable
            </h2>
            @if($agentsDisponibles->isNotEmpty())
                <button type="button" @click="showForm = !showForm"
                        class="text-xs text-indigo-600 hover:underline font-medium">
                    <span x-text="showForm ? 'Annuler' : '{{ $currentAssignment ? 'Réaffecter' : 'Affecter' }}'"></span>
                </button>
            @endif
        </div>

        @if($currentAssignment)
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($currentAssignment->agent->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">{{ $currentAssignment->agent->name }}</p>
                    <p class="text-xs text-gray-400">
                        Affecté par {{ $currentAssignment->assignePar->name }}
                        · {{ $currentAssignment->created_at->format('d/m/Y à H:i') }}
                    </p>
                    @if($currentAssignment->note)
                        <p class="text-xs text-gray-500 italic mt-0.5">"{{ $currentAssignment->note }}"</p>
                    @endif
                </div>
            </div>
        @else
            <p class="text-sm text-gray-400 italic">Aucun agent affecté pour le moment.</p>
        @endif

        <div x-show="showForm" x-cloak x-transition class="mt-4 border-t border-gray-100 pt-4">
            @if($agentsDisponibles->isEmpty())
                <p class="text-xs text-gray-400">Aucun agent actif dans le service courant.</p>
            @else
                <form method="POST" action="{{ route('admin.demandes.assigner-agent', $demande) }}">
                    @csrf
                    <div class="flex flex-col sm:flex-row gap-3">
                        <select name="user_id" required
                                class="flex-1 text-sm border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-300 focus:outline-none">
                            <option value="">— Choisir un agent —</option>
                            @foreach($agentsDisponibles as $agent)
                                <option value="{{ $agent->id }}"
                                    {{ $currentAssignment?->user_id === $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition">
                            Confirmer
                        </button>
                    </div>
                    <input type="text" name="note" placeholder="Note (optionnel)"
                           class="mt-2 w-full text-sm border border-gray-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:outline-none text-gray-700">
                </form>
            @endif
        </div>

        {{-- Historique des affectations --}}
        @php $pastAssignments = $demande->assignments()->with(['agent', 'assignePar'])->whereNotNull('ended_at')->get(); @endphp
        @if($pastAssignments->isNotEmpty())
            <div x-data="{ open: false }" class="mt-4 border-t border-gray-100 pt-3">
                <button type="button" @click="open = !open"
                        class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    <i class="fas fa-history"></i>
                    <span x-text="open ? 'Masquer l\'historique' : 'Voir l\'historique ({{ $pastAssignments->count() }})'"></span>
                </button>
                <div x-show="open" x-cloak x-transition class="mt-2 space-y-2">
                    @foreach($pastAssignments as $past)
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold flex-shrink-0 text-[10px]">
                                {{ strtoupper(substr($past->agent->name, 0, 1)) }}
                            </div>
                            <span class="font-medium text-gray-700">{{ $past->agent->name }}</span>
                            <span class="text-gray-300">·</span>
                            <span>{{ $past->created_at->format('d/m/Y') }} → {{ $past->ended_at->format('d/m/Y') }}</span>
                            <span class="text-gray-300">·</span>
                            <span class="italic">par {{ $past->assignePar->name }}</span>
                            @if($past->note)
                                <span class="text-gray-300">·</span>
                                <span class="italic text-gray-400">"{{ $past->note }}"</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ── Décision finale Direction ───────────────────────────────────────── --}}
    @role('direction')
        @php
            $directionServiceId = \App\Models\Service::where('code', \App\Models\Service::DIRECTION)->value('id');
            $isAtDirection = $demande->current_service_id === $directionServiceId;
            $isClosed = in_array($demande->status?->code, ['APPROUVEE', 'FINALISEE', 'REJETEE', 'ANNULEE']);
            $hasBeenRouted = $demande->interactions()
                ->where('type', \App\Models\DemandeInteraction::TYPE_TRANSFERT)
                ->where('statut', \App\Models\DemandeInteraction::STATUT_ACCEPTE)
                ->whereNotNull('from_service_id')
                ->exists();
            $allConditionsMet = empty(array_filter($requiredConditions ?? [], fn($c) => !$c['is_met']));
            $canFinalize = $hasBeenRouted && $allConditionsMet;
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

                        {{-- Conditions préalables --}}
                        @if(!empty($requiredConditions))
                            <div class="mb-4 bg-white border border-blue-200 rounded-lg overflow-hidden">
                                <div class="px-3 py-2 bg-blue-100 border-b border-blue-200">
                                    <p class="text-xs font-semibold text-blue-800 uppercase tracking-wide">
                                        <i class="fas fa-tasks mr-1"></i> Conditions requises pour Approuver / Clôturer
                                    </p>
                                </div>
                                <ul class="divide-y divide-gray-100">
                                    @foreach($requiredConditions as $condition)
                                        <li class="flex items-center gap-3 px-3 py-2.5">
                                            @if($condition['is_met'])
                                                <span class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-check text-green-600 text-[10px]"></i>
                                                </span>
                                                <span class="text-sm text-green-800 font-medium">{{ $condition['service_name'] }}</span>
                                                <span class="ml-auto text-xs text-green-600 font-medium">Traité</span>
                                            @else
                                                <span class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                                    <i class="fas fa-times text-red-500 text-[10px]"></i>
                                                </span>
                                                <span class="text-sm text-gray-700">{{ $condition['service_name'] }}</span>
                                                <span class="ml-auto text-xs text-red-500 font-medium">En attente</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(!$canFinalize)
                            <div class="flex items-start gap-2 bg-yellow-50 border border-yellow-300 rounded-lg px-3 py-2 mb-3">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 text-sm flex-shrink-0"></i>
                                <p class="text-xs text-yellow-800">
                                    @if(!$hasBeenRouted)
                                        L'approbation et la clôture nécessitent que le dossier ait d'abord été traité et acheminé par un autre service.
                                    @else
                                        Le circuit de traitement n'est pas complet. Tous les services listés ci-dessus doivent avoir traité le dossier.
                                    @endif
                                    Le rejet et l'annulation restent possibles.
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
    @php
        $transferts = $demande->interactions()
            ->with(['fromService', 'toService', 'initiatedBy', 'reponduBy'])
            ->where('type', \App\Models\DemandeInteraction::TYPE_TRANSFERT)
            ->latest()
            ->get();
    @endphp
    @if($transferts->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Historique des transferts</h2>
            <div class="space-y-3">
                @foreach($transferts as $wf)
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
                                Par {{ $wf->initiatedBy?->name ?? '—' }} · {{ $wf->created_at->format('d/m/Y H:i') }}
                                @if($wf->commentaire)
                                    · <em>{{ $wf->commentaire }}</em>
                                @endif
                            </p>
                            @if($wf->isRefused())
                                <p class="text-red-600 text-xs mt-0.5">
                                    Refusé par {{ $wf->reponduBy?->name ?? '—' }}
                                    @if($wf->reponse) · "{{ $wf->reponse }}" @endif
                                </p>
                            @elseif($wf->isAccepted() && $wf->repondu_at)
                                <p class="text-green-600 text-xs mt-0.5">
                                    Réceptionné par {{ $wf->reponduBy?->name ?? '—' }}
                                    le {{ $wf->repondu_at->format('d/m/Y H:i') }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Pièces jointes ───────────────────────────────────────────────── --}}
    @php $mediaItems = $demande->getMedia(); @endphp
    @if($mediaItems->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">
            <i class="fas fa-paperclip text-gray-400 mr-1"></i> Pièces jointes ({{ $mediaItems->count() }})
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($mediaItems as $media)
                @php
                    $isImage = str_starts_with($media->mime_type, 'image/');
                    $isPdf   = $media->mime_type === 'application/pdf';
                    $sizeMo  = number_format($media->size / 1048576, 2);
                @endphp
                <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:bg-gray-50 group">
                    <div class="w-9 h-9 rounded flex items-center justify-center flex-shrink-0
                        {{ $isImage ? 'bg-blue-50' : ($isPdf ? 'bg-red-50' : 'bg-gray-100') }}">
                        <i class="fas {{ $isImage ? 'fa-image text-blue-400' : ($isPdf ? 'fa-file-pdf text-red-400' : 'fa-file text-gray-400') }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-medium text-gray-700 truncate">{{ $media->file_name }}</p>
                        <p class="text-[10px] text-gray-400">{{ $media->collection_name }} · {{ $sizeMo }} Mo</p>
                    </div>
                    <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition">
                        @if($isImage)
                            <a href="{{ $media->getUrl() }}" target="_blank"
                               class="p-1.5 rounded text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="Aperçu">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                        @endif
                        <a href="{{ $media->getUrl() }}" download="{{ $media->file_name }}"
                           class="p-1.5 rounded text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Télécharger">
                            <i class="fas fa-download text-xs"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Historique des états ──────────────────────────────────────────── --}}
    @php $histories = $demande->histories()->with('changer')->latest()->get(); @endphp
    @if($histories->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">Historique des états</h2>
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
