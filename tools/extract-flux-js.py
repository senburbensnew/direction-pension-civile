import re
import pathlib

s = pathlib.Path("resources/views/admin/flux-transitions/index.blade.php").read_text(
    encoding="utf-8"
)
start = s.index("document.addEventListener('DOMContentLoaded'")
end = s.index("});", start) + 3
body = s[start:end]
body = re.sub(r"@php.*?@endphp", "", body, flags=re.S)
body = re.sub(r"@if\(\$hasInitTransition\).*?@endif\s*", "", body, flags=re.S)
body = body.replace(
    "const cyNodes = {!! json_encode($cyNodes->values()) !!};",
    "const cyNodes = config.nodes || [];",
)
body = body.replace(
    "const cyEdges = {!! json_encode($cyEdges->values()) !!};",
    "const cyEdges = config.edges || [];",
)
body = body.replace("document.addEventListener('DOMContentLoaded', function () {", "")
body = body.replace(
    "const cyContainer = document.getElementById('cy');\n    if (!cyContainer) return;\n\n    cytoscape.use(cytoscapeDagre);\n\n    \n\n    \n",
    "",
)
body = body.replace(
    "a.download = 'circuit-{{ $selectedType ?? \"commun\" }}.png';",
    'a.download = `circuit-${config.downloadSlug || "commun"}.png`;',
)
lines = body.strip().splitlines()
if lines and lines[-1].strip() == "});":
    lines = lines[:-1]

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

    cytoscape.use(cytoscapeDagre);

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

out = header + "\n".join(lines) + footer
pathlib.Path("resources/js/admin/flux-transitions-index.js").write_text(out, encoding="utf-8")
print("written", len(out))
