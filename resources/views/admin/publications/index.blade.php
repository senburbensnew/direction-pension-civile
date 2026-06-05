@extends('layouts.admin')

@section('title', 'Textes & Publications')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Textes & Publications</span>
@endsection

@section('content')
<div class="space-y-4" x-data="pubAdmin()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Textes & Publications légales</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérez les lois, décrets, circulaires et documents officiels.</p>
        </div>
        <div class="flex items-center gap-2">
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
            <button @click="openCreate()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg whitespace-nowrap">
                <i class="fas fa-plus"></i> Ajouter
            </button>
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
                            <span class="text-xs px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-medium">{{ $pub->typeLabel() }}</span>
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
                                    <i class="fas fa-pencil-alt"></i> Éditer
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

    {{-- Modal --}}
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
</div>
@endsection

@push('scripts')
<script>
function pubAdmin() {
    return {
        open: false,
        editing: false,
        form: { id: null, title: '', type: 'document', description: '', url: '', file_path: null, published: true },
        openCreate() {
            this.editing = false;
            this.form = { id: null, title: '', type: 'document', description: '', url: '', file_path: null, published: true };
            this.open = true;
        },
        openEdit(item) {
            this.editing = true;
            this.form = { ...item, published: !!item.published };
            this.open = true;
        }
    };
}
</script>
@endpush
