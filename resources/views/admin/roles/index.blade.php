@extends('layouts.admin')

@section('title', 'Rôles')

@section('breadcrumb')
    <span class="text-gray-700 text-sm">Rôles</span>
@endsection

@section('content')
<div class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Rôles</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestion des rôles et de leurs permissions.</p>
        </div>
        <button type="button" onclick="document.getElementById('modal-create-role').classList.remove('hidden')"
            class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fas fa-plus"></i> Nouveau rôle
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
                    <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-left">Rôle</th>
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
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full
                                {{ $role->name === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $role->name }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($role->permissions->take(5) as $permission)
                                    <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full">
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
                            <span class="text-sm font-medium text-gray-700">{{ $role->users->count() }}</span>
                            <span class="text-xs text-gray-400 ml-0.5">utilisateur{{ $role->users->count() > 1 ? 's' : '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs text-gray-400 italic">
                                Géré via code
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-400">
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

</div>

{{-- Modal création rôle --}}
<div id="modal-create-role" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white w-full max-w-md rounded-xl shadow-xl p-6">
        <h3 class="font-semibold text-gray-800 text-base mb-4">
            <i class="fas fa-shield-alt mr-2 text-blue-500"></i> Créer un rôle
        </h3>
        <p class="text-sm text-gray-500 mb-4">
            La création de rôles nécessite une implémentation complète du contrôleur.
            Les rôles sont gérés via Spatie Permission.
        </p>
        <div class="flex justify-end gap-2">
            <button type="button" onclick="document.getElementById('modal-create-role').classList.add('hidden')"
                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                Fermer
            </button>
        </div>
    </div>
</div>

@endsection
