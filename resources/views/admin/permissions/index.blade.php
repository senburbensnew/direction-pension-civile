@extends('layouts.admin')

@section('title', 'Permissions')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Permissions</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showCreate: false, editId: null, editName: '' }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Permissions</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestion des permissions d'accès aux fonctionnalités.</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus"></i> Nouvelle permission
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
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Permission</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Rôles associés</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Utilisateurs directs</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($permissions as $index => $permission)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ ($permissions->currentPage() - 1) * $permissions->perPage() + $index + 1 }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                {{ $permission->name }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $permission->roles->count() }}</span>
                            <span class="text-xs text-gray-400 ml-0.5">rôle{{ $permission->roles->count() > 1 ? 's' : '' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $permission->users->count() }}</span>
                            <span class="text-xs text-gray-400 ml-0.5">utilisateur{{ $permission->users->count() > 1 ? 's' : '' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    @click="editId = {{ $permission->id }}; editName = '{{ addslashes($permission->name) }}'"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <form action="/admin/permissions/{{ $permission->id }}" method="POST"
                                    onsubmit="return confirm('Supprimer la permission {{ addslashes($permission->name) }} ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-key text-3xl mb-2 block"></i>
                            Aucune permission définie.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($permissions->hasPages())
        <div>{{ $permissions->links() }}</div>
    @endif

    {{-- Modal création --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">
                <i class="fas fa-key mr-2 text-blue-500"></i> Nouvelle permission
            </h3>
            <form action="/admin/permissions" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nom de la permission <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required autofocus
                        placeholder="ex: manage-users, view-reports…"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showCreate = false"
                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                        Annuler
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal édition --}}
    <div x-show="editId !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">
                <i class="fas fa-pencil-alt mr-2 text-blue-500"></i> Modifier la permission
            </h3>
            <template x-if="editId">
                <form :action="`/admin/permissions/${editId}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nom de la permission <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required x-model="editName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editId = null"
                            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                            Annuler
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            Enregistrer
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
