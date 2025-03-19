@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-5 shadow rounded">
        <h2 class="text-xl font-semibold mb-4">Users List</h2>
        <a href="{{ route('admin.users.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">+ Add User</a>

        @if (session('success'))
            <div class="bg-green-200 p-2 rounded mt-3">{{ session('success') }}</div>
        @endif

        <table class="w-full mt-4 border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Name</th>
                    <th class="border p-2">Email</th>
                    <th class="border p-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td class="border p-2">{{ $user->name }}</td>
                        <td class="border p-2">{{ $user->email }}</td>
                        <td class="border p-2">
                            <a href="{{ route('admin.users.edit', $user) }}"
                                class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button class="bg-red-500 text-white px-2 py-1 rounded"
                                    onclick="return confirm('Delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{--         <div class="mt-3">
            {{ $users->links() }}
        </div> --}}
    </div>
@endsection
