import cytoscape from 'cytoscape';
import dagre from 'dagre';
import cytoscapeDagre from 'cytoscape-dagre';

cytoscape.use(cytoscapeDagre);
window.dagre = dagre;

function readFluxConfig() {
    const el = document.getElementById('flux-transitions-config');
    if (!el) {
        return null;
    }
    try {
        return JSON.parse(el.textContent);
    } catch {
        return null;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const cyContainer = document.getElementById('cy');
    const config = readFluxConfig();
    if (!cyContainer || !config) {
        return;
    }

    const cyNodes = config.nodes || [];
    const cyEdges = config.edges || [];

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
});

window.addStepModal = function addStepModal() {
    const form = document.getElementById('clone-step-form');
    const cloneBase = (form?.dataset?.cloneStepsBase || '').replace(/\/$/, '');
    return {
        tab: 'new',
        selectedStepId: '',
        reset() {
            this.tab = 'new';
            this.selectedStepId = '';
        },
        submitClone() {
            if (!this.selectedStepId || !form || !cloneBase) {
                return;
            }
            form.action = cloneBase + '/' + this.selectedStepId + '/clone';
            form.submit();
        },
    };
};
