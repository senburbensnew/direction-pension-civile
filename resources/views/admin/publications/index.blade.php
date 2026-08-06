@extends('layouts.admin')

@section('title', 'Textes & Publications')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Textes & Publications</span>
@endsection

@section('content')
@php
    $badgePresets = [
        'bg-red-100 text-red-700' => 'Rouge',
        'bg-orange-100 text-orange-700' => 'Orange',
        'bg-amber-100 text-amber-700' => 'Ambre',
        'bg-green-100 text-green-700' => 'Vert',
        'bg-blue-100 text-blue-700' => 'Bleu',
        'bg-indigo-100 text-indigo-700' => 'Indigo',
        'bg-purple-100 text-purple-700' => 'Violet',
        'bg-pink-100 text-pink-700' => 'Rose',
        'bg-gray-100 text-gray-700' => 'Gris',
    ];
    $iconPresets = [
        'fa-gavel', 'fa-scroll', 'fa-envelope-open', 'fa-file-alt', 'fa-file-contract',
        'fa-paperclip', 'fa-file', 'fa-book', 'fa-balance-scale', 'fa-stamp',
        'fa-landmark', 'fa-clipboard-list', 'fa-folder-open',
    ];
@endphp

<div class="space-y-4" x-data="pubAdmin()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Textes & Publications légales</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérez les lois, décrets, circulaires et documents officiels.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap justify-end">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher…"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-40 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tous les types</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('q') || request('type'))
                    <a href="{{ route('admin.publications.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></a>
                @endif
            </form>
            <button type="button" @click="typesOpen = !typesOpen"
                class="inline-flex items-center gap-1.5 px-4 py-2 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg whitespace-nowrap">
                <i class="fas fa-tags"></i> Types
            </button>
            <button type="button" @click="openCreate()"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg whitespace-nowrap">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>
    </div>

    {{-- Types de documents --}}
    <div x-show="typesOpen" x-cloak
        class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-800">Types de documents</h2>
                <p class="text-xs text-gray-500">Ajoutez, modifiez ou supprimez les catégories utilisées pour les publications.</p>
            </div>
            <button type="button" @click="openTypeCreate()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg">
                <i class="fas fa-plus"></i> Nouveau type
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Ordre</th>
                        <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Aperçu</th>
                        <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Libellé</th>
                        <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Code</th>
                        <th class="px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($publicationTypes as $ptype)
                        <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                            <td class="px-4 py-2.5 text-gray-500">{{ $ptype->order_column }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded font-medium {{ $ptype->badge_class }}">
                                    <i class="fas {{ $ptype->icon }}"></i> {{ $ptype->label }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 font-medium text-gray-800">{{ $ptype->label }}</td>
                            <td class="px-4 py-2.5 text-gray-500 font-mono text-xs">{{ $ptype->code }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" @click="openTypeEdit({{ $ptype->toJson() }})"
                                        class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium"
                                        title="Modifier">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <form action="{{ route('admin.publication-types.destroy', $ptype) }}" method="POST"
                                        onsubmit="return confirm('Supprimer ce type ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium"
                                            title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-400">Aucun type défini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Titre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Type</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Fichier</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Date</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Statut</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($publications as $pub)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ Str::limit($pub->title, 60) }}</td>
                        <td class="px-4 py-3">
                            @php
                                $ptype = $publicationTypes->firstWhere('code', $pub->type);
                            @endphp
                            <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded font-medium {{ $ptype->badge_class ?? 'bg-gray-100 text-gray-700' }}">
                                @if($ptype?->icon)
                                    <i class="fas {{ $ptype->icon }}"></i>
                                @endif
                                {{ $pub->typeLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($pub->file_path)
                                <a href="{{ $pub->fileUrl() }}" target="_blank" class="text-blue-600 hover:underline text-xs">
                                    <i class="fas fa-file-pdf"></i> Voir
                                </a>
                            @elseif($pub->url)
                                <a href="{{ $pub->url }}" target="_blank" class="text-blue-600 hover:underline text-xs">
                                    <i class="fas fa-external-link-alt"></i> Lien
                                </a>
                            @else
                                <span class="text-gray-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-xs whitespace-nowrap">{{ $pub->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $pub->published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $pub->published ? 'Publié' : 'Masqué' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.publications.toggle', $pub) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 rounded text-xs font-medium {{ $pub->published ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        <i class="fas {{ $pub->published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <button @click="openEdit({{ $pub->toJson() }})"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <form action="{{ route('admin.publications.destroy', $pub) }}" method="POST"
                                    onsubmit="return confirm('Supprimer cette publication ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-file-alt text-3xl mb-2 block"></i>
                            Aucune publication trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($publications->hasPages())
        <div>{{ $publications->withQueryString()->links() }}</div>
    @endif

    {{-- Modal publication --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4 z-10 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-gray-800" x-text="editing ? 'Modifier la publication' : 'Ajouter une publication'"></h2>

            <form :action="editing ? '/admin/publications/' + form.id : '{{ route('admin.publications.store') }}'"
                method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Titre <span class="text-red-500">*</span></label>
                    <input type="text" name="title" x-model="form.title" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select name="type" x-model="form.type" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" x-model="form.description" rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Fichier PDF / Word</label>
                    <input type="file" name="file" accept=".pdf,.doc,.docx"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <template x-if="editing && form.file_path">
                        <p class="text-xs text-gray-500 mt-1"><i class="fas fa-paperclip"></i> Fichier actuel — un nouveau fichier le remplacera.</p>
                    </template>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">URL externe (alternative au fichier)</label>
                    <input type="url" name="url" x-model="form.url" placeholder="https://..."
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="published" value="0">
                    <input type="checkbox" id="pub-published" name="published" value="1" x-model="form.published" class="rounded border-gray-300 text-blue-600">
                    <label for="pub-published" class="text-sm text-gray-700">Visible sur le site</label>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="open = false"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Annuler</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal type --}}
    <div x-show="typeOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="typeOpen = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md p-6 space-y-4 z-10 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-bold text-gray-800" x-text="typeEditing ? 'Modifier le type' : 'Nouveau type de document'"></h2>

            <form :action="typeEditing ? '/admin/publication-types/' + typeForm.id : '{{ route('admin.publication-types.store') }}'"
                method="POST" class="space-y-3">
                @csrf
                <template x-if="typeEditing"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Libellé <span class="text-red-500">*</span></label>
                    <input type="text" name="label" x-model="typeForm.label" required
                        placeholder="Ex. Arrêté, Ordonnance…"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <template x-if="!typeEditing">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Code <span class="text-gray-400">(optionnel)</span></label>
                        <input type="text" name="code" x-model="typeForm.code"
                            placeholder="Généré automatiquement depuis le libellé"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 mt-1">Lettres, chiffres, tirets et underscores uniquement.</p>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Icône</label>
                    <div class="grid grid-cols-7 gap-1.5 mb-2">
                        @foreach($iconPresets as $icon)
                            <button type="button" @click="typeForm.icon = '{{ $icon }}'"
                                :class="typeForm.icon === '{{ $icon }}' ? 'ring-2 ring-blue-500 bg-blue-50' : 'bg-gray-50 hover:bg-gray-100'"
                                class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-700 text-sm">
                                <i class="fas {{ $icon }}"></i>
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="icon" x-model="typeForm.icon">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Couleur du badge</label>
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        @foreach($badgePresets as $class => $name)
                            <button type="button" @click="typeForm.badge_class = '{{ $class }}'"
                                :class="typeForm.badge_class === '{{ $class }}' ? 'ring-2 ring-blue-500' : ''"
                                class="text-xs px-2 py-1 rounded font-medium {{ $class }}" title="{{ $name }}">
                                {{ $name }}
                            </button>
                        @endforeach
                    </div>
                    <input type="hidden" name="badge_class" x-model="typeForm.badge_class">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Ordre d'affichage</label>
                    <input type="number" name="order_column" x-model="typeForm.order_column" min="0"
                        class="w-28 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2">
                    <p class="text-xs text-gray-500 mb-1">Aperçu</p>
                    <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded font-medium"
                        :class="typeForm.badge_class || 'bg-gray-100 text-gray-700'">
                        <i class="fas" :class="typeForm.icon || 'fa-file'"></i>
                        <span x-text="typeForm.label || 'Nouveau type'"></span>
                    </span>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" @click="typeOpen = false"
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">Annuler</button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function pubAdmin() {
    const defaultType = @json(array_key_first($types) ?: 'document');
    return {
        open: false,
        editing: false,
        typesOpen: {{ session('error') || old('label') ? 'true' : 'false' }},
        typeOpen: false,
        typeEditing: false,
        form: { id: null, title: '', type: defaultType, description: '', url: '', file_path: null, published: true },
        typeForm: { id: null, label: '', code: '', icon: 'fa-file', badge_class: 'bg-gray-100 text-gray-700', order_column: 0 },
        openCreate() {
            this.editing = false;
            this.form = { id: null, title: '', type: defaultType, description: '', url: '', file_path: null, published: true };
            this.open = true;
        },
        openEdit(item) {
            this.editing = true;
            this.form = { ...item, published: !!item.published };
            this.open = true;
        },
        openTypeCreate() {
            this.typeEditing = false;
            this.typeForm = { id: null, label: '', code: '', icon: 'fa-file', badge_class: 'bg-gray-100 text-gray-700', order_column: {{ (int) ($publicationTypes->max('order_column') ?? 0) + 1 }} };
            this.typeOpen = true;
        },
        openTypeEdit(item) {
            this.typeEditing = true;
            this.typeForm = { ...item };
            this.typeOpen = true;
        }
    };
}
</script>
@endpush
