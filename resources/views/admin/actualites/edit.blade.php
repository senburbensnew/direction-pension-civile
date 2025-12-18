@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">
        Éditer l’actualité
    </h1>

    {{-- Display all validation errors --}}
    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Update Actualité Form --}}
    <form id="update-actualite-form"
        action="{{ route('actualites.update', ['actualite' => $actualiteEdit->id]) }}"
        method="POST" enctype="multipart/form-data" class="space-y-4 mb-8">

        @csrf
        @method('PUT')

        {{-- Title --}}
        <div>
            <label class="block text-sm">Titre</label>
            <input name="title" value="{{ old('title', $actualiteEdit->title) }}"
                class="w-full border rounded px-3 py-2" required>
        </div>

        {{-- Description --}}
        <div>
            <label class="block text-sm">Description</label>
            <textarea name="description"
                class="w-full border rounded px-3 py-2">{{ old('description', $actualiteEdit->description) }}</textarea>
        </div>

        {{-- Content --}}
        <div>
            <label class="block text-sm">Contenu</label>
            <textarea name="content_text" rows="5"
                class="w-full border rounded px-3 py-2">{{ old('content_text', $actualiteEdit->content_text) }}</textarea>
        </div>

        {{-- Category & Posted In --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm">Catégorie</label>
                <input name="category" value="{{ old('category', $actualiteEdit->category) }}"
                    class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm">Lieu de publication</label>
                <input name="posted_in"
                    value="{{ old('posted_in', $actualiteEdit->posted_in) }}"
                    class="w-full border rounded px-3 py-2">
            </div>
        </div>

        {{-- File Upload --}}
        <div class="mb-4">
            <label class="block text-sm mb-1">
                Images (une ou plusieurs)
            </label>
            <input type="file" name="images[]" multiple accept="image/*" class="w-full border rounded px-3 py-2">
            <p class="text-xs text-gray-500 mt-1">
                Formats acceptés : JPG, PNG, WEBP
            </p>
        </div>

        {{-- Existing Images --}}
        <div>
            <label class="block text-sm my-5">Selectionner les images a supprimer</label>
            <div class="flex flex-wrap gap-4 mb-4">
                @foreach($actualiteEdit->images as $image)
                    <div class="relative group w-48 sm:w-40 h-40 flex-shrink-0">
                        <img src="{{ Storage::url($image->image_path) }}" alt="Actualité Image"
                            class="rounded w-full h-full object-cover" />

                        {{-- Checkbox to delete --}}
                        <label class="absolute top-1 right-1 z-10 flex items-center justify-center">
                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                class="w-4 h-4 accent-red-600">
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Published Checkbox --}}
        <div>
            <label class="inline-flex items-center">
                <input type="checkbox" name="published" value="1" class="mr-2"
                    {{ old('published', $actualiteEdit->published) ? 'checked' : '' }}>
                Publiée
            </label>
        </div>

        {{-- Submit Button --}}
        <div class="mt-5">
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Mettre à jour
            </button>
        </div>
    </form>
</div>
@endsection
