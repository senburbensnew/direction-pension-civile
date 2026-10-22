@extends('layouts.admin')

@section('title', 'Rôles')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Rôles</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showCreate: false, editRole: null, editName: '', editDescription: '' }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Rôles</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestion des rôles et de leurs permissions.</p>
            <p class="text-xs text-amber-700 mt-1">Renommer un rôle référencé dans le code peut désactiver des parcours métier.</p>
        </div>
        <button type="button" @click="showCreate = true"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus"></i> Nouveau rôle
        </button>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">#</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Rôle</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Description</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Permissions</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Utilisateurs</th>
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $index => $role)
                    <tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-400 text-xs">
                            {{ ($roles->currentPage() - 1) * $roles->perPage() + $index + 1 }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $role->name }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-xs">
                            @if(filled($role->description))
                                <p class="text-xs text-gray-600 line-clamp-2" title="{{ $role->description }}">{{ $role->description }}</p>
                            @else
                                <span class="text-xs text-gray-400 italic">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($role->permissions->take(5) as $permission)
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full"
                                        @if(filled($permission->description)) title="{{ $permission->description }}" @endif>
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-xs text-gray-400 italic">Aucune permission</span>
                                @endforelse
                                @if($role->permissions->count() > 5)
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">
                                        +{{ $role->permissions->count() - 5 }} de plus
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm font-medium text-gray-700">{{ $role->users_count }}</span>
                            <span class="text-xs text-gray-400 ml-0.5">utilisateur{{ $role->users_count > 1 ? 's' : '' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button"
                                    @click="editRole = {{ $role->id }}; editName = @js($role->name); editDescription = @js($role->description ?? '')"
                                    class="px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded text-xs font-medium">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST"
                                    onsubmit="return confirm('Supprimer le rôle {{ addslashes($role->name) }} ?')">
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
                        <td colspan="6" class="px-4 py-10 text-center text-gray-400">
                            <i class="fas fa-shield-alt text-3xl mb-2 block"></i>
                            Aucun rôle défini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($roles->hasPages())
        <div>{{ $roles->links() }}</div>
    @endif

    {{-- Modal création --}}
    <div x-show="showCreate" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">
                <i class="fas fa-shield-alt mr-2 text-blue-500"></i> Créer un rôle
            </h3>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nom du rôle <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required autofocus
                        value="{{ old('name') }}"
                        placeholder="ex: agent_support"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Minuscules, chiffres et underscores (ex. agent_rdv).</p>
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" maxlength="2000"
                        placeholder="Rôle réservé aux agents…"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <x-form.dual-listbox
                    name="permissions[]"
                    label="Permissions"
                    available-title="Permissions disponibles"
                    selected-title="Permissions attribuées au rôle"
                    hint="Sélectionnez une ou plusieurs permissions à gauche, puis utilisez les flèches pour les attribuer."
                    :options="$permissionOptions"
                    :selected="$assignedPermissions"
                    class="mb-4"
                />
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
    <div x-show="editRole !== null" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
        <div class="bg-white w-full max-w-2xl rounded-xl shadow-xl p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <h3 class="font-semibold text-gray-800 text-base mb-4">
                <i class="fas fa-pencil-alt mr-2 text-blue-500"></i> Modifier le rôle
            </h3>
            <template x-if="editRole !== null">
                <form :action="`{{ url('/admin/roles') }}/${editRole}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nom du rôle <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required x-model="editName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3" maxlength="2000" x-model="editDescription"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                    @foreach($roles as $role)
                        <template x-if="editRole === {{ $role->id }}">
                            <x-form.dual-listbox
                                name="permissions[]"
                                label="Permissions"
                                available-title="Permissions disponibles"
                                selected-title="Permissions attribuées au rôle"
                                hint="Sélectionnez une ou plusieurs permissions à gauche, puis utilisez les flèches pour les attribuer."
                                :options="$permissionOptions"
                                :selected="$role->permissions->pluck('name')->values()->all()"
                                class="mb-4"
                            />
                        </template>
                    @endforeach
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="editRole = null"
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
