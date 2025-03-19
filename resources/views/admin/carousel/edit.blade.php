@extends('layouts.admin')

@section('content')
    <form action="{{ route('admin.carousels.update', $carousel->id) }}" method="POST" enctype="multipart/form-data"
        class="max-w-4xl mx-auto p-6 bg-white shadow-lg rounded-lg">
        @csrf @method('PUT')

        <div class="mb-6">
            <label for="title" class="block text-lg font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="title"
                class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                value="{{ $carousel->title }}" required>
        </div>

        <div class="mb-6">
            <label for="description" class="block text-lg font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="4"
                class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" required>{{ $carousel->description }}</textarea>
        </div>

        <div class="mb-6">
            <label for="current-image" class="block text-lg font-medium text-gray-700">Current Image</label><br>
            <img src="{{ asset('storage/' . $carousel->image) }}" width="100" alt="Current Image">
        </div>

        <div class="mb-6">
            <label for="image" class="block text-lg font-medium text-gray-700">New Image</label>
            <input type="file" name="image" id="image"
                class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="order" class="block text-lg font-medium text-gray-700">Order</label>
            <input type="number" name="order" id="order"
                class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                value="{{ $carousel->order }}" required>
        </div>

        <div class="mb-6">
            <label for="order" class="block text-lg font-medium text-gray-700">Link</label>
            <input type="text" name="link" id="link"
                class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
        </div>

        <div class="mb-6">
            <label for="status" class="block text-lg font-medium text-gray-700">Status</label>
            <select name="status" id="status"
                class="mt-1 p-3 w-full border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                <option value="1" @if ($carousel->status) selected @endif>Active</option>
                <option value="0" @if (!$carousel->status) selected @endif>Inactive</option>
            </select>
        </div>

        <button type="submit"
            class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
            Update
        </button>
    </form>
@endsection
