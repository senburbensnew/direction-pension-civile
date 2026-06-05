@extends('layouts.admin')

@section('title', 'Directions Départementales')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Directions Départementales</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showCreate: false, editDirection: null }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Directions Départementales</h1>
            <p class="text-sm text-gray-500 mt-0.5">Représentations régionales affichées sur la page Contact.</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus"></i> Nouvelle direction
        </button>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Ordre</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Sigle</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Nom</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Ville</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Couleur</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($directions as $dir)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">{{ $dir->order }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-mono px-2 py-0.5 bg-slate-100 text-slate-600 rounded">{{ $dir->abbr }}</span>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $dir->nom }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $dir->ville }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $dir->color }}-100 text-{{ $dir->color }}-700">
                                {{ $dir->color }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button"
                                @click="editDirection = {{ $dir->toJson() }}"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium mr-3">
                                <i class="fas fa-pen"></i> Modifier
                            </button>
                            <form method="POST" action="{{ route('admin.directions.destroy', $dir) }}" class="inline"
                                onsubmit="return confirm('Supprimer cette direction ?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-map-marker-alt text-3xl mb-2 block"></i>
                            Aucune direction départementale définie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Modal création --}}
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">Nouvelle direction départementale</h3>
            <form method="POST" action="{{ route('admin.directions.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sigle *</label>
                        <input type="text" name="abbr" required placeholder="DDO" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Ville *</label>
                        <input type="text" name="ville" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nom complet *</label>
                    <input type="text" name="nom" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Couleur *</label>
                        <select name="color" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach(['blue','green','purple','red','yellow','indigo','orange','cyan','pink','teal'] as $c)
                                <option value="{{ $c }}">{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Ordre</label>
                        <input type="number" name="order" value="0" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal édition --}}
    <div x-show="editDirection" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-lg rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">Modifier la direction</h3>
            <template x-if="editDirection">
                <form method="POST" :action="`/admin/directions/${editDirection.id}`" class="space-y-4">
                    @csrf @method('PUT')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Sigle *</label>
                            <input type="text" name="abbr" :value="editDirection.abbr" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Ville *</label>
                            <input type="text" name="ville" :value="editDirection.ville" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Nom complet *</label>
                        <input type="text" name="nom" :value="editDirection.nom" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Couleur *</label>
                            <select name="color" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @foreach(['blue','green','purple','red','yellow','indigo','orange','cyan','pink','teal'] as $c)
                                    <option value="{{ $c }}" :selected="editDirection.color === '{{ $c }}'">{{ ucfirst($c) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Ordre</label>
                            <input type="number" name="order" :value="editDirection.order" min="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="editDirection = null" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Annuler</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">Mettre à jour</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
