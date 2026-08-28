@extends('layouts.admin')

@section('title', 'Circuit de traitement')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Circuit de traitement</span>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/cytoscape@3.30.2/dist/cytoscape.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dagre@0.8.5/dist/dagre.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/cytoscape-dagre@2.5.0/cytoscape-dagre.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cyContainer = document.getElementById('cy');
    if (!cyContainer) return;

    cytoscape.use(cytoscapeDagre);

    @php
        // Build Cytoscape elements from workflow_steps + workflow_step_transitions

        $stepMap = $steps->keyBy('id');

        // Required service map for badge colouring
        $requiredServiceIds = $requiredServices->pluck('service_id')->unique()->values();

        // Nodes
        $cyNodes = $steps->map(function ($step) use ($requiredServiceIds) {
            $isRequired = $step->service_id && $requiredServiceIds->contains($step->service_id);

            // Type visuel selon le rôle dans le workflow
            $nodeType = match(true) {
                $step->isInitial() && $step->code === 'BROUILLON' => 'start_draft',
                $step->isInitial()                                => 'start_submit',
                $step->isTerminal()                               => str_contains(\App\Models\WorkflowStep::getStatusStyle($step->code), 'red') ? 'fin_ko' : 'fin_ok',
                in_array($step->code, ['DECISION_FINALE', 'EN_DECISION']) => 'end_decision',
                default                                               => 'step',
            };

            // Label = nom de l'étape + [Service] entre parenthèses
            $etatLabel = $step->nom;
            $label = $step->service
                ? $etatLabel . "\n(" . $step->service->nom . ")"
                : $etatLabel;

            return [
                'data' => [
                    'id'          => 's' . $step->id,
                    'label'       => $label,
                    'stepName'    => $step->nom,
                    'serviceName' => $step->service?->nom ?? '',
                    'type'        => $nodeType,
                    'required'    => $isRequired ? 1 : 0,
                    'isGlobal'    => $step->type_demande === null ? 1 : 0,
                ],
            ];
        });

        // Codes des étapes terminales pour colorier les arêtes
        $terminalOkCodes = ['APPROUVEE', 'FINALISEE'];
        $terminalKoCodes = ['REJETEE',   'ANNULEE'];

        // Edges from step transitions
        $cyEdges = collect();
        foreach ($stepTransitions as $i => $t) {
            $src         = $t->from_step_id ? 's' . $t->from_step_id : 'INIT';
            $dst         = 's' . $t->to_step_id;
            $toCode      = $t->toStep?->code ?? '';
            $cyEdges->push(['data' => [
                'id'           => 'e' . $i,
                'source'       => $src,
                'target'       => $dst,
                'label'        => $t->action . ($t->is_urgent_only ? "\n⚡ urgent" : ''),
                'isTerminalOk' => in_array($toCode, $terminalOkCodes),
                'isTerminalKo' => in_array($toCode, $terminalKoCodes),
            ]]);
        }

        // Initial submission node (if any transition starts from null)
        $hasInitTransition = $stepTransitions->whereNull('from_step_id')->isNotEmpty();

    @endphp

    @if($hasInitTransition)
    const initNode = { data: { id: 'INIT', label: "Soumission\ninitiale", stepName: 'Soumission initiale', serviceName: '', type: 'init', required: 0, isGlobal: 0 } };
    @endif

    const cyNodes = {!! json_encode($cyNodes->values()) !!};
    const cyEdges = {!! json_encode($cyEdges->values()) !!};

    @if($hasInitTransition)
    cyNodes.unshift(initNode);
    @endif


    // Dédupliquer les nœuds (les orphelins restent visibles pour le diagnostic)
    const seen = new Set();
    const uniqueNodes = cyNodes.filter(n => {
        if (seen.has(n.data.id)) return false;
        seen.add(n.data.id);
        return true;
    });

    const elements = {
        nodes: uniqueNodes,
        edges: cyEdges,
    };

    // ── Styles partagés (interaction — identiques dans les deux modes) ──
    const sharedStyles = [
        { selector: 'edge', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '90px',
            'font-size': '9px', 'color': '#374151',
            'text-background-color': '#f8fafc', 'text-background-opacity': 1, 'text-background-padding': '2px',
            'curve-style': 'bezier', 'target-arrow-shape': 'triangle',
            'target-arrow-color': '#64748b', 'line-color': '#94a3b8', 'width': 2, 'arrow-scale': 1.2,
        }},
        { selector: 'edge[?isTerminalOk]', style: { 'line-color': '#4ade80', 'target-arrow-color': '#4ade80', 'line-style': 'dashed' } },
        { selector: 'edge[?isTerminalKo]', style: { 'line-color': '#f87171', 'target-arrow-color': '#f87171', 'line-style': 'dashed' } },
        { selector: 'node:selected, node.hl', style: { 'border-color': '#3b82f6', 'border-width': 3, 'background-color': '#dbeafe' } },
        { selector: 'edge:selected, edge.hl',  style: { 'line-color': '#3b82f6', 'target-arrow-color': '#3b82f6', 'width': 3 } },
        { selector: '.faded', style: { 'opacity': 0.2 } },
    ];

    // ── Mode personnalisé (couleurs métier) ──────────────────────────
    const customStyles = [
        { selector: 'node[type="step"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '130px',
            'background-color': '#eff6ff', 'border-color': '#93c5fd', 'border-width': 2,
            'color': '#1e3a8a', 'font-size': '10px', 'font-weight': '600',
            'text-valign': 'center', 'text-halign': 'center',
            'width': '145px', 'height': '54px', 'shape': 'round-rectangle', 'padding': '8px',
        }},
        { selector: 'node[type="step"][isGlobal=1]', style: { 'border-style': 'dashed' } },
        { selector: 'node[type="start_draft"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '100px',
            'background-color': '#fffbeb', 'border-color': '#f59e0b', 'border-width': 3, 'border-style': 'dashed',
            'color': '#78350f', 'font-size': '10px', 'font-weight': '700', 'font-style': 'italic',
            'text-valign': 'bottom', 'text-margin-y': '10px', 'text-halign': 'center',
            'width': '56px', 'height': '56px', 'shape': 'ellipse',
        }},
        { selector: 'node[type="start_submit"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '110px',
            'background-color': '#eef2ff', 'border-color': '#4f46e5', 'border-width': 4,
            'color': '#312e81', 'font-size': '10px', 'font-weight': '800',
            'text-valign': 'bottom', 'text-margin-y': '10px', 'text-halign': 'center',
            'width': '64px', 'height': '64px', 'shape': 'ellipse',
            'outline-color': '#a5b4fc', 'outline-width': 3, 'outline-offset': 3, 'outline-opacity': 1,
        }},
        { selector: 'node[type="init"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap',
            'background-color': '#1e1b4b', 'border-color': '#4f46e5', 'border-width': 2,
            'color': '#e0e7ff', 'font-size': '10px', 'font-style': 'italic',
            'text-valign': 'center', 'text-halign': 'center',
            'width': '100px', 'height': '40px', 'shape': 'round-rectangle',
        }},
        { selector: 'node[required=1]', style: { 'background-color': '#fff7ed', 'border-color': '#f97316', 'border-width': 3 } },
        { selector: 'node[type="end_decision"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '130px',
            'background-color': '#312e81', 'border-color': '#818cf8', 'border-width': 3,
            'color': '#e0e7ff', 'font-size': '11px', 'font-weight': '800',
            'text-valign': 'center', 'text-halign': 'center',
            'width': '150px', 'height': '58px', 'shape': 'round-rectangle',
            'outline-color': '#6366f1', 'outline-width': 3, 'outline-offset': 3, 'outline-opacity': 1,
        }},
        { selector: 'node[type="fin_ok"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '110px',
            'background-color': '#16a34a', 'border-color': '#4ade80', 'border-width': 5,
            'color': '#ffffff', 'font-size': '10px', 'font-weight': '800',
            'text-valign': 'bottom', 'text-margin-y': '10px', 'text-halign': 'center',
            'width': '56px', 'height': '56px', 'shape': 'ellipse',
            'outline-color': '#bbf7d0', 'outline-width': 3, 'outline-offset': 2, 'outline-opacity': 1,
        }},
        { selector: 'node[type="fin_ko"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '110px',
            'background-color': '#dc2626', 'border-color': '#f87171', 'border-width': 5,
            'color': '#ffffff', 'font-size': '10px', 'font-weight': '800',
            'text-valign': 'bottom', 'text-margin-y': '10px', 'text-halign': 'center',
            'width': '56px', 'height': '56px', 'shape': 'ellipse',
            'outline-color': '#fecaca', 'outline-width': 3, 'outline-offset': 2, 'outline-opacity': 1,
        }},
    ];

    // ── Mode BPMN 2.0 (formes standard) ─────────────────────────────
    // Événement début = ellipse fin / Tâche = rectangle arrondi
    // Passerelle = diamond / Événement fin = ellipse épaisse
    const bpmnStyles = [
        // Tâche de service (étape standard)
        { selector: 'node[type="step"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '110px',
            'background-color': '#ffffff', 'border-color': '#374151', 'border-width': 2,
            'color': '#111827', 'font-size': '10px', 'font-weight': '600',
            'text-valign': 'center', 'text-halign': 'center',
            'width': '130px', 'height': '52px', 'shape': 'round-rectangle', 'padding': '8px',
        }},
        { selector: 'node[type="step"][isGlobal=1]', style: { 'border-style': 'dashed', 'border-color': '#6b7280' } },
        // Événement début non-interrompant (brouillon) — cercle fin + contour pointillé ambre
        { selector: 'node[type="start_draft"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '80px',
            'background-color': '#fffbeb', 'border-color': '#f59e0b', 'border-width': 2, 'border-style': 'dashed',
            'color': '#78350f', 'font-size': '9px', 'font-weight': '600',
            'text-valign': 'bottom', 'text-margin-y': '6px', 'text-halign': 'center',
            'width': '40px', 'height': '40px', 'shape': 'ellipse',
        }},
        // Événement début (soumission) — cercle fin indigo
        { selector: 'node[type="start_submit"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '80px',
            'background-color': '#eef2ff', 'border-color': '#4f46e5', 'border-width': 3,
            'color': '#312e81', 'font-size': '9px', 'font-weight': '700',
            'text-valign': 'bottom', 'text-margin-y': '6px', 'text-halign': 'center',
            'width': '44px', 'height': '44px', 'shape': 'ellipse',
        }},
        // Événement début générique (init)
        { selector: 'node[type="init"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '80px',
            'background-color': '#1e1b4b', 'border-color': '#4f46e5', 'border-width': 2,
            'color': '#e0e7ff', 'font-size': '9px',
            'text-valign': 'bottom', 'text-margin-y': '6px', 'text-halign': 'center',
            'width': '36px', 'height': '36px', 'shape': 'ellipse',
        }},
        // Étape obligatoire — marquée par une bordure orange
        { selector: 'node[required=1]', style: { 'border-color': '#f97316', 'border-width': 3 } },
        // Passerelle exclusive (décision) — losange indigo
        { selector: 'node[type="end_decision"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '90px',
            'background-color': '#312e81', 'border-color': '#818cf8', 'border-width': 3,
            'color': '#e0e7ff', 'font-size': '9px', 'font-weight': '800',
            'text-valign': 'bottom', 'text-margin-y': '8px', 'text-halign': 'center',
            'width': '54px', 'height': '54px', 'shape': 'diamond',
        }},
        // Événement fin positif — cercle épais vert (BPMN : trait double)
        { selector: 'node[type="fin_ok"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '80px',
            'background-color': '#dcfce7', 'border-color': '#16a34a', 'border-width': 5,
            'color': '#14532d', 'font-size': '9px', 'font-weight': '700',
            'text-valign': 'bottom', 'text-margin-y': '6px', 'text-halign': 'center',
            'width': '44px', 'height': '44px', 'shape': 'ellipse',
        }},
        // Événement fin négatif — cercle épais rouge
        { selector: 'node[type="fin_ko"]', style: {
            'label': 'data(label)', 'text-wrap': 'wrap', 'text-max-width': '80px',
            'background-color': '#fee2e2', 'border-color': '#dc2626', 'border-width': 5,
            'color': '#7f1d1d', 'font-size': '9px', 'font-weight': '700',
            'text-valign': 'bottom', 'text-margin-y': '6px', 'text-halign': 'center',
            'width': '44px', 'height': '44px', 'shape': 'ellipse',
        }},
    ];

    let isBpmn = false;

    const cy = cytoscape({
        container: cyContainer,
        elements,
        layout: { name: 'dagre', rankDir: 'LR', nodeSep: 50, rankSep: 130, edgeSep: 20, padding: 30 },
        style: [...customStyles, ...sharedStyles],
    });

    const toggleBtn = document.getElementById('cy-toggle-mode');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            isBpmn = !isBpmn;
            cy.style([...(isBpmn ? bpmnStyles : customStyles), ...sharedStyles]);
            toggleBtn.innerHTML = isBpmn
                ? '<i class="fas fa-palette mr-1"></i> Vue métier'
                : '<i class="fas fa-shapes mr-1"></i> Vue BPMN';
            toggleBtn.classList.toggle('bg-purple-50',  isBpmn);
            toggleBtn.classList.toggle('text-purple-700', isBpmn);
            toggleBtn.classList.toggle('border-purple-300', isBpmn);
            toggleBtn.classList.toggle('text-gray-600', !isBpmn);
            toggleBtn.classList.toggle('border-gray-300', !isBpmn);
        });
    }

    cy.on('tap', 'node', e => {
        const n = e.target;
        cy.elements().addClass('faded');
        n.removeClass('faded').addClass('hl');
        n.connectedEdges().removeClass('faded').addClass('hl');
        n.connectedEdges().connectedNodes().removeClass('faded');
    });
    cy.on('tap', e => { if (e.target === cy) cy.elements().removeClass('faded hl'); });

    document.getElementById('cy-fit')?.addEventListener('click',   () => cy.fit(undefined, 30));
    document.getElementById('cy-reset')?.addEventListener('click', () => { cy.elements().removeClass('faded hl'); cy.fit(undefined, 30); });
    document.getElementById('cy-download')?.addEventListener('click', () => {
        const a = document.createElement('a');
        a.href     = cy.png({ bg: 'white', scale: 2, full: true });
        a.download = 'circuit-{{ $selectedType ?? "commun" }}.png';
        a.click();
    });
});
</script>
@endpush

@section('content')

@php
    $selectedTypeLabel = $selectedType
        ? (\App\Models\TypeDemande::labelFor($selectedType) ?? $selectedType)
        : null;
    $openRequiredByDefault = ($requiredServices ?? collect())->isEmpty();
    $openSlaByDefault = ($slaRules ?? collect())->isEmpty();
@endphp

<div class="space-y-5">

    {{-- ── Header ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-lg font-bold text-gray-800">Circuit de traitement</h1>
                <p class="text-xs text-gray-400 mt-0.5">États (nœuds) et transitions (arêtes) par type de demande. Les dossiers déjà soumis conservent leur circuit figé.</p>
            </div>

            {{-- Sélecteur de type --}}
            <div class="flex items-center gap-2" x-data="{ showNewType: {{ $errors->hasAny(['type_code', 'type_label', 'description', 'clone_from']) ? 'true' : 'false' }}, showDeleteType: false }">
                <form method="GET" action="{{ route('admin.flux-transitions.index') }}">
                    <select name="type" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[220px]">
                        @foreach($typeDemandeOptions as $typeEnum)
                            <option value="{{ $typeEnum->value }}" {{ $selectedType === $typeEnum->value ? 'selected' : '' }}>
                                {{ $typeEnum->label }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <button type="button" @click="showNewType = true"
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors whitespace-nowrap">
                    <i class="fas fa-plus"></i> Nouveau type
                </button>
                <button type="button"
                    @if($canDeleteSelectedType)
                        @click="showDeleteType = true"
                        class="inline-flex items-center gap-1.5 px-3 py-2 border border-red-300 text-red-700 hover:bg-red-50 text-sm font-medium rounded-lg transition-colors whitespace-nowrap"
                    @else
                        disabled
                        title="Les types métier prédéfinis ne peuvent pas être supprimés."
                        class="inline-flex items-center gap-1.5 px-3 py-2 border border-gray-200 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed whitespace-nowrap"
                    @endif>
                    <i class="fas fa-trash"></i> Supprimer
                </button>

                <div x-show="showDeleteType" x-cloak
                     @keydown.escape.window="showDeleteType = false"
                     @click.self="showDeleteType = false"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                        <h3 class="font-semibold text-gray-800 mb-2">
                            <i class="fas fa-trash-alt mr-2 text-red-500"></i> Supprimer ce type
                        </h3>
                        <p class="text-sm text-gray-600 mb-5">
                            Supprimer <strong>{{ $selectedTypeLabel }}</strong> et son circuit de traitement ?
                            Cette action est irréversible.
                        </p>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showDeleteType = false"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Annuler</button>
                            <form method="POST" action="{{ route('admin.flux-transitions.types.destroy', $selectedType) }}">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">
                                    Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div x-show="showNewType" x-cloak
                     @keydown.escape.window="showNewType = false"
                     @click.self="showNewType = false"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6" @click.stop>
                        <h3 class="font-semibold text-gray-800 text-base mb-1">
                            <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Nouveau type de demande
                        </h3>
                        <p class="text-xs text-gray-400 mb-4">Le circuit sera propre à ce type. Vous pouvez copier un circuit existant comme point de départ.</p>
                        <form method="POST" action="{{ route('admin.flux-transitions.types.store') }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                                <input type="text" name="type_code" required maxlength="50" placeholder="ex : DEMANDE_CERTIFICAT"
                                    value="{{ old('type_code') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
                                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '')">
                                @error('type_code') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                                <input type="text" name="type_label" required maxlength="150" placeholder="Nom affiché"
                                    value="{{ old('type_label') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @error('type_label') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                <input type="text" name="description" maxlength="500" placeholder="Optionnel"
                                    value="{{ old('description') }}"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Copier le circuit de</label>
                                <select name="clone_from"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Circuit vide —</option>
                                    <option value="__global__" @selected(old('clone_from') === '__global__')>Modèle de base (étapes partagées)</option>
                                    @foreach($typeDemandeOptions as $typeEnum)
                                        <option value="{{ $typeEnum->value }}" @selected(old('clone_from') === $typeEnum->value)>{{ $typeEnum->label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex justify-end gap-2 pt-1">
                                <button type="button" @click="showNewType = false"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Annuler</button>
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                                    Créer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <p class="font-medium flex items-center gap-2 mb-1">
                <i class="fas fa-exclamation-circle text-red-500"></i> Veuillez corriger les erreurs suivantes :
            </p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!empty($healthIssues))
        <div class="bg-white rounded-xl border border-amber-200 shadow-sm p-4 space-y-2">
            <h2 class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                <i class="fas fa-heartbeat text-amber-500"></i> Santé du circuit
            </h2>
            <ul class="space-y-1.5">
                @foreach($healthIssues as $issue)
                    @php
                        $tone = match($issue['level'] ?? 'info') {
                            'error'   => 'text-red-700 bg-red-50 border-red-200',
                            'warning' => 'text-amber-800 bg-amber-50 border-amber-200',
                            default   => 'text-slate-600 bg-slate-50 border-slate-200',
                        };
                        $icon = match($issue['level'] ?? 'info') {
                            'error'   => 'fa-times-circle text-red-500',
                            'warning' => 'fa-exclamation-triangle text-amber-500',
                            default   => 'fa-info-circle text-slate-400',
                        };
                    @endphp
                    <li class="flex items-start gap-2 text-xs border rounded-lg px-3 py-2 {{ $tone }}">
                        <i class="fas {{ $icon }} mt-0.5"></i>
                        <span>{{ $issue['message'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Graph ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm"
         x-data="{ open: true }">
        <div class="flex items-center justify-between p-5 cursor-pointer select-none" @click="open = !open">
            <h2 class="text-sm font-semibold text-gray-700">
                <i class="fas fa-project-diagram mr-2 text-blue-400"></i> Graphe des états
                @if($selectedTypeLabel)
                    <span class="ml-1 text-xs font-normal text-blue-500">— {{ $selectedTypeLabel }}</span>
                @endif
            </h2>
            <div class="flex gap-2 items-center">
                <template x-if="open">
                    <div class="flex gap-2" @click.stop>
                        <button id="cy-toggle-mode"
                            class="text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-600 transition-colors">
                            <i class="fas fa-shapes mr-1"></i> Vue BPMN
                        </button>
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
                </template>
                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200 ml-2"
                   :class="open ? '' : '-rotate-90'"></i>
            </div>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="px-5 pb-5">
            <div class="flex items-center gap-4 mb-3 flex-wrap text-xs">
                <span class="flex items-center gap-1.5 text-amber-700 font-semibold">
                    <span class="inline-block w-5 h-3.5 rounded border-[3px] border-dashed border-amber-400 bg-amber-50"></span> Brouillon (début)
                </span>
                <span class="flex items-center gap-1.5 text-indigo-700 font-semibold">
                    <span class="inline-block w-4 h-4 rounded border-4 border-indigo-500 bg-indigo-50 ring-2 ring-indigo-200 ring-offset-1"></span> Soumission initiale (entrée circuit)
                </span>
                <span class="flex items-center gap-1.5 text-gray-500">
                    <span class="inline-block w-4 h-4 rounded border-2 border-blue-300 bg-blue-50"></span> État de traitement
                </span>
                <span class="flex items-center gap-1.5 text-gray-500">
                    <span class="inline-block w-4 h-4 rounded border-2 border-dashed border-blue-300 bg-blue-50"></span> État global (partagé)
                </span>
                <span class="flex items-center gap-1.5 text-orange-700 font-medium">
                    <span class="inline-block w-4 h-4 rounded border-[3px] border-orange-400 bg-orange-50"></span> Étape obligatoire
                </span>
                <span class="flex items-center gap-1.5 text-indigo-900 font-semibold">
                    <span class="inline-block w-4 h-4 rounded bg-indigo-900 ring-2 ring-indigo-400 ring-offset-1"></span> Décision finale (Direction)
                </span>
                <span class="flex items-center gap-1.5 text-green-700 font-semibold">
                    <span class="inline-block w-5 h-3.5 rounded bg-green-600 ring-2 ring-green-200 ring-offset-1"></span> Terminal positif (Approuvé / Clôturé)
                </span>
                <span class="flex items-center gap-1.5 text-red-700 font-semibold">
                    <span class="inline-block w-5 h-3.5 rounded bg-red-600 ring-2 ring-red-200 ring-offset-1"></span> Terminal négatif (Rejeté / Annulé)
                </span>
            </div>
            @if($steps->isEmpty())
                <div class="flex flex-col items-center justify-center h-40 text-gray-400 text-sm">
                    <i class="fas fa-project-diagram text-3xl mb-2"></i>
                    Aucun état défini pour ce type. Ajoutez des états ci-dessous.
                </div>
            @else
                <div id="cy" class="w-full rounded-lg border border-gray-100" style="height: 440px;"></div>
            @endif
        </div>
        </div>
    </div>

    {{-- ── États (nœuds) ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         x-data="{ open: true, showAddStep: false }">
        <div class="px-5 py-4 flex items-start justify-between cursor-pointer select-none" @click="open = !open">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-circle mr-2 text-blue-400 text-xs"></i> États du dossier (nœuds)
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Chaque état encode une étape métier <strong>et</strong> le service responsable.
                    La localisation du dossier est déduite de son état courant.
                </p>
            </div>
            <div class="flex items-center gap-3" @click.stop>
                <button type="button" @click="showAddStep = true"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus"></i> Ajouter un nœud
                </button>
                <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200 shrink-0"
                   :class="open ? '' : '-rotate-90'" @click="open = !open"></i>
            </div>
        </div>

        {{-- Modal ajout nœud --}}
        <div x-show="showAddStep" x-cloak
             @keydown.escape.window="showAddStep = false"
             @click.self="showAddStep = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
             x-data="addStepModal([])">
            <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6" @click.stop>
                <h3 class="font-semibold text-gray-800 text-base mb-4">
                    <i class="fas fa-plus-circle mr-2 text-blue-500"></i> Ajouter un nœud
                </h3>

                {{-- Onglets Nouveau / Existant --}}
                <div class="flex rounded-lg border border-gray-200 overflow-hidden mb-5 text-sm">
                    <button type="button" @click="tab = 'new'"
                        :class="tab === 'new' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="flex-1 py-2 font-medium transition-colors">
                        Nouveau nœud
                    </button>
                    @if($reusableSteps->isNotEmpty())
                    <button type="button" @click="tab = 'existing'"
                        :class="tab === 'existing' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                        class="flex-1 py-2 font-medium border-l border-gray-200 transition-colors">
                        Nœud existant
                    </button>
                    @endif
                </div>

                {{-- Onglet : nœud existant --}}
                @if($reusableSteps->isNotEmpty())
                <div x-show="tab === 'existing'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Choisir un nœud <span class="text-red-500">*</span></label>
                            <select x-model="selectedStepId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Sélectionner —</option>
                                @foreach($reusableSteps->groupBy(fn($s) => $s->type_demande ?? 'Commun') as $group => $groupSteps)
                                    <optgroup label="{{ $group === 'Commun' ? 'Modèle de base' : (\App\Models\TypeDemande::labelFor($group) ?? $group) }}">
                                        @foreach($groupSteps as $rs)
                                            <option value="{{ $rs->id }}">
                                                {{ $rs->nom }}{{ $rs->service ? ' (' . $rs->service->nom . ')' : '' }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" @click="showAddStep = false; reset()"
                                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Annuler</button>
                            <button type="button"
                                :disabled="!selectedStepId"
                                :class="!selectedStepId ? 'opacity-40 cursor-not-allowed' : 'hover:bg-blue-700'"
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg transition-colors"
                                @click="submitClone()">
                                <i class="fas fa-plus mr-1"></i> Ajouter
                            </button>
                        </div>

                        {{-- Formulaire caché pour la soumission --}}
                        <form id="clone-step-form" method="POST" style="display:none">
                            @csrf
                            <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                        </form>
                    </div>
                </div>
                @endif

                {{-- Onglet : nouveau nœud --}}
                <div x-show="tab === 'new'">
                <form method="POST" action="{{ route('admin.workflow-steps.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type_demande" value="{{ $selectedType }}">

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Code <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required placeholder="ex : EN_REVISION"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 uppercase"
                            oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9_]/g, '')">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Service</label>
                        <select name="service_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Aucun service —</option>
                            @foreach($services as $svc)
                                <option value="{{ $svc->id }}">{{ $svc->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="nom" required placeholder="Libellé court" value="{{ old('nom') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('nom') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}" placeholder="Rôle de cette étape dans le circuit"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('description') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Ordre</label>
                            <input type="number" name="ordre" value="{{ old('ordre', 50) }}" min="0" max="999"
                                class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Type de nœud <span class="text-red-500">*</span></label>
                            <select name="type_noeud" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(\App\Enums\WorkflowStepTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}" {{ old('type_noeud', \App\Enums\WorkflowStepTypeEnum::INTERMEDIAIRE->value) === $type->value ? 'selected' : '' }}>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @error('code') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                    <div class="flex justify-end gap-2 pt-1">
                        <button type="button" @click="showAddStep = false; reset()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Annuler</button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-plus mr-1"></i> Ajouter
                        </button>
                    </div>
                </form>
                </div>{{-- end tab new --}}
            </div>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Nom de l'état</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Service</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Description</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-16">Ordre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Portée</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-24">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($steps as $step)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800 text-sm">
                            {{ $step->nom }}
                            <span class="ml-1 text-[10px] text-gray-400 font-normal font-mono">{{ $step->code }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                {{ $step->service?->nom ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $step->description ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $step->ordre }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $typeNoeud = $step->type_noeud;
                                $typeClass = match($typeNoeud) {
                                    \App\Enums\WorkflowStepTypeEnum::INITIAL   => 'bg-indigo-100 text-indigo-700',
                                    \App\Enums\WorkflowStepTypeEnum::TERMINAL  => 'bg-red-100 text-red-700',
                                    default                                     => 'bg-yellow-100 text-yellow-700',
                                };
                            @endphp
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $typeClass }}">
                                {{ $typeNoeud?->label() ?? '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($step->type_demande)
                                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                    {{ \App\Models\TypeDemande::labelFor($step->type_demande) }}
                                </span>
                            @else
                                <span class="text-[10px] text-gray-400 italic">Global</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                @if($step->isInitial())
                                    <span class="text-xs text-gray-400 italic px-2">Système</span>
                                @else
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = true"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div x-show="open" x-cloak
                                         @keydown.escape.window="open = false"
                                         @click.self="open = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6" @click.stop>
                                            <h3 class="font-semibold text-gray-800 mb-1">Modifier le nœud</h3>
                                            <p class="text-xs text-gray-400 mb-4 font-mono">{{ $step->code }}</p>
                                            <form method="POST" action="{{ route('admin.workflow-steps.update', $step->id) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="type_demande" value="{{ $selectedType }}">

                                                <div class="mb-3">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                                                    <input type="text" name="nom" required value="{{ $step->nom }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Service</label>
                                                    <select name="service_id"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                        <option value="">— Aucun service —</option>
                                                        @foreach($services as $svc)
                                                            <option value="{{ $svc->id }}" {{ $step->service_id == $svc->id ? 'selected' : '' }}>
                                                                {{ $svc->nom }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                                                    <input type="text" name="description" value="{{ $step->description }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div class="mb-3 flex gap-4">
                                                    <div>
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Ordre</label>
                                                        <input type="number" name="ordre" value="{{ $step->ordre }}" min="0"
                                                            class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                    </div>
                                                    <div class="flex-1">
                                                        <label class="block text-xs font-medium text-gray-700 mb-1">Type de nœud <span class="text-red-500">*</span></label>
                                                        <select name="type_noeud" required
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                            @foreach(\App\Enums\WorkflowStepTypeEnum::cases() as $typeCase)
                                                                <option value="{{ $typeCase->value }}" {{ $step->type_noeud === $typeCase ? 'selected' : '' }}>
                                                                    {{ $typeCase->label() }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="open = false"
                                                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Annuler</button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = true"
                                        class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div x-show="open" x-cloak
                                         @keydown.escape.window="open = false"
                                         @click.self="open = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                            <h3 class="font-semibold text-gray-800 mb-3">Supprimer l'état</h3>
                                            <p class="text-sm text-gray-600 mb-5">
                                                Supprimer <strong>« {{ $step->nom }} »</strong> ? Les transitions liées seront également supprimées.
                                            </p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="open = false"
                                                    class="px-4 py-2 text-sm font-medium text-gray-600">Annuler</button>
                                                <form method="POST" action="{{ route('admin.workflow-steps.destroy', $step->id) }}">
                                                    @csrf @method('DELETE')
                                                    <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-circle text-3xl mb-2 block"></i>
                            Aucun état défini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- ── Transitions (arêtes) ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         x-data="{ open: true }">
        <div class="px-5 py-4 flex items-start justify-between cursor-pointer select-none" @click="open = !open">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-arrow-right mr-2 text-blue-400"></i> Transitions entre états (arêtes)
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Définissent quels enchaînements d'états sont autorisés et le libellé de l'action de transfert.
                </p>
            </div>
            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200 mt-1 shrink-0 ml-4"
               :class="open ? '' : '-rotate-90'"></i>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="px-5 py-4 border-t border-b border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('admin.flux-transitions.step-transitions.store') }}"
                  class="flex flex-wrap gap-3 items-end">
                @csrf
                <input type="hidden" name="type_demande" value="{{ $selectedType }}">

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">État source</label>
                    <select name="from_step_id"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                        <option value="">— Soumission initiale —</option>
                        @foreach($steps as $step)
                            @unless($step->isTerminal())
                                <option value="{{ $step->id }}">{{ $step->nom }}</option>
                            @endunless
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end pb-2 text-gray-400"><i class="fas fa-long-arrow-alt-right"></i></div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">État destination <span class="text-red-500">*</span></label>
                    <select name="to_step_id" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                        <option value="">— Choisir —</option>
                        @foreach($steps as $step)
                            @unless($step->isDraftEntry())
                                <option value="{{ $step->id }}">{{ $step->nom }}</option>
                            @endunless
                        @endforeach
                    </select>
                    @error('to_step_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Action <span class="text-red-500">*</span></label>
                    <input type="text" name="action" required placeholder="ex : Transmettre, Valider…"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-44">
                </div>

                <div class="flex items-end gap-2">
                    <label class="flex items-center gap-1.5 text-xs text-gray-600 pb-2.5 cursor-pointer">
                        <input type="checkbox" name="is_urgent_only" value="1" class="rounded text-orange-500">
                        Urgent uniquement
                    </label>
                </div>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors self-end">
                    <i class="fas fa-plus mr-1"></i> Ajouter
                </button>
            </form>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-3 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-8">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">État source</th>
                    <th class="px-2 py-3 w-6"></th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">État destination</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Action</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-24">Ordre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-24">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stepTransitions as $i => $t)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-3 py-3 text-center text-gray-400 text-xs">{{ $i + 1 }}</td>
                        <td class="px-4 py-3">
                            @if($t->from_step_id)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                    {{ $t->fromStep?->nom ?? '?' }}
                                </span>
                                @if($t->fromStep?->service)
                                    <span class="text-[10px] text-gray-400 ml-1">{{ $t->fromStep->service->nom }}</span>
                                @endif
                            @else
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-900 text-white italic">Soumission initiale</span>
                            @endif
                        </td>
                        <td class="px-2 py-3 text-gray-400 text-center"><i class="fas fa-arrow-right text-xs"></i></td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                {{ $t->toStep?->nom ?? '?' }}
                            </span>
                            @if($t->toStep?->service)
                                <span class="text-[10px] text-gray-400 ml-1">{{ $t->toStep->service->nom }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-xs font-medium">
                            {{ $t->action }}
                            @if($t->is_urgent_only)
                                <span class="ml-1 inline-flex items-center gap-0.5 text-[10px] px-1.5 py-0.5 rounded-full bg-orange-100 text-orange-700 font-semibold">
                                    <i class="fas fa-bolt text-[9px]"></i> Urgent
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <form method="POST" action="{{ route('admin.flux-transitions.step-transitions.move-up', $t->id) }}">
                                    @csrf
                                    <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                    <button type="submit" class="text-gray-400 hover:text-blue-600" title="Monter">
                                        <i class="fas fa-chevron-up text-xs"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.flux-transitions.step-transitions.move-down', $t->id) }}">
                                    @csrf
                                    <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                    <button type="submit" class="text-gray-400 hover:text-blue-600" title="Descendre">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                {{-- Edit --}}
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = true"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <div x-show="open" x-cloak
                                         @keydown.escape.window="open = false"
                                         @click.self="open = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                            <h3 class="font-semibold text-gray-800 mb-4">Modifier la transition</h3>
                                            <form method="POST" action="{{ route('admin.flux-transitions.step-transitions.update', $t->id) }}">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                                <div class="mb-3">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                                                    <input type="text" name="action" required value="{{ $t->action }}"
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                                </div>
                                                <div class="mb-5">
                                                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                                        <input type="checkbox" name="is_urgent_only" value="1"
                                                            {{ $t->is_urgent_only ? 'checked' : '' }}
                                                            class="rounded text-orange-500">
                                                        Urgent uniquement
                                                    </label>
                                                </div>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" @click="open = false"
                                                        class="px-4 py-2 text-sm font-medium text-gray-600">Annuler</button>
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Enregistrer</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {{-- Delete --}}
                                <div x-data="{ open: false }">
                                    <button type="button" @click="open = true"
                                        class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <div x-show="open" x-cloak
                                         @keydown.escape.window="open = false"
                                         @click.self="open = false"
                                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                            <h3 class="font-semibold text-gray-800 mb-3">Supprimer la transition</h3>
                                            <p class="text-sm text-gray-600 mb-5">
                                                Supprimer <strong>« {{ $t->action }} »</strong>
                                                ({{ $t->fromStep?->nom ?? 'Soumission initiale' }} → {{ $t->toStep?->nom ?? '?' }}) ?
                                            </p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" @click="open = false"
                                                    class="px-4 py-2 text-sm font-medium text-gray-600">Annuler</button>
                                                <form method="POST" action="{{ route('admin.flux-transitions.step-transitions.destroy', $t->id) }}">
                                                    @csrf @method('DELETE')
                                                    <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                                    <button type="submit"
                                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Supprimer</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-arrow-right text-3xl mb-2 block"></i>
                            Aucune transition définie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- ── Étapes obligatoires ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         x-data="{ open: {{ $openRequiredByDefault ? 'true' : 'false' }} }">
        <div class="px-5 py-4 flex items-start justify-between cursor-pointer select-none" @click="open = !open">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-shield-alt mr-2 text-orange-400"></i> Étapes obligatoires
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $requiredServices->count() }})</span>
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Un dossier ne peut être approuvé que si tous ces services l'ont traité.
                </p>
            </div>
            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200 mt-1 shrink-0 ml-4"
               :class="open ? '' : '-rotate-90'"></i>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('admin.flux-transitions.required.store') }}"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span></label>
                    <select name="service_id" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[180px]">
                        <option value="">— Choisir —</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->nom }}</option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande</label>
                    <select name="type_demande"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                        <option value="">— Tous les types —</option>
                        @foreach($typeDemandeOptions as $type)
                            <option value="{{ $type->value }}" @selected($selectedType === $type->value)>{{ $type->label }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1"></i> Ajouter
                </button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Service</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-20">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requiredServices as $req)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">{{ $req->service->nom }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($req->type_demande)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                    {{ \App\Models\TypeDemande::labelFor($req->type_demande) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Tous</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div x-data="{ open: false }">
                                <button type="button" @click="open = true"
                                    class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div x-show="open" x-cloak
                                     @keydown.escape.window="open = false"
                                     @click.self="open = false"
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                        <h3 class="font-semibold text-gray-800 mb-3">Supprimer</h3>
                                        <p class="text-sm text-gray-600 mb-5">
                                            Supprimer <strong>{{ $req->service->nom }}</strong>
                                            @if($req->type_demande) pour <strong>{{ \App\Models\TypeDemande::labelFor($req->type_demande) }}</strong>@else (tous les types)@endif ?
                                        </p>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false"
                                                class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                                            <form method="POST" action="{{ route('admin.flux-transitions.required.destroy', $req->id) }}">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                                <button type="submit"
                                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-10 text-center text-gray-400">Aucune étape obligatoire.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- ── Délais SLA ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden"
         x-data="{ open: {{ $openSlaByDefault ? 'true' : 'false' }} }">
        <div class="px-5 py-4 flex items-start justify-between cursor-pointer select-none" @click="open = !open">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-clock mr-2 text-teal-500"></i> Délais SLA
                    <span class="ml-1 text-xs font-normal text-gray-400">({{ $slaRules->count() }})</span>
                </h2>
                <p class="text-xs text-gray-400 mt-0.5">
                    Délai maximal (en jours) de traitement par service avant alerte.
                </p>
            </div>
            <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform duration-200 mt-1 shrink-0 ml-4"
               :class="open ? '' : '-rotate-90'"></i>
        </div>

        <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        <div class="px-5 py-4 border-b border-gray-100 bg-gray-50">
            <form method="POST" action="{{ route('admin.flux-transitions.sla.store') }}"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Service <span class="text-red-500">*</span></label>
                    <select name="service_id" required
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 min-w-[180px]">
                        <option value="">— Choisir —</option>
                        @foreach($services as $svc)
                            <option value="{{ $svc->id }}">{{ $svc->nom }}</option>
                        @endforeach
                    </select>
                    @error('service_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type de demande</label>
                    <select name="type_demande"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 min-w-[200px]">
                        <option value="">— Tous les types —</option>
                        @foreach($typeDemandeOptions as $type)
                            <option value="{{ $type->value }}" @selected($selectedType === $type->value)>{{ $type->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Délai (jours) <span class="text-red-500">*</span></label>
                    <input type="number" name="delai_jours" required min="1" max="365" value="{{ old('delai_jours', 5) }}"
                        class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500">
                    @error('delai_jours') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                    class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1"></i> Enregistrer
                </button>
            </form>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Service</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Délai</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center w-20">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slaRules as $sla)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-teal-100 text-teal-700">{{ $sla->service->nom }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($sla->type_demande)
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                    {{ \App\Models\TypeDemande::labelFor($sla->type_demande) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Tous</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-700 text-sm font-medium">
                            {{ $sla->delai_jours }} jour{{ $sla->delai_jours > 1 ? 's' : '' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div x-data="{ open: false }">
                                <button type="button" @click="open = true"
                                    class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <div x-show="open" x-cloak
                                     @keydown.escape.window="open = false"
                                     @click.self="open = false"
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                    <div class="bg-white w-full max-w-sm rounded-xl shadow-xl p-6" @click.stop>
                                        <h3 class="font-semibold text-gray-800 mb-3">Supprimer le SLA</h3>
                                        <p class="text-sm text-gray-600 mb-5">
                                            Supprimer le délai de <strong>{{ $sla->delai_jours }} j</strong> pour
                                            <strong>{{ $sla->service->nom }}</strong> ?
                                        </p>
                                        <div class="flex justify-end gap-2">
                                            <button type="button" @click="open = false"
                                                class="px-4 py-2 text-sm text-gray-600">Annuler</button>
                                            <form method="POST" action="{{ route('admin.flux-transitions.sla.destroy', $sla->id) }}">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="type_demande" value="{{ $selectedType }}">
                                                <button type="submit"
                                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-gray-400">Aucun délai SLA configuré.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

</div>

<script>
function addStepModal(etats) {
    return {
        tab: 'new',
        selectedStepId: '',

        reset() {
            this.tab = 'new';
            this.selectedStepId = '';
        },

        submitClone() {
            if (!this.selectedStepId) return;
            const form = document.getElementById('clone-step-form');
            form.action = '{{ url('admin/workflow-steps') }}/' + this.selectedStepId + '/clone';
            form.submit();
        },
    };
}

</script>
@endsection
