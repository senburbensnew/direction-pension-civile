@extends('layouts.admin')

@section('title', 'Éditer : ' . Str::limit($actualiteEdit->title, 40))

@section('breadcrumb')
    <a href="{{ route('admin.actualites.admin.index') }}" class="text-gray-500 hover:text-gray-800">Actualités</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-700">Éditer</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Éditer l'actualité</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ Str::limit($actualiteEdit->title, 60) }}</p>
        </div>
        <a href="{{ route('admin.actualites.admin.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-300 text-green-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="flex items-start gap-2 bg-red-50 border border-red-300 text-red-800 rounded-lg px-4 py-3 text-sm">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.actualites.update', $actualiteEdit->id) }}"
          method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">

            {{-- Contenu principal --}}
            <div class="px-6 py-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">Contenu de l'article</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Titre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $actualiteEdit->title) }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description courte <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" rows="2"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $actualiteEdit->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contenu complet <span class="text-red-500">*</span>
                    </label>
                    <textarea name="content_text" rows="8"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('content_text', $actualiteEdit->content_text) }}</textarea>
                    @error('content_text')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="px-6 py-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">Métadonnées</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <input type="text" name="category" value="{{ old('category', $actualiteEdit->category) }}"
                            placeholder="ex: Annonce, Événement…"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu de publication</label>
                        <input type="text" name="posted_in" value="{{ old('posted_in', $actualiteEdit->posted_in) }}"
                            placeholder="ex: Port-au-Prince"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Images existantes --}}
            @if($actualiteEdit->images->isNotEmpty())
            <div class="px-6 py-5 space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">Images actuelles</h2>
                <p class="text-xs text-gray-400">Cochez les images à supprimer lors de l'enregistrement.</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($actualiteEdit->images as $image)
                        <div class="relative group w-36 h-28 flex-shrink-0 rounded-lg overflow-hidden border border-gray-200">
                            <img src="{{ Storage::url($image->image_path) }}" alt="Image"
                                class="w-full h-full object-cover">
                            <label class="absolute inset-0 flex items-center justify-center cursor-pointer
                                bg-transparent group-hover:bg-black/10 transition-colors">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                    class="sr-only peer">
                                <span class="absolute top-1.5 right-1.5 w-5 h-5 rounded border-2 border-white bg-white/80
                                    peer-checked:bg-red-500 peer-checked:border-red-500 transition-colors flex items-center justify-center">
                                    <i class="fas fa-times text-white text-xs peer-checked:block hidden"></i>
                                </span>
                            </label>
                            {{-- Visual deletion overlay --}}
                            <div class="absolute inset-0 bg-red-500/20 opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity"></div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-red-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Les images cochées seront définitivement supprimées à l'enregistrement.
                </p>
            </div>
            @endif

            {{-- Nouvelles images --}}
            <div class="px-6 py-5 space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">Ajouter des images</h2>
                <x-file-input name="images[]" label="Nouvelles images" accept="image/*" multiple
                    hint="Formats acceptés : JPG, PNG, WEBP — max 2 Mo par fichier" />
            </div>

            {{-- Publication --}}
            <div class="px-6 py-4 bg-gray-50 flex items-center gap-3 rounded-b-xl">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="published" value="1"
                        {{ old('published', $actualiteEdit->published) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Publié</span>
                </label>
                <span class="text-xs text-gray-400">
                    · Statut actuel :
                    <span class="font-medium {{ $actualiteEdit->published ? 'text-green-600' : 'text-gray-500' }}">
                        {{ $actualiteEdit->published ? 'Publié' : 'Brouillon' }}
                    </span>
                </span>
            </div>

        </div>

        {{-- Footer actions --}}
        <div class="mt-4 px-6 py-4 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-2">
                {{-- Quick toggle --}}
                <form action="{{ route('admin.actualites.toggle', $actualiteEdit->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="px-3 py-2 text-xs font-medium rounded-lg transition-colors
                            {{ $actualiteEdit->published
                                ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                        <i class="fas {{ $actualiteEdit->published ? 'fa-eye-slash' : 'fa-eye' }} mr-1"></i>
                        {{ $actualiteEdit->published ? 'Dépublier' : 'Publier' }}
                    </button>
                </form>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.actualites.admin.index') }}"
                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                    Annuler
                </a>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-save mr-1.5"></i> Enregistrer les modifications
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
