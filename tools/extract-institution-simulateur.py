#!/usr/bin/env python3
from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]


def strip_script_after_section(blade: Path, out_js: Path, global_fns: list[str] | None = None) -> None:
    text = blade.read_text(encoding="utf-8")
    idx = text.find("\n<script>")
    if idx == -1:
        print(f"skip {blade.name}: no script")
        return
    inner = text[idx + len("\n<script>") : text.rfind("</script>")].strip()
    wrapped = (
        "function bootPage() {\n"
        + "\n".join("    " + line if line.strip() else line for line in inner.splitlines())
        + "\n}\n\n"
        "if (document.readyState === 'loading') {\n"
        "    document.addEventListener('DOMContentLoaded', bootPage);\n"
        "} else {\n"
        "    bootPage();\n"
        "}\n"
    )
    if global_fns:
        pre = ""
        for fn in global_fns:
            if f"function {fn}" in inner:
                pre += f"window.{fn} = {fn};\n"
        wrapped = wrapped.replace("function bootPage()", pre + "function bootPage()", 1)
    out_js.parent.mkdir(parents=True, exist_ok=True)
    out_js.write_text(wrapped, encoding="utf-8")
    blade.write_text(text[:idx] + "\n", encoding="utf-8")
    print(f"extracted {out_js.name} from {blade.name}")


def strip_script_in_section(blade: Path, out_js: Path) -> None:
    text = blade.read_text(encoding="utf-8")
    # remove CDN script tags before inline
    while '<script src="' in text:
        start = text.index('<script src="')
        end = text.index("</script>", start) + len("</script>")
        text = text[:start] + text[end:]

    idx = text.rfind("\n    <script>")
    if idx == -1:
        idx = text.rfind("\n<script>")
    if idx == -1:
        print(f"skip {blade.name}: no inline script")
        return
    inner = text[idx + len("\n    <script>") : text.rfind("</script>")].strip()
    if inner.startswith("<script>"):
        inner = inner[len("<script>") :].strip()
    body = "\n".join("    " + line if line.strip() else line for line in inner.splitlines())
    content = (
        "export async function bootSimulateurCalcul() {\n"
        f"{body}\n"
        "}\n\n"
        "if (document.readyState === 'loading') {\n"
        "    document.addEventListener('DOMContentLoaded', bootSimulateurCalcul);\n"
        "} else {\n"
        "    bootSimulateurCalcul();\n"
        "}\n"
    )
    out_js.write_text(content, encoding="utf-8")
    new_text = text[:idx].rstrip() + "\n@endsection\n"
    if "@endsection" not in new_text:
        new_text = text[:idx].rstrip() + "\n"
    else:
        # already had @endsection before script — fix duplicate
        new_text = text[:idx].rstrip() + "\n@endsection\n"
    blade.write_text(new_text, encoding="utf-8")
    print(f"extracted {out_js.name} from {blade.name}")


def main() -> None:
    strip_script_after_section(
        ROOT / "resources/views/institution/demande-adhesion.blade.php",
        ROOT / "resources/js/pages/institution-demande-adhesion.js",
        global_fns=["deleteDocument"],
    )
    # pension standard: two script blocks — merge by reading file manually if needed
    ps = ROOT / "resources/views/institution/demande-pension-standard.blade.php"
    ps_text = ps.read_text(encoding="utf-8")
    parts = ps_text.split("<script>")
    if len(parts) >= 3:
        merged = parts[1].rsplit("</script>", 1)[0].strip() + "\n\n" + parts[2].rsplit("</script>", 1)[0].strip()
        out = ROOT / "resources/js/pages/institution-pension-standard.js"
        body = "\n".join("    " + line if line.strip() else line for line in merged.splitlines())
        out.write_text(
            "function bootPage() {\n" + body + "\n}\n\n"
            "if (document.readyState === 'loading') {\n"
            "    document.addEventListener('DOMContentLoaded', bootPage);\n"
            "} else {\n"
            "    bootPage();\n"
            "}\n"
            "window.deleteDocument = deleteDocument;\n",
            encoding="utf-8",
        )
        ps.write_text(parts[0].rstrip() + "\n", encoding="utf-8")
        print("extracted institution-pension-standard.js")

    rev = ROOT / "resources/views/institution/demande-pension-reversion.blade.php"
    rev_text = rev.read_text(encoding="utf-8")
    if "<script>" in rev_text:
        inner = rev_text.split("<script>", 1)[1].rsplit("</script>", 1)[0].strip()
        out = ROOT / "resources/js/pages/institution-pension-reversion.js"
        body = "\n".join("    " + line if line.strip() else line for line in inner.splitlines())
        out.write_text(
            "function bootPage() {\n" + body + "\n}\n\n"
            "if (document.readyState === 'loading') {\n"
            "    document.addEventListener('DOMContentLoaded', bootPage);\n"
            "} else {\n"
            "    bootPage();\n"
            "}\n"
            "window.deleteDocument = deleteDocument;\n",
            encoding="utf-8",
        )
        rev.write_text(rev_text.split("<script>", 1)[0].rstrip() + "\n", encoding="utf-8")
        print("extracted institution-pension-reversion.js")

    strip_script_in_section(
        ROOT / "resources/views/fonctionnaire/simulateur-calcul.blade.php",
        ROOT / "resources/js/pages/simulateur-calcul.js",
    )


if __name__ == "__main__":
    main()
