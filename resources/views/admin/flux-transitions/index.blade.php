@extends('layouts.admin')

@section('title', 'Circuit de traitement')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Circuit de traitement</span>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cytoscape@3.30.2/dist/cytoscape.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dagre@0.8.5/dist/dagre.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cytoscape-dagre@2.5.0/cytoscape-dagre.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    cytoscape.use(cytoscapeDagre);

    @php
        // Build required circuit map: serviceNodeId → [type_demande|null, ...]
        $requiredMap = [];
        foreach ($requiredServices as $req) {
            $rid = 's' . $req->service_id;
            $requiredMap[$rid][] = $req->type_demande;
        }
        $requiredLabel = function(string $rid) use ($requiredMap): string {
            if (!isset($requiredMap[$rid])) return '';
            $labels = array_map(function($t) {
                if ($t === null) return 'Tous les types';
                return \App\Enums\TypeDemandeEnum::tryFrom($t)?->label() ?? $t;
            }, $requiredMap[$rid]);
            return implode(', ', $labels);
        };

        $nodes = collect();
        foreach ($transitions as $t) {
            if ($t->sourceService && !$nodes->contains('data.id', 's'.$t->sourceService->id)) {
                $rid = 's'.$t->sourceService->id;
                $nodes->push(['data' => ['id' => $rid, 'label' => $t->sourceService->nom, 'type' => 'service',
                    'required' => isset($requiredMap[$rid]) ? 1 : 0, 'requiredLabel' => $requiredLabel($rid)]]);
            }
            if (!$t->sourceService && !$nodes->contains('data.id', 'INIT')) {
                $nodes->push(['data' => ['id' => 'INIT', 'label' => "Soumission\ninitiale", 'type' => 'init', 'required' => 0, 'requiredLabel' => '']]);
            }
            if (!$nodes->contains('data.id', 's'.$t->destinationService->id)) {
                $rid = 's'.$t->destinationService->id;
                $nodes->push(['data' => ['id' => $rid, 'label' => $t->destinationService->nom, 'type' => 'service',
                    'required' => isset($requiredMap[$rid]) ? 1 : 0, 'requiredLabel' => $requiredLabel($rid)]]);
            }
        }
        $edges = $transitions->map(function ($t, $i) {
            $src = $t->sourceService ? 's'.$t->sourceService->id : 'INIT';
            $dst = 's'.$t->destinationService->id;
            $label = $t->action;
            if ($t->type_demande) {
                $enum = \App\Enums\TypeDemandeEnum::tryFrom($t->type_demande);
                $label .= "\n(".($enum?->label() ?? $t->type_demande).")";
            }
            return ['data' => ['id' => 'e'.$i, 'source' => $src, 'target' => $dst, 'label' => $label]];
        });

        $connectedServiceIds = collect();
        foreach ($transitions as $t) {
            if ($t->sourceService) $connectedServiceIds->push($t->sourceService->id);
            $connectedServiceIds->push($t->destinationService->id);
        }
        $connectedServiceIds = $connectedServiceIds->unique();
        foreach ($services as $service) {
            if (!$connectedServiceIds->contains($service->id)) {
                $rid = 's'.$service->id;
                $nodes->push(['data' => ['id' => $rid, 'label' => $service->nom, 'type' => 'orphan',
                    'required' => isset($requiredMap[$rid]) ? 1 : 0, 'requiredLabel' => $requiredLabel($rid)]]);
            }
        }
    @endphp

    const elements = {!! json_encode(['nodes' => $nodes->values(), 'edges' => $edges->values()]) !!};

    elements.nodes.push({ data: { id: 'FIN_APPROUVE',  label: 'Approuvé',  type: 'fin_ok'  } });
    elements.nodes.push({ data: { id: 'FIN_FINALISE',  label: 'Clôturé',   type: 'fin_ok'  } });
    elements.nodes.push({ data: { id: 'FIN_REJETE',    label: 'Rejeté',    type: 'fin_ko'  } });
    elements.nodes.push({ data: { id: 'FIN_ANNULE',    label: 'Annulé',    type: 'fin_ko'  } });

    @php $directionId = \App\Models\Service::where('code', \App\Models\Service::DIRECTION)->value('id'); @endphp
    const directionNodeId = 's{{ $directionId }}';
    if (elements.nodes.find(n => n.data.id === directionNodeId)) {
        elements.edges.push({ data: { id: 'e_approuve', source: directionNodeId, target: 'FIN_APPROUVE', label: 'Approuver' } });
        elements.edges.push({ data: { id: 'e_finalise', source: directionNodeId, target: 'FIN_FINALISE', label: 'Clôturer'  } });
        elements.edges.push({ data: { id: 'e_rejete',   source: directionNodeId, target: 'FIN_REJETE',   label: 'Rejeter'   } });
        elements.edges.push({ data: { id: 'e_annule',   source: directionNodeId, target: 'FIN_ANNULE',   label: 'Annuler'   } });
    }

    const cy = cytoscape({
        container: document.getElementById('cy'),
        elements,
        layout: { name: 'dagre', rankDir: 'LR', nodeSep: 60, rankSep: 120, edgeSep: 20, padding: 30 },
        style: [
            {
                selector: 'node[type="service"]',
                style: {
                    'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '110px',
                    'background-color': '#eff6ff', 'border-color': '#93c5fd', 'border-width': 2,
                    'color': '#1e3a8a', 'font-size': '11px', 'font-weight': '600',
                    'text-valign': 'center', 'text-halign': 'center',
                    'width': '120px', 'height': '44px', 'shape': 'round-rectangle', 'padding': '8px',
                },
            },
            {
                selector: 'node[type="init"]',
                style: {
                    'label': 'data(label)', 'text-wrap': 'wrap',
                    'background-color': '#000000', 'border-color': '#000000', 'border-width': 2,
                    'border-style': 'solid', 'color': '#ffffff', 'font-size': '10px', 'font-style': 'italic',
                    'text-valign': 'center', 'text-halign': 'center',
                    'width': '100px', 'height': '40px', 'shape': 'round-rectangle',
                },
            },
            {
                selector: 'edge',
                style: {
                    'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '100px',
                    'font-size': '9px', 'color': '#374151',
                    'text-background-color': '#f8fafc', 'text-background-opacity': 1, 'text-background-padding': '2px',
                    'curve-style': 'bezier', 'target-arrow-shape': 'triangle',
                    'target-arrow-color': '#64748b', 'line-color': '#94a3b8', 'width': 2, 'arrow-scale': 1.2,
                },
            },
            {
                selector: 'node[type="fin_ok"]',
                style: {
                    'label': 'data(label)', 'background-color': '#f0fdf4', 'border-color': '#4ade80', 'border-width': 3,
                    'color': '#166534', 'font-size': '11px', 'font-weight': '700',
                    'text-valign': 'center', 'text-halign': 'center', 'width': '90px', 'height': '36px', 'shape': 'round-rectangle',
                },
            },
            {
                selector: 'node[type="fin_ko"]',
                style: {
                    'label': 'data(label)', 'background-color': '#fff1f2', 'border-color': '#f87171', 'border-width': 3,
                    'color': '#991b1b', 'font-size': '11px', 'font-weight': '700',
                    'text-valign': 'center', 'text-halign': 'center', 'width': '90px', 'height': '36px', 'shape': 'round-rectangle',
                },
            },
            { selector: 'edge[target="FIN_APPROUVE"], edge[target="FIN_FINALISE"]', style: { 'line-color': '#4ade80', 'target-arrow-color': '#4ade80', 'line-style': 'dashed' } },
            { selector: 'edge[target="FIN_REJETE"], edge[target="FIN_ANNULE"]', style: { 'line-color': '#f87171', 'target-arrow-color': '#f87171', 'line-style': 'dashed' } },
            {
                selector: 'node[required=1]',
                style: {
                    'background-color': '#fff7ed', 'border-color': '#f97316', 'border-width': 3,
                },
            },
            { selector: 'node[required=1]:selected, node[required=1].highlighted', style: { 'background-color': '#ffedd5', 'border-color': '#ea580c', 'border-width': 4 } },
            { selector: 'node:selected, node.highlighted', style: { 'background-color': '#dbeafe', 'border-color': '#3b82f6', 'border-width': 3 } },
            { selector: 'edge:selected, edge.highlighted', style: { 'line-color': '#3b82f6', 'target-arrow-color': '#3b82f6', 'width': 3 } },
            { selector: '.faded', style: { 'opacity': 0.2 } },
            {
                selector: 'node[type="orphan"]',
                style: {
                    'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '110px',
                    'background-color': '#f9fafb', 'border-color': '#d1d5db', 'border-width': 2,
                    'border-style': 'dashed', 'color': '#6b7280', 'font-size': '11px',
                    'text-valign': 'center', 'text-halign': 'center',
                    'width': '120px', 'height': '44px', 'shape': 'round-rectangle', 'padding': '8px',
                },
            },
        ],
    });

    cy.on('mouseover', 'node[required=1]', function (e) {
        const node = e.target;
        const oe   = e.originalEvent;
        const tip  = document.getElementById('cy-tooltip');
        tip.querySelector('[data-tip]').textContent = 'Requis pour : ' + node.data('requiredLabel');
        tip.style.left = (oe.clientX + 14) + 'px';
        tip.style.top  = (oe.clientY - 36) + 'px';
        tip.classList.remove('hidden');
    });
    cy.on('mouseout', 'node', function () {
        document.getElementById('cy-tooltip').classList.add('hidden');
    });

    cy.on('tap', 'node', function (e) {
        const node = e.target;
        cy.elements().addClass('faded');
        node.removeClass('faded').addClass('highlighted');
        node.connectedEdges().removeClass('faded').addClass('highlighted');
        node.connectedEdges().connectedNodes().removeClass('faded');
    });
    cy.on('tap', function (e) {
        if (e.target === cy) cy.elements().removeClass('faded highlighted');
    });
    document.getElementById('cy-fit').addEventListener('click', () => cy.fit(undefined, 30));
    document.getElementById('cy-reset').addEventListener('click', () => {
        cy.elements().removeClass('faded highlighted');
        cy.fit(undefined, 30);
    });
    document.getElementById('cy-download').addEventListener('click', () => {
        const link = document.createElement('a');
        link.href = cy.png({ bg: 'white', scale: 2, full: true });
        link.download = 'circuit-traitement.png';
        link.click();
    });
    document.getElementById('swimlane-download').addEventListener('click', () => {
        const el = document.getElementById('swimlane-container');
        html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true }).then(canvas => {
            const link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'couloirs-traitement.png';
            link.click();
        });
    });
});
</script>
@endpush

@section('content')

@php
/* ──────────────────────────────────────────────
   Build swimlane data (ordered by first appearance)
   ────────────────────────────────────────────── */
// Required circuit services for swimlane badges
$swimRequiredMap = [];
foreach ($requiredServices as $req) {
    $rid = 's' . $req->service_id;
    $swimRequiredMap[$rid][] = $req->type_demande;
}
$swimRequiredLabel = function(string $rid) use ($swimRequiredMap): string {
    if (!isset($swimRequiredMap[$rid])) return '';
    $labels = array_map(function($t) {
        if ($t === null) return 'Tous';
        return \App\Enums\TypeDemandeEnum::tryFrom($t)?->label() ?? $t;
    }, $swimRequiredMap[$rid]);
    return implode(', ', $labels);
};

$laneOrder = [];   // ordered list of node IDs
$laneData  = [];   // ['nodeId' => ['label', 'incoming' => [], 'outgoing' => []]]

foreach ($transitions as $t) {
    if (!$t->sourceService) {
        if (!isset($laneData['INIT'])) {
            $laneOrder[]        = 'INIT';
            $laneData['INIT']   = ['label' => 'Portail Citoyen', 'incoming' => [], 'outgoing' => []];
        }
    }
}

foreach ($transitions as $t) {
    $srcId    = $t->sourceService ? 's'.$t->sourceService->id : 'INIT';
    $srcLabel = $t->sourceService ? $t->sourceService->nom    : 'Portail Citoyen';
    $dstId    = 's'.$t->destinationService->id;
    $dstLabel = $t->destinationService->nom;

    if (!isset($laneData[$srcId])) {
        $laneOrder[]        = $srcId;
        $laneData[$srcId]   = ['label' => $srcLabel, 'incoming' => [], 'outgoing' => []];
    }
    if (!isset($laneData[$dstId])) {
        $laneOrder[]        = $dstId;
        $laneData[$dstId]   = ['label' => $dstLabel, 'incoming' => [], 'outgoing' => []];
    }

    $actionLabel = $t->action;
    if ($t->type_demande) {
        $enum        = \App\Enums\TypeDemandeEnum::tryFrom($t->type_demande);
        $actionLabel .= ' · ' . ($enum?->label() ?? $t->type_demande);
    }

    $laneData[$srcId]['outgoing'][] = ['action' => $actionLabel, 'to' => $dstLabel,  'final' => false];
    $laneData[$dstId]['incoming'][] = ['action' => $actionLabel, 'from' => $srcLabel, 'final' => false];
}

// Append direction final-state outgoing transitions
$swimDirId = \App\Models\Service::where('code', \App\Models\Service::DIRECTION)->value('id');
if ($swimDirId && isset($laneData['s'.$swimDirId])) {
    foreach ([
        ['action' => 'Approuver', 'to' => 'Approuvé',  'final' => 'ok'],
        ['action' => 'Clôturer',  'to' => 'Clôturé',   'final' => 'ok'],
        ['action' => 'Rejeter',   'to' => 'Rejeté',    'final' => 'ko'],
        ['action' => 'Annuler',   'to' => 'Annulé',    'final' => 'ko'],
    ] as $f) {
        $laneData['s'.$swimDirId]['outgoing'][] = $f;
    }
}

// Colour palette — one set per lane (bg/border/header-bg/header-text/dot)
$palette = [
    'INIT' => ['bg'=>'bg-slate-50',   'border'=>'border-slate-200', 'hbg'=>'bg-slate-100',   'ht'=>'text-slate-700',  'dot'=>'bg-slate-400'],
    '_0'   => ['bg'=>'bg-blue-50',    'border'=>'border-blue-200',  'hbg'=>'bg-blue-100',    'ht'=>'text-blue-800',   'dot'=>'bg-blue-500'],
    '_1'   => ['bg'=>'bg-emerald-50', 'border'=>'border-emerald-200','hbg'=>'bg-emerald-100','ht'=>'text-emerald-800','dot'=>'bg-emerald-500'],
    '_2'   => ['bg'=>'bg-violet-50',  'border'=>'border-violet-200', 'hbg'=>'bg-violet-100', 'ht'=>'text-violet-800', 'dot'=>'bg-violet-500'],
    '_3'   => ['bg'=>'bg-amber-50',   'border'=>'border-amber-200',  'hbg'=>'bg-amber-100',  'ht'=>'text-amber-800',  'dot'=>'bg-amber-500'],
    '_4'   => ['bg'=>'bg-rose-50',    'border'=>'border-rose-200',   'hbg'=>'bg-rose-100',   'ht'=>'text-rose-800',   'dot'=>'bg-rose-500'],
    '_5'   => ['bg'=>'bg-cyan-50',    'border'=>'border-cyan-200',   'hbg'=>'bg-cyan-100',   'ht'=>'text-cyan-800',   'dot'=>'bg-cyan-500'],
    '_6'   => ['bg'=>'bg-teal-50',    'border'=>'border-teal-200',   'hbg'=>'bg-teal-100',   'ht'=>'text-teal-800',   'dot'=>'bg-teal-500'],
    '_7'   => ['bg'=>'bg-orange-50',  'border'=>'border-orange-200', 'hbg'=>'bg-orange-100', 'ht'=>'text-orange-800', 'dot'=>'bg-orange-500'],
];
$laneColors = [];
$pi = 0;
foreach ($laneOrder as $nodeId) {
    $laneColors[$nodeId] = $nodeId === 'INIT' ? $palette['INIT'] : $palette['_'.($pi++ % 8)];
}
@endphp

<div class="space-y-6"
     x-data="{ view: localStorage.getItem('flux-view') || 'graph' }"
     x-init="$watch('view', v => localStorage.setItem('flux-view', v))">

    {{-- Page header + view toggle --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Circuit de traitement</h1>
            <p class="text-sm text-gray-500 mt-0.5">Transitions autorisées entre services pour le traitement des dossiers.</p>
        </div>

        {{-- Toggle button --}}
        <div class="flex items-center bg-gray-100 rounded-lg p-1 gap-0.5 shrink-0">
            <button @click="view='graph'"
                :class="view==='graph' ? 'bg-white shadow-sm text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md transition-all whitespace-nowrap">
                <i class="fas fa-project-diagram"></i> Graphe
            </button>
            <button @click="view='swimlane'"
                :class="view==='swimlane' ? 'bg-white shadow-sm text-blue-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-md transition-all whitespace-nowrap">
                <i class="fas fa-layer-group"></i> Couloirs
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ═══════════════════════════════════════════
         VIEW 1 — Cytoscape graph
    ════════════════════════════════════════════ --}}
    <div x-show="view === 'graph'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-project-diagram mr-2 text-blue-400"></i> Représentation en graphe
                </h2>
                <div class="flex gap-2">
                    <button id="cy-fit"
                        class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors">
                        <i class="fas fa-compress-alt mr-1"></i> Ajuster
                    </button>
                    <button id="cy-reset"
                        class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors">
                        <i class="fas fa-redo mr-1"></i> Réinitialiser
                    </button>
                    <button id="cy-download"
                        class="text-xs px-3 py-1.5 border border-blue-300 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors">
                        <i class="fas fa-download mr-1"></i> Télécharger
                    </button>
                </div>
            </div>
            <p class="text-xs text-gray-400 mb-3">
                Cliquez sur un nœud pour voir ses connexions · Glissez les nœuds pour les repositionner · Molette pour zoomer
            </p>
            {{-- Legend --}}
            <div class="flex items-center gap-4 mb-3 flex-wrap">
                <div class="flex items-center gap-1.5 text-xs text-gray-500">
                    <span class="inline-block w-4 h-4 rounded border-2 border-blue-300 bg-blue-50"></span>
                    Service
                </div>
                <div class="flex items-center gap-1.5 text-xs text-orange-700 font-medium">
                    <span class="inline-block w-4 h-4 rounded border-[3px] border-orange-400 bg-orange-50"></span>
                    Étape obligatoire du circuit
                </div>
                <div class="flex items-center gap-1.5 text-xs text-gray-400">
                    <span class="inline-block w-4 h-4 rounded border-2 border-dashed border-gray-300 bg-gray-50"></span>
                    Service non connecté
                </div>
            </div>
            <div id="cy" class="w-full rounded-lg border border-gray-100" style="height: 420px;"></div>
            {{-- Tooltip (fixed, hidden by default) --}}
            <div id="cy-tooltip"
                 class="hidden fixed z-50 pointer-events-none bg-orange-50 border border-orange-300 text-orange-800 text-xs rounded-lg px-3 py-1.5 shadow-lg max-w-[220px]">
                <i class="fas fa-shield-alt text-orange-500 mr-1"></i><span data-tip></span>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         VIEW 2 — Swimlane (couloirs)
    ════════════════════════════════════════════ --}}
    <div x-show="view === 'swimlane'" x-cloak>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-layer-group mr-2 text-blue-400"></i> Procédure en couloirs (Swimlane)
                </h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400 italic">Un couloir par service responsable · flux de haut en bas</span>
                    <button id="swimlane-download"
                        class="text-xs px-3 py-1.5 border border-blue-300 rounded-lg hover:bg-blue-50 text-blue-600 transition-colors shrink-0">
                        <i class="fas fa-download mr-1"></i> Télécharger
                    </button>
                </div>
            </div>

            @if(empty($laneOrder))
                <p class="text-center text-gray-400 py-10 text-sm">Aucune transition définie.</p>
            @else
                <div id="swimlane-container" class="relative">
                    {{-- Vertical spine line --}}
                    <div class="absolute left-6 top-8 bottom-8 w-px bg-gray-200 z-0"></div>

                    <div class="space-y-0 relative z-10">
                        @foreach($laneOrder as $nodeId)
                            @php
                                $lane   = $laneData[$nodeId];
                                $c      = $laneColors[$nodeId];
                                $isInit = $nodeId === 'INIT';
                            @endphp

                            {{-- Lane row --}}
                            <div class="flex gap-4 items-stretch">

                                {{-- Left: step bullet + connector --}}
                                <div class="flex flex-col items-center shrink-0 w-12">
                                    <div class="w-5 h-5 rounded-full {{ $c['dot'] }} border-2 border-white shadow-sm shrink-0 mt-4 z-10"></div>
                                    @unless($loop->last)
                                        <div class="flex-1 w-px bg-gray-200 my-1"></div>
                                    @endunless
                                </div>

                                {{-- Right: lane card --}}
                                <div class="flex-1 mb-3">
                                    <div class="rounded-xl border {{ $c['border'] }} {{ $c['bg'] }} overflow-hidden">

                                        {{-- Lane header --}}
                                        <div class="{{ $c['hbg'] }} border-b {{ $c['border'] }} px-4 py-2.5 flex items-center gap-2.5">
                                            <span class="text-sm font-semibold {{ $c['ht'] }}">{{ $lane['label'] }}</span>
                                            @if($isInit)
                                                <span class="text-[11px] text-gray-400 italic">— point d'entrée</span>
                                            @endif
                                            @if(!$isInit && isset($swimRequiredMap[$nodeId]))
                                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-orange-100 border border-orange-300 text-orange-700"
                                                      title="Requis pour : {{ $swimRequiredLabel($nodeId) }}">
                                                    <i class="fas fa-shield-alt text-[9px]"></i>
                                                    Obligatoire
                                                    @if(count($swimRequiredMap[$nodeId]) === 1 && $swimRequiredMap[$nodeId][0] !== null)
                                                        · {{ $swimRequiredLabel($nodeId) }}
                                                    @endif
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Lane body --}}
                                        <div class="px-4 py-3 space-y-3">

                                            {{-- Incoming --}}
                                            @if(!empty($lane['incoming']))
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($lane['incoming'] as $inc)
                                                        <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full bg-white border border-gray-200 text-gray-500 shadow-xs">
                                                            <i class="fas fa-arrow-circle-down text-gray-300 text-[10px]"></i>
                                                            Reçoit de <strong class="text-gray-600">{{ $inc['from'] }}</strong>
                                                            <span class="text-gray-300">·</span>
                                                            <em>{{ $inc['action'] }}</em>
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif

                                            {{-- Outgoing --}}
                                            @if(!empty($lane['outgoing']))
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach($lane['outgoing'] as $out)
                                                        @if($out['final'] === 'ok')
                                                            <div class="inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg bg-green-50 border border-green-200 text-green-700 font-medium">
                                                                <i class="fas fa-check-circle text-green-500 text-[11px]"></i>
                                                                {{ $out['action'] }} → {{ $out['to'] }}
                                                            </div>
                                                        @elseif($out['final'] === 'ko')
                                                            <div class="inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg bg-red-50 border border-red-200 text-red-700 font-medium">
                                                                <i class="fas fa-times-circle text-red-400 text-[11px]"></i>
                                                                {{ $out['action'] }} → {{ $out['to'] }}
                                                            </div>
                                                        @else
                                                            <div class="inline-flex items-center gap-2 text-xs px-3 py-1.5 rounded-lg bg-white border border-gray-200 text-gray-700 shadow-xs">
                                                                <i class="fas fa-arrow-right text-gray-400 text-[10px]"></i>
                                                                <strong>{{ $out['action'] }}</strong>
                                                                <span class="text-gray-300">→</span>
                                                                {{ $out['to'] }}
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @endif

                                            @if(empty($lane['incoming']) && empty($lane['outgoing']))
                                                <span class="text-xs text-gray-400 italic">Aucune transition configurée.</span>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Final states row --}}
                        @if(!empty($laneOrder))
                            <div class="flex gap-4 items-start">
                                <div class="flex flex-col items-center shrink-0 w-12">
                                    <div class="w-5 h-5 rounded-full bg-gray-300 border-2 border-white shadow-sm mt-2 z-10"></div>
                                </div>
                                <div class="flex-1 grid grid-cols-2 gap-3 pb-2">
                                    <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                                            <i class="fas fa-check text-green-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-green-800">Approuvé / Clôturé</p>
                                            <p class="text-xs text-green-600 mt-0.5">Dossier traité avec succès</p>
                                        </div>
                                    </div>
                                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                            <i class="fas fa-times text-red-600 text-sm"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-red-800">Rejeté / Annulé</p>
                                            <p class="text-xs text-red-600 mt-0.5">Dossier clôturé sans approbation</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         Formulaire d'ajout (always visible)
    ════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100"
         x-data="{ sourceId: '' }">
        <div class="px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-plus-circle mr-1.5 text-blue-500"></i> Ajouter une transition
            </h2>
        </div>
        <div class="px-6 py-5">
            <form method="POST" action="{{ route('admin.flux-transitions.store') }}"
                  class="flex flex-wrap gap-4 items-end">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service source</label>
                    <select name="service_source_id" x-model="sourceId"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                        <option value="" disabled selected>— Choisir un service —</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end pb-2.5 text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Service destination <span class="text-red-500">*</span>
                    </label>
                    <select name="service_destination_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                        <option value="">-- Choisir --</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}"
                                x-bind:disabled="sourceId == '{{ $svc->id }}'">
                                {{ $svc->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_destination_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Action <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="action" value="{{ old('action') }}" required
                        placeholder="ex: Annoter, Dispatcher…"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-44">
                    @error('action')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande</label>
                    <select name="type_demande"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[180px]">
                        <option value="">— Tous les types —</option>
                        @foreach(\App\Enums\TypeDemandeEnum::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         Tableau des transitions (always visible)
    ════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-8">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Source</th>
                    <th class="px-2 py-3 w-6"></th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Destination</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Action</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Type demande</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Ordre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transitions as $i => $transition)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">

                        <td class="px-3 py-3 text-center text-gray-400 text-xs">{{ $i + 1 }}</td>

                        <td class="px-4 py-3">
                            @if($transition->sourceService)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                    {{ $transition->sourceService->nom }}
                                </span>
                            @else
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-black text-white italic">
                                    Soumission initiale
                                </span>
                            @endif
                        </td>

                        <td class="px-2 py-3 text-gray-400 text-center">
                            <i class="fas fa-arrow-right text-xs"></i>
                        </td>

                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                {{ $transition->destinationService->nom }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-gray-600 text-xs">{{ $transition->action }}</td>

                        <td class="px-4 py-3">
                            @if($transition->type_demande)
                                @php $enum = \App\Enums\TypeDemandeEnum::tryFrom($transition->type_demande); @endphp
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                    {{ $enum?->label() ?? $transition->type_demande }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Tous</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($transition->service_source_id !== null)
                            <div class="flex items-center justify-center gap-1">
                                @unless($loop->first)
                                    <form method="POST" action="{{ route('admin.flux-transitions.move-up', $transition->id) }}">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-blue-600 transition-colors" title="Monter">
                                            <i class="fas fa-chevron-up text-xs"></i>
                                        </button>
                                    </form>
                                @endunless
                                @unless($loop->last)
                                    <form method="POST" action="{{ route('admin.flux-transitions.move-down', $transition->id) }}">
                                        @csrf
                                        <button type="submit" class="text-gray-400 hover:text-blue-600 transition-colors" title="Descendre">
                                            <i class="fas fa-chevron-down text-xs"></i>
                                        </button>
                                    </form>
                                @endunless
                            </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    x-data
                                    @click="$dispatch('open-edit-{{ $transition->id }}')"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium transition-colors">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>

                                @if($transition->service_source_id !== null)
                                <button type="button"
                                    x-data
                                    @click="$dispatch('open-delete-{{ $transition->id }}')"
                                    class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>

                            @if($transition->service_source_id !== null)
                            <div x-data="{ open: false }"
                                 x-on:open-delete-{{ $transition->id }}.window="open = true"
                                 x-show="open" x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                            <i class="fas fa-trash text-red-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Supprimer la transition</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">Cette action est irréversible.</p>
                                        </div>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-5">
                                        Voulez-vous vraiment supprimer la transition
                                        <span class="font-medium text-gray-800">« {{ $transition->action }} »</span> ?
                                    </p>
                                    <div class="flex justify-end gap-2">
                                        <button type="button" @click="open = false"
                                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                                            Annuler
                                        </button>
                                        <form method="POST" action="{{ route('admin.flux-transitions.destroy', $transition->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div x-data="{ open: false }"
                                 x-on:open-edit-{{ $transition->id }}.window="open = true"
                                 x-show="open" x-cloak
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6" @click.stop>
                                    <h3 class="font-semibold text-gray-800 mb-4">
                                        <i class="fas fa-pencil-alt mr-2 text-blue-500"></i> Modifier la transition
                                    </h3>
                                    <form method="POST" action="{{ route('admin.flux-transitions.update', $transition->id) }}">
                                        @csrf
                                        @method('PATCH')

                                        @if($transition->service_source_id !== null)
                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Service source</label>
                                            <select name="service_source_id"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                @foreach($services as $svc)
                                                    <option value="{{ $svc->id }}" @selected($transition->service_source_id === $svc->id)>{{ $svc->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @endif

                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Service destination <span class="text-red-500">*</span></label>
                                            <select name="service_destination_id" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                @foreach($services as $svc)
                                                    <option value="{{ $svc->id }}" @selected($transition->service_destination_id === $svc->id)>{{ $svc->nom }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                                            <input type="text" name="action" required value="{{ $transition->action }}"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                        <div class="mb-5">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande</label>
                                            <select name="type_demande"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">— Tous les types —</option>
                                                @foreach(\App\Enums\TypeDemandeEnum::cases() as $type)
                                                    <option value="{{ $type->value }}"
                                                        @selected($transition->type_demande === $type->value)>
                                                        {{ $type->label() }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false"
                                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                                                Annuler
                                            </button>
                                            <button type="submit"
                                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-route text-3xl mb-2 block"></i>
                            Aucune transition définie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════
         Étapes obligatoires (circuit enforcement)
    ════════════════════════════════════════════ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-shield-alt mr-2 text-orange-400"></i> Étapes obligatoires du circuit
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Un dossier ne peut être approuvé ou clôturé que si tous ces services l'ont traité (réception acceptée).
                </p>
            </div>
        </div>

        {{-- Add form --}}
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('admin.flux-transitions.required.store') }}"
                  class="flex flex-wrap items-end gap-3">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Service <span class="text-red-500">*</span>
                    </label>
                    <select name="service_id" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[180px]">
                        <option value="">— Choisir —</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande</label>
                    <select name="type_demande"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[200px]">
                        <option value="">— Tous les types —</option>
                        @foreach(\App\Enums\TypeDemandeEnum::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit"
                        class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fas fa-plus mr-1"></i> Ajouter
                    </button>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Service requis</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Type de demande</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-20">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requiredServices as $req)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                {{ $req->service->nom }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if($req->type_demande)
                                @php $enum = \App\Enums\TypeDemandeEnum::tryFrom($req->type_demande); @endphp
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                    {{ $enum?->label() ?? $req->type_demande }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Tous les types</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div x-data="{ open: false }">
                                <button type="button" @click="open = true"
                                    class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium transition-colors">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div x-show="open" x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                        <h3 class="font-semibold text-gray-800 mb-3">Supprimer l'étape obligatoire</h3>
                                        <p class="text-sm text-gray-600 mb-5">
                                            Supprimer <span class="font-medium text-gray-800">{{ $req->service->nom }}</span>
                                            @if($req->type_demande)
                                                pour <span class="font-medium text-gray-800">{{ \App\Enums\TypeDemandeEnum::tryFrom($req->type_demande)?->label() ?? $req->type_demande }}</span>
                                            @else
                                                (tous les types)
                                            @endif
                                            ? Les dossiers pourront être approuvés sans passer par ce service.
                                        </p>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false"
                                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                                                Annuler
                                            </button>
                                            <form method="POST" action="{{ route('admin.flux-transitions.required.destroy', $req->id) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-shield-alt text-3xl mb-2 block"></i>
                            Aucune étape obligatoire définie. Tous les dossiers peuvent être approuvés librement.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
