@extends('layouts.admin')

@section('content')
<div class="container mx-auto bg-white p-5 shadow rounded">

    {{-- Message succès --}}
    @if (session('success'))
        <div class="bg-green-200 p-2 rounded mb-3">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg mt-5 relative overflow-x-auto bg-neutral-primary-soft shadow-xs border border-default">

        {{-- Barre top --}}
        <div class="flex items-center justify-between flex-wrap p-4">

            {{-- Dropdown Action --}}
            <div class="relative inline-block">
                <button type="button"
                    id="actionDropdownButton"
                    class="rounded-lg inline-flex items-center justify-center text-body bg-neutral-secondary-medium border border-default-medium hover:bg-neutral-tertiary-medium focus:ring-2 focus:ring-neutral-tertiary px-3 py-2 text-sm">
                    Actions
                    <svg class="w-4 h-4 ms-1.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" d="m19 9-7 7-7-7" />
                    </svg>
                </button>

                {{-- Menu --}}
                <div id="actionDropdown"
                    class="absolute mt-2 hidden z-50 w-36 bg-white border border-default-medium rounded-base shadow-lg">
                    <ul class="p-2 text-sm text-body font-medium">
                        <li>
                            <a href="{{ route('admin.users.create') }}"
                               class="block px-3 py-2 rounded hover:bg-neutral-tertiary-medium hover:text-heading">
                                + Ajouter
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Recherche --}}
            <div class="relative mt-4 md:mt-0">
                <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                    <svg class="w-4 h-4 text-body" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2"
                            d="m21 21-3.5-3.5M17 10a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>
                </div>
                <input type="text"
                    class="rounded-lg block w-full max-w-96 ps-9 pe-3 py-2 bg-neutral-secondary-medium border border-default-medium text-heading text-sm focus:ring-brand focus:border-brand shadow-xs placeholder:text-body"
                    placeholder="Rechercher">
            </div>
        </div>

        {{-- Table --}}
        <table class="w-full text-sm text-left text-body">
            <thead class="bg-neutral-secondary-medium border-b border-t border-default-medium">
                <tr>
                    <th class="px-6 py-3 font-medium">Nom</th>
                    <th class="px-6 py-3 font-medium">NIF</th>
                    <th class="px-6 py-3 font-medium">Type</th>
                    <th class="px-6 py-3 font-medium">Sexe</th>
                    <th class="px-6 py-3 font-medium">Telephone</th>
                    <th class="px-6 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr class="bg-neutral-primary-soft border-b border-default hover:bg-neutral-secondary-medium">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10">
                                    @if ($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}"
                                             class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <svg class="w-10 h-10 text-gray-300" viewBox="0 0 32 32"
                                            fill="currentColor">
                                            <path
                                                d="M16 16c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 4c-5.523 0-16 2.477-16 8v4h32v-4c0-5.523-10.477-8-16-8z" />
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-semibold">{{ $user->name }}</div>
                                    <div class="text-body text-sm">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">{{ $user->nif }}</td>
                        <td class="px-6 py-4">{{ ucfirst($user->userType->name ?? '--') }}</td>
                        <td class="px-6 py-4">{{ ucfirst($user->gender->name ?? '--') }}</div>
                        <td class="px-6 py-4">{{ ucfirst($user->phone ?? '--') }}</div>
                        <td class="px-6 py-4">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="bg-yellow-500 text-white px-2 py-1 rounded text-sm hover:bg-yellow-600">
                                    Edit
                                </a>

                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        onclick="return confirm('Supprimer cet utilisateur ?')"
                                        class="bg-red-500 text-white px-2 py-1 rounded text-sm hover:bg-red-600">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-4 text-center text-gray-500">
                            Aucun utilisateur.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if ($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
</div>

{{-- JS dropdown custom --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('actionDropdownButton');
    const dropdown = document.getElementById('actionDropdown');

    button.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('hidden');
    });

    document.addEventListener('click', () => {
        dropdown.classList.add('hidden');
    });
});
</script>
@endsection
