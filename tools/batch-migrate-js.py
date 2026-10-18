#!/usr/bin/env python3
"""One-off helpers for JS → Vite migration (extract / trim blade scripts)."""
from __future__ import annotations

import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def trim_lines(path: Path, keep_before: int, keep_after: int) -> None:
    """Keep lines [1..keep_before] and [keep_after..end] (1-based inclusive cuts)."""
    lines = path.read_text(encoding="utf-8").splitlines(keepends=True)
    new = lines[:keep_before] + lines[keep_after:]
    path.write_text("".join(new), encoding="utf-8")
    print(f"trimmed {path.relative_to(ROOT)}: removed lines {keep_before + 1}-{keep_after}")


def extract_script_inner(blade: Path, out_js: Path, wrap: str) -> None:
    text = blade.read_text(encoding="utf-8")
    m = re.search(r"<script>\s*(.*?)\s*</script>\s*$", text, re.DOTALL | re.MULTILINE)
    if not m and "<script>" in text:
        # last script block
        parts = text.rsplit("<script>", 1)
        if len(parts) == 2:
            inner = parts[1].rsplit("</script>", 1)[0]
        else:
            raise SystemExit(f"No script in {blade}")
    elif m:
        inner = m.group(1)
    else:
        raise SystemExit(f"No script in {blade}")

    body = inner.strip()
    if wrap == "register_rdv":
        body = body.replace("document.addEventListener('alpine:init', () => {", "")
        body = body.replace("document.addEventListener(\"alpine:init\", () => {", "")
        body = body.rstrip()
        if body.endswith("});"):
            body = body[:-3].rstrip()
        if body.endswith("});"):
            body = body[:-3].rstrip()
        content = f"""export function registerRdvBookingForm(Alpine) {{
{body}
}}
"""
    elif wrap == "boot":
        content = f"""export function bootPage() {{
{body}
}}

if (document.readyState === 'loading') {{
    document.addEventListener('DOMContentLoaded', bootPage);
}} else {{
    bootPage();
}}
"""
    elif wrap == "init_home_graphs":
        content = f"""export async function initHomeGraphs() {{
    const d3 = await import('d3');
{body}
}}
"""
    else:
        content = body + "\n"

    out_js.parent.mkdir(parents=True, exist_ok=True)
    out_js.write_text(content, encoding="utf-8")
    print(f"wrote {out_js.relative_to(ROOT)}")


def main() -> None:
    # demande-compte: drop duplicate inline Alpine component (lines 1840-3589)
    dc = ROOT / "resources/views/auth/demande-compte.blade.php"
    if "function demandeCompteForm(initial)" in dc.read_text(encoding="utf-8"):
        trim_lines(dc, 1839, 3589)

    # rdv booking alpine block (lines 839-964 in file — verify after edits)
    rdv = ROOT / "resources/views/demandes/rencontre/create.blade.php"
    rdv_text = rdv.read_text(encoding="utf-8")
    if "document.addEventListener('alpine:init'" not in rdv_text:
        print("rdv create: already migrated")
    else:
        _extract_rdv(rdv_text, rdv)
    extract_graph_section()


def _extract_rdv(rdv_text: str, rdv: Path) -> None:
    start = rdv_text.find("    <script>\n\n        document.addEventListener('alpine:init'")
    end = rdv_text.find("    </script>\n\n    @if(isset($rdvActif)", start)
    if start == -1 or end == -1:
        raise SystemExit("rdv script markers not found")
    inner = rdv_text[start + len("    <script>\n\n") : end]
    out = ROOT / "resources/js/pages/rdv-booking-form.js"
    body = inner.strip()
    body = body.replace("document.addEventListener('alpine:init', () => {", "").strip()
    if body.endswith("});"):
        body = body[:-3].strip()
    out.write_text(
        "export function registerRdvBookingForm(Alpine) {\n" + body + "\n}\n",
        encoding="utf-8",
    )
    rdv.write_text(rdv_text[:start] + rdv_text[end + len("    </script>\n") :], encoding="utf-8")
    print("extracted rdv-booking-form.js and trimmed create.blade.php")


def extract_graph_section() -> None:
    gs = ROOT / "resources/views/components/graph-section.blade.php"
    gs_text = gs.read_text(encoding="utf-8")
    marker = '<script src="https://d3js.org/d3.v7.min.js"></script>'
    if marker not in gs_text:
        print("graph-section: already migrated")
        return
    start = gs_text.index(marker)
    script_open = gs_text.index("<script>", start)
    script_close = gs_text.index("</script>", script_open)
    inner = gs_text[script_open + len("<script>") : script_close].strip()
    inner = re.sub(
        r"^document\.addEventListener\('DOMContentLoaded', function\(\) \{\s*",
        "",
        inner,
    )
    inner = re.sub(r"\}\);\s*$", "", inner).strip()
    out_gs = ROOT / "resources/js/components/home-graphs.js"
    body = "\n".join(("    " + line) if line.strip() else line for line in inner.splitlines())
    out_gs.write_text(
        "export async function initHomeGraphs() {\n"
        "    const d3 = await import('d3');\n"
        f"{body}\n"
        "}\n",
        encoding="utf-8",
    )
    gs_text = gs_text[:start] + gs_text[script_close + len("</script>") :]
    if "data-dpc-home-graphs" not in gs_text:
        gs_text = gs_text.replace(
            '<section class="py-12 bg-gray-50 fade-in">',
            '<section class="py-12 bg-gray-50 fade-in" data-dpc-home-graphs>',
            1,
        )
    gs.write_text(gs_text, encoding="utf-8")
    print("extracted home-graphs.js and trimmed graph-section.blade.php")


if __name__ == "__main__":
    main()
