@extends('layouts.admin')

@section('title', 'Nouvelle actualité')

@section('breadcrumb')
    <a href="{{ route('admin.actualites.admin.index') }}" class="text-gray-500 hover:text-gray-800">Actualités</a>
    <i class="fas fa-chevron-right text-xs text-gray-300 mx-1"></i>
    <span class="text-gray-700">Nouvelle actualité</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Nouvelle actualité</h1>
            <p class="text-sm text-gray-500 mt-0.5">Rédigez et publiez un article sur le site.</p>
        </div>
        <a href="{{ route('admin.actualites.admin.index') }}"
            class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Retour
        </a>
    </div>

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

    <form action="{{ route('admin.actualites.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">

            {{-- Contenu principal --}}
            <div class="px-6 py-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">Contenu de l'article</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Titre <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                        placeholder="Titre de l'actualité"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description courte</label>
                    <textarea name="description" rows="2"
                        placeholder="Résumé affiché dans la liste des actualités…"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenu complet</label>
                    <textarea name="content_text" rows="8"
                        placeholder="Texte intégral de l'article…"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('content_text') }}</textarea>
                </div>
            </div>

            {{-- Métadonnées --}}
            <div class="px-6 py-5 space-y-4">
                <h2 class="text-sm font-semibold text-gray-700">Métadonnées</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                        <input type="text" name="category" value="{{ old('category') }}"
                            placeholder="ex: Annonce, Événement…"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lieu de publication</label>
                        <input type="text" name="posted_in" value="{{ old('posted_in') }}"
                            placeholder="ex: Port-au-Prince"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div class="px-6 py-5 space-y-3">
                <h2 class="text-sm font-semibold text-gray-700">Images</h2>
                <x-file-input name="images[]" label="Images (une ou plusieurs)" accept="image/*" multiple
                    hint="Formats acceptés : JPG, PNG, WEBP — max 2 Mo par fichier" />
            </div>

            {{-- Publication --}}
            <div class="px-6 py-4 bg-gray-50 flex items-center gap-3 rounded-b-xl">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="published" value="1"
                        {{ old('published', true) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Publier immédiatement</span>
                </label>
                <span class="text-xs text-gray-400">· Décochez pour enregistrer en brouillon</span>
            </div>

        </div>

        {{-- Footer actions --}}
        <div class="mt-4 px-6 py-4 bg-gray-50 border border-gray-200 rounded-xl flex items-center justify-end gap-3">
            <a href="{{ route('admin.actualites.admin.index') }}"
                class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                Annuler
            </a>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-save mr-1.5"></i> Enregistrer
            </button>
        </div>
    </form>

</div>
@endsection
