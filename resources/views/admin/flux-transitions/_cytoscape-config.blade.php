@php
    $requiredServiceIds = $requiredServices->pluck('service_id')->unique()->values();

    $cyNodes = $steps->map(function ($step) use ($requiredServiceIds) {
        $isRequired = $step->service_id && $requiredServiceIds->contains($step->service_id);

        $nodeType = match (true) {
            $step->isInitial() && $step->code === 'BROUILLON' => 'start_draft',
            $step->isInitial() => 'start_submit',
            $step->isTerminal() => str_contains(\App\Models\WorkflowStep::getStatusStyle($step->code), 'red') ? 'fin_ko' : 'fin_ok',
            in_array($step->code, ['DECISION_FINALE', 'EN_DECISION']) => 'end_decision',
            default => 'step',
        };

        $etatLabel = $step->nom;
        $label = $step->service
            ? $etatLabel."\n(".$step->service->nom.')'
            : $etatLabel;

        return [
            'data' => [
                'id' => 's'.$step->id,
                'label' => $label,
                'stepName' => $step->nom,
                'serviceName' => $step->service?->nom ?? '',
                'type' => $nodeType,
                'required' => $isRequired ? 1 : 0,
                'isGlobal' => $step->type_demande === null ? 1 : 0,
            ],
        ];
    });

    $terminalOkCodes = ['APPROUVEE', 'FINALISEE'];
    $terminalKoCodes = ['REJETEE', 'ANNULEE'];

    $cyEdges = collect();
    foreach ($stepTransitions as $i => $t) {
        $src = $t->from_step_id ? 's'.$t->from_step_id : 'INIT';
        $dst = 's'.$t->to_step_id;
        $toCode = $t->toStep?->code ?? '';
        $cyEdges->push(['data' => [
            'id' => 'e'.$i,
            'source' => $src,
            'target' => $dst,
            'label' => $t->action.($t->is_urgent_only ? "\n⚡ urgent" : ''),
            'isTerminalOk' => in_array($toCode, $terminalOkCodes),
            'isTerminalKo' => in_array($toCode, $terminalKoCodes),
        ]]);
    }

    $hasInitTransition = $stepTransitions->whereNull('from_step_id')->isNotEmpty();
    $nodes = $cyNodes->values()->all();

    if ($hasInitTransition) {
        array_unshift($nodes, [
            'data' => [
                'id' => 'INIT',
                'label' => "Soumission\ninitiale",
                'stepName' => 'Soumission initiale',
                'serviceName' => '',
                'type' => 'init',
                'required' => 0,
                'isGlobal' => 0,
            ],
        ]);
    }

    $seen = [];
    $uniqueNodes = [];
    foreach ($nodes as $node) {
        $id = $node['data']['id'] ?? null;
        if ($id === null || isset($seen[$id])) {
            continue;
        }
        $seen[$id] = true;
        $uniqueNodes[] = $node;
    }

    $fluxCytoscapeConfig = [
        'nodes' => $uniqueNodes,
        'edges' => $cyEdges->values()->all(),
        'downloadSlug' => $selectedType ?? 'commun',
    ];
@endphp
<script type="application/json" id="flux-transitions-config">@json($fluxCytoscapeConfig)</script>
