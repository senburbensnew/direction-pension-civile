@extends('layouts.admin')

@section('title', 'FAQ')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">FAQ</span>
@endsection

@section('content')
<div class="space-y-4" x-data="faqAdmin()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">FAQ</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérez les questions fréquemment posées.</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="GET" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Rechercher…"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">
                    <i class="fas fa-search"></i>
                </button>
                @if(request('q'))
                    <a href="{{ route('admin.faq.index') }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></a>
                @endif
            </form>
            <button @click="openCreate()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-plus"></i> Ajouter
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Question</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Catégorie</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Ordre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Statut</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800 max-w-xs">
                            {{ Str::limit($item->question, 80) }}
                        </td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $item->category }}</td>
                        <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $item->order_column }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $item->published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $item->published ? 'Publié' : 'Masqué' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <form action="{{ route('admin.faq.toggle', $item) }}" method="POST">
                                    @csrf
                                    <button type="submit" title="{{ $item->published ? 'Masquer' : 'Publier' }}"
                                        class="px-2 py-1 rounded text-xs font-medium {{ $item->published ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        <i class="fas {{ $item->published ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                <button @click="openEdit({{ $item->toJson() }})"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i> Éditer
                                </button>
                                <form action="{{ route('admin.faq.destroy', $item) }}" method="POST"
                                    onsubmit="return confirm('Supprimer cette question ?')">
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
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-question-circle text-3xl mb-2 block"></i>
                            Aucune question trouvée.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
        <div>{{ $items->withQueryString()->links() }}</div>
    @endif

    {{-- Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50" @click="open = false"></div>
        <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg p-6 space-y-4 z-10">
            <h2 class="text-lg font-bold text-gray-800" x-text="editing ? 'Modifier la question' : 'Ajouter une question'"></h2>

            <form :action="editing ? '/admin/faq/' + form.id : '{{ route('admin.faq.store') }}'" method="POST" class="space-y-3">
                @csrf
                <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Question <span class="text-red-500">*</span></label>
                    <input type="text" name="question" x-model="form.question" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Réponse <span class="text-red-500">*</span></label>
                    <textarea name="answer" x-model="form.answer" required rows="4"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Catégorie</label>
                        <input type="text" name="category" x-model="form.category" list="faq-categories"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <datalist id="faq-categories">
                            <option>Général</option>
                            <option>Éligibilité</option>
                            <option>Documents requis</option>
                            <option>Calcul de la pension</option>
                            <option>Paiement</option>
                            <option>Procédures</option>
                        </datalist>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Ordre</label>
                        <input type="number" name="order_column" x-model="form.order_column" min="0"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="published" value="0">
                    <input type="checkbox" id="faq-published" name="published" value="1" x-model="form.published"
                        class="rounded border-gray-300 text-blue-600">
                    <label for="faq-published" class="text-sm text-gray-700">Visible sur le site</label>
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
function faqAdmin() {
    return {
        open: false,
        editing: false,
        form: { id: null, question: '', answer: '', category: 'Général', order_column: 0, published: true },
        openCreate() {
            this.editing = false;
            this.form = { id: null, question: '', answer: '', category: 'Général', order_column: 0, published: true };
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
