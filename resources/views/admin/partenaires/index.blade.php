@extends('layouts.admin')

@section('title', 'Nos Partenaires')
@section('breadcrumb') Nos Partenaires @endsection

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Nos Partenaires</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $partenaires->count() }} partenaire(s) au total</p>
        </div>
        <a href="{{ route('admin.partenaires.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus text-xs"></i> Ajouter un partenaire
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($partenaires->count() > 1)
        <div id="banner-hint" class="flex items-center gap-2 px-4 py-2.5 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-sm">
            <i class="fas fa-grip-vertical"></i> Glissez-deposez les cartes pour modifier l'ordre d'affichage.
        </div>
        <div id="banner-saving" class="hidden items-center gap-2 px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-700 rounded-lg text-sm">
            <i class="fas fa-spinner fa-spin"></i> Enregistrement...
        </div>
        <div id="banner-saved" class="hidden items-center gap-2 px-4 py-2.5 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">
            <i class="fas fa-check-circle"></i> Ordre enregistre.
        </div>
    @endif

    @if($partenaires->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 py-16 flex flex-col items-center text-center gap-3">
            <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fas fa-handshake text-2xl text-gray-300"></i>
            </div>
            <p class="text-gray-500 font-medium">Aucun partenaire pour le moment</p>
            <a href="{{ route('admin.partenaires.create') }}" class="text-sm text-blue-600 hover:underline">
                Ajouter le premier partenaire
            </a>
        </div>
    @else
        <div id="partenaires-grid" class="flex flex-wrap gap-5">
            @foreach($partenaires as $p)
            <div class="part-card bg-white rounded-xl border-2 border-gray-200 overflow-hidden shadow-sm transition-all flex flex-col select-none"
                 style="width: calc((100% - 40px) / 3);"
                 draggable="true"
                 data-id="{{ $p->id }}">

                <div class="relative h-36 bg-gray-50 flex items-center justify-center overflow-hidden p-4">
                    @if($p->logoUrl())
                        <img src="{{ $p->logoUrl() }}"
                             alt="{{ $p->name }}"
                             class="max-h-24 max-w-full object-contain pointer-events-none">
                    @else
                        <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-building text-2xl text-gray-400"></i>
                        </div>
                    @endif
                    <span class="order-badge absolute top-2 left-2 bg-black/50 text-white text-xs font-bold px-2 py-0.5 rounded-full backdrop-blur-sm">
                        #{{ $p->order }}
                    </span>
                    <span class="absolute top-2 right-2 text-xs font-semibold px-2 py-0.5 rounded-full
                        {{ $p->active ? 'bg-green-500 text-white' : 'bg-gray-400 text-white' }}">
                        {{ $p->active ? 'Actif' : 'Inactif' }}
                    </span>
                </div>

                <div class="px-4 py-3 flex-1">
                    <p class="text-sm font-semibold text-gray-800 line-clamp-2">{{ $p->name }}</p>
                    @if($p->website_url)
                        <a href="{{ $p->website_url }}" target="_blank"
                           class="text-xs text-blue-500 hover:underline flex items-center gap-1 mt-1">
                            <i class="fas fa-external-link-alt text-[10px]"></i> Site web
                        </a>
                    @endif
                </div>

                <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between gap-2">
                    <span class="text-gray-300 cursor-grab"><i class="fas fa-grip-vertical text-base"></i></span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.partenaires.edit', $p->id) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                            <i class="fas fa-pencil-alt text-[10px]"></i> Modifier
                        </a>
                        <form action="{{ route('admin.partenaires.destroy', $p->id) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce partenaire ?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors">
                                <i class="fas fa-trash-alt text-[10px]"></i> Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif

</div>

<style>
.part-card { cursor: grab; }
.part-card:active { cursor: grabbing; }
.part-card.dragging { opacity: 0.35; border-color: #93c5fd !important; }
.part-card.drop-before { border-left-color: #2563eb !important; border-left-width: 4px !important; }
.part-card.drop-after  { border-right-color: #2563eb !important; border-right-width: 4px !important; }
</style>

<script>
(function () {
    const grid = document.getElementById('partenaires-grid');
    if (!grid) return;
    const bannerHint   = document.getElementById('banner-hint');
    const bannerSaving = document.getElementById('banner-saving');
    const bannerSaved  = document.getElementById('banner-saved');

    function showBanner(el, autohide = 0) {
        [bannerHint, bannerSaving, bannerSaved].forEach(b => {
            if (!b) return;
            b.classList.add('hidden'); b.classList.remove('flex');
        });
        if (!el) return;
        el.classList.remove('hidden'); el.classList.add('flex');
        if (autohide) setTimeout(() => {
            el.classList.add('hidden'); el.classList.remove('flex');
            if (bannerHint) { bannerHint.classList.remove('hidden'); bannerHint.classList.add('flex'); }
        }, autohide);
    }

    let dragged = null;
    function cards() { return [...grid.querySelectorAll('.part-card')]; }
    function clearDrop() { cards().forEach(c => c.classList.remove('drop-before', 'drop-after')); }

    function saveOrder() {
        showBanner(bannerSaving);
        const ids = cards().map(c => c.dataset.id);
        cards().forEach((c, i) => {
            const b = c.querySelector('.order-badge');
            if (b) b.textContent = '#' + (i + 1);
        });
        fetch('{{ route('admin.partenaires.reorder') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ order: ids }),
        })
        .then(r => r.json())
        .then(d => showBanner(d.success ? bannerSaved : bannerHint, d.success ? 2500 : 0))
        .catch(() => showBanner(bannerHint, 0));
    }

    cards().forEach(card => {
        card.addEventListener('dragstart', e => {
            dragged = card;
            e.dataTransfer.effectAllowed = 'move';
            requestAnimationFrame(() => card.classList.add('dragging'));
        });
        card.addEventListener('dragend', () => {
            if (dragged) dragged.classList.remove('dragging');
            clearDrop(); dragged = null; saveOrder();
        });
        card.addEventListener('dragover', e => {
            e.preventDefault();
            if (!dragged || card === dragged) return;
            clearDrop();
            const { left, width } = card.getBoundingClientRect();
            card.classList.add(e.clientX < left + width / 2 ? 'drop-before' : 'drop-after');
        });
        card.addEventListener('dragleave', () => clearDrop());
        card.addEventListener('drop', e => {
            e.preventDefault();
            if (!dragged || card === dragged) return;
            clearDrop();
            const { left, width } = card.getBoundingClientRect();
            grid.insertBefore(dragged, e.clientX < left + width / 2 ? card : card.nextSibling);
        });
    });
})();
</script>
@endsection
