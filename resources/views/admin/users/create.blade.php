@extends('layouts.admin')

@section('content')
    <div class="max-w-md mx-auto bg-white p-5 shadow rounded">
        <h2 class="text-xl font-semibold mb-4">Create User</h2>

        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="block">Name</label>
                <input type="text" name="name" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-3">
                <label class="block">Email</label>
                <input type="email" name="email" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-3">
                <label class="block">Password</label>
                <input type="password" name="password" class="w-full p-2 border rounded" required>
            </div>
            <button class="bg-green-500 text-white px-4 py-2 rounded">Create</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
        </form>
    </div>
@endsection
