@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">
        Ajouter une actualité
    </h1>

    @if ($errors->any())
    <div class="mb-4 rounded border border-red-300 bg-red-50 p-4 text-red-700">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <form action="{{ route('actualites.store') }}"
          method="POST"
          enctype="multipart/form-data"
          class="space-y-4 mb-8">

        @csrf

        <div>
            <label class="block text-sm">Titre</label>
            <input
                name="title"
                value="{{ old('title') }}"
                class="w-full border rounded px-3 py-2"
                required
            >
        </div>

        <div>
            <label class="block text-sm">Description</label>
            <textarea
                name="description"
                class="w-full border rounded px-3 py-2"
            >{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm">Contenu</label>
            <textarea
                name="content_text"
                rows="5"
                class="w-full border rounded px-3 py-2"
            >{{ old('content_text') }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm">Catégorie</label>
                <input
                    name="category"
                    value="{{ old('category') }}"
                    class="w-full border rounded px-3 py-2"
                >
            </div>

            <div>
                <label class="block text-sm">Lieu de publication</label>
                <input
                    name="posted_in"
                    value="{{ old('posted_in') }}"
                    class="w-full border rounded px-3 py-2"
                >
            </div>
        </div>

        <!-- ✅ IMAGES -->
        <div>
            <label class="block text-sm mb-1">
                Images (une ou plusieurs)
            </label>
            <input
                type="file"
                name="images[]"
                multiple
                accept="image/*"
                class="w-full border rounded px-3 py-2"
            >
            <p class="text-xs text-gray-500 mt-1">
                Formats acceptés : JPG, PNG, WEBP
            </p>
        </div>

<div>
    <label class="inline-flex items-center">
        <input
            type="checkbox"
            name="published"
            value="1"
            class="mr-2"
            {{ old('published', true) ? 'checked' : '' }}
        >
        Publier immédiatement
    </label>
</div>


        <div>
            <button class="px-4 py-2 bg-green-600 text-white rounded">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
