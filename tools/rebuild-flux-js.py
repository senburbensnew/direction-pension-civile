from pathlib import Path

blade = Path("resources/views/admin/flux-transitions/index.blade.php").read_text(encoding="utf-8")
start = blade.index("    // Dédupliquer les nœuds")
end = blade.index("    document.getElementById('cy-fit')")
tail_end = blade.index("});", end) + 3
tail = blade[end:tail_end]
tail = tail.replace(
    "a.download = 'circuit-{{ $selectedType ?? \"commun\" }}.png';",
    'a.download = `circuit-${config.downloadSlug || "commun"}.png`;',
)

js_body = blade[start:end]
js_body = (
    "    const cyNodes = config.nodes || [];\n"
    "    const cyEdges = config.edges || [];\n\n"
    + js_body
)

header = """import cytoscape from 'cytoscape';
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

"""

footer = """
});

window.addStepModal = function addStepModal() {
    const form = document.getElementById('clone-step-form');
    const cloneBase = (form?.dataset?.cloneStepsBase || '').replace(/\\/$/, '');
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
"""

out = header + js_body + tail + footer
Path("resources/js/admin/flux-transitions-index.js").write_text(out, encoding="utf-8")
print("bytes", len(out))
