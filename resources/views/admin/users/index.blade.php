@extends('layouts.admin')

@section('content')
    <div class="container mx-auto bg-white p-5 shadow rounded">
        <a href="{{ route('admin.users.create') }}" class="text-blue-500 hover:text-blue-800 transition-colors">+
            Ajouter</a>

        @if (session('success'))
            <div class="bg-green-200 p-2 rounded mt-3">{{ session('success') }}</div>
        @endif

        <div class="overflow-x-auto mt-4">
            <table class="w-full border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-2">Photo</th>
                        <th class="border p-2">Nom</th>
                        <th class="border p-2">Email</th>
                        <th class="border p-2">NIF</th>
                        <th class="border p-2">Type</th>
                        <th class="border p-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="border p-2 text-center align-middle">
                                <div class="w-10 h-10 inline-flex items-center justify-center">
                                    @if ($user->profile_photo)
                                        <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="Profile photo"
                                            class="w-10 h-10 rounded-full object-cover">
                                    @else
                                        <svg class="h-full w-full text-gray-300" viewBox="0 0 32 32" fill="currentColor"
                                            aria-hidden="true">
                                            <path
                                                d="M16 16c4.418 0 8-3.582 8-8s-3.582-8-8-8-8 3.582-8 8 3.582 8 8 8zm0 4c-5.523 0-16 2.477-16 8v4h32v-4c0-5.523-10.477-8-16-8z" />
                                        </svg>
                                    @endif
                                </div>
                            </td>
                            <td class="border p-2">{{ $user->name }}</td>
                            <td class="border p-2">{{ $user->email }}</td>
                            <td class="border p-2">{{ $user->nif }}</td>
                            <td class="border p-2">{{ $user->userType->name ?? 'N/A' }}</td>
                            <td class="border p-2">
                                <div class="flex space-x-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="bg-yellow-500 text-white px-2 py-1 rounded hover:bg-yellow-600 transition-colors text-sm">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition-colors text-sm"
                                            onclick="return confirm('Are you sure you want to delete this user?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border p-4 text-center text-gray-500">
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
@endsection
