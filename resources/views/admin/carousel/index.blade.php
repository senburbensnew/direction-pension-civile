@extends('layouts.admin')

@section('content')
    <div class="container mx-auto p-6 bg-white shadow-md rounded-lg">
        <div class="flex justify-between items-center mb-4">
            <a href="{{ route('admin.carousels.create') }}"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Add Slide</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border p-3 text-left">Image</th>
                        <th class="border p-3 text-left">Title</th>
                        <th class="border p-3 text-left">Order</th>
                        <th class="border p-3 text-left">Link</th>
                        <th class="border p-3 text-left">Status</th>
                        <th class="border p-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($carousels as $carousel)
                        <tr class="border">
                            <td class="border p-3">
                                <img src="{{ asset('storage/' . $carousel->image) }}" class="w-24 h-auto rounded-lg">
                            </td>
                            <td class="border p-3">{{ $carousel->title }}</td>
                            <td class="border p-3">{{ $carousel->order }}</td>
                            <td class="border p-3">{{ $carousel->link }}</td>
                            <td class="border p-3">
                                <span
                                    class="px-2 py-1 text-xs font-semibold {{ $carousel->status ? 'bg-green-200 text-green-800' : 'bg-red-200 text-red-800' }} rounded-lg">
                                    {{ $carousel->status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="border p-3 flex gap-2">
                                <a href="{{ route('admin.carousels.edit', $carousel->id) }}"
                                    class="bg-yellow-500 text-white px-3 py-1 rounded-lg hover:bg-yellow-600">Edit</a>
                                <form action="{{ route('admin.carousels.destroy', $carousel->id) }}" method="POST"
                                    onsubmit="return confirm('Delete this slide?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
