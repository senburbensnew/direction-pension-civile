#!/usr/bin/env python3
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

PATCHES = [
    (
        "resources/views/admin/carousel/index.blade.php",
        "@section('content')\n",
        "@section('content')\n<x-push-vite :js=\"['resources/js/admin/drag-reorder-grid.js']\" />\n",
        'id="carousel-grid" class="flex flex-wrap gap-5"',
        'id="carousel-grid" class="flex flex-wrap gap-5" data-dpc-drag-reorder data-card-selector=".carousel-card" '
        'data-reorder-url="{{ route(\'admin.carousels.reorder\') }}" data-reorder-flip="1"',
    ),
    (
        "resources/views/admin/partenaires/index.blade.php",
        "@section('content')\n",
        "@section('content')\n<x-push-vite :js=\"['resources/js/admin/drag-reorder-grid.js']\" />\n",
        'id="partenaires-grid" class="flex flex-wrap gap-5"',
        'id="partenaires-grid" class="flex flex-wrap gap-5" data-dpc-drag-reorder data-card-selector=".part-card" '
        'data-reorder-url="{{ route(\'admin.partenaires.reorder\') }}" data-reorder-flip="1"',
    ),
    (
        "resources/views/admin/institution-images/index.blade.php",
        "@section('content')\n",
        "@section('content')\n<x-push-vite :js=\"['resources/js/admin/drag-reorder-grid.js']\" />\n",
        'id="institution-images-grid" class="flex flex-wrap gap-5"',
        'id="institution-images-grid" class="flex flex-wrap gap-5" data-dpc-drag-reorder data-card-selector=".img-card" '
        'data-reorder-url="{{ route(\'admin.institution-images.reorder\') }}" data-reorder-flip="1"',
    ),
]


def strip_style_script(path: Path) -> None:
    text = path.read_text(encoding="utf-8")
    while "\n<style>" in text:
        start = text.index("\n<style>")
        end = text.index("</style>", start) + len("</style>")
        text = text[:start] + text[end:]
    while "\n<script>" in text:
        start = text.index("\n<script>")
        end = text.index("</script>", start) + len("</script>")
        text = text[:start] + text[end:]
    path.write_text(text, encoding="utf-8")


def main() -> None:
    for rel, needle, insert, old_grid, new_grid in PATCHES:
        path = ROOT / rel
        text = path.read_text(encoding="utf-8")
        if "data-dpc-drag-reorder" not in text:
            if needle in text and insert not in text:
                text = text.replace(needle, insert, 1)
            text = text.replace(old_grid, new_grid, 1)
            path.write_text(text, encoding="utf-8")
        strip_style_script(path)
        print("patched", rel)


if __name__ == "__main__":
    main()
