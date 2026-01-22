@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-6">Roles</h1>
<div class="container mx-auto bg-white p-5 shadow rounded">

    {{-- Message succès --}}
    @if (session('success'))
        <div class="bg-green-200 p-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

     <div class="rounded-lg mt-5 relative overflow-x-auto bg-neutral-primary-soft shadow-xs border border-default">

        <table class="w-full text-sm text-left text-body">
            <thead class="bg-neutral-secondary-medium border-b border-t border-default-medium">
                <tr>
                    <th class="px-6 py-3 font-medium">#</th>
                    <th class="px-6 py-3 font-medium">Role</th>
                    <th class="px-6 py-3 font-medium">Permissions</th>
                    <th class="px-6 py-3 font-medium"># Utilisateurs</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($roles as $index => $role)
                    <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                        <td class="px-6 py-4">
                            {{ $index + 1}}
                        </td>
                        <td class="px-6 py-4">
                                <span
                                    id="badge-dismiss-brand"
                                    class="inline-flex items-center gap-1.5
                                        rounded-full border border-brand-subtle
                                        bg-brand-softer
                                        px-3 py-1
                                        text-sm font-semibold text-fg-brand-strong
                                        shadow-sm
                                        transition hover:bg-brand-soft">
                                {{ $role->name }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @forelse ($role->permissions as $permission)
                                <span
                                    id="badge-dismiss-brand"
                                    class="inline-flex items-center gap-1.5
                                        rounded-full border border-brand-subtle
                                        bg-brand-softer
                                        px-3 py-1 my-1
                                        text-sm font-semibold text-fg-brand-strong
                                        shadow-sm
                                        transition hover:bg-brand-soft">
                                    {{ $permission->name }}
                                </span>
                            @empty
                                <span class="px-3 italic">
                                    Aucune permission.
                                </span>
                                
                            @endforelse
                        </td>
                        <td class="px-6 py-4">
                            {{ $role->users->count() }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex justify-start items-center">
                                <a href=""
                                class="text-blue-500 mr-5">Modifier</a>

                                <form action=""
                                    method="POST"
                                    onsubmit="return confirm('Supprimer ce role ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            Aucun role.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $roles->links() }}
    </div>
</div>
</div>
@endsection
