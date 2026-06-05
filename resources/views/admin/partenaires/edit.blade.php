@extends('layouts.admin')

@section('title', 'Modifier un partenaire')
@section('breadcrumb')
    <a href="{{ route('admin.partenaires.index') }}" class="text-blue-600 hover:underline">Nos Partenaires</a>
    <span class="mx-1 text-gray-400">/</span> Modifier
@endsection

@section('content')
<div class="max-w-2xl mx-auto"
     x-data="{
        preview: null,
        fileName: null,
        handleFile(e) {
            const f = e.target.files[0];
            if (!f) return;
            this.fileName = f.name;
            const r = new FileReader();
            r.onload = ev => this.preview = ev.target.result;
            r.readAsDataURL(f);
        }
     }">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.partenaires.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Modifier le partenaire</h1>
        </div>
    </div>

    <form action="{{ route('admin.partenaires.update', $partenaire->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
        @csrf @method('PUT')

        @if($errors->any())
            <div class="px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Nom <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $partenaire->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Current logo --}}
        @if($partenaire->logoUrl())
            <div>
                <p class="block text-sm font-medium text-gray-700 mb-2">Logo actuel</p>
                <img src="{{ $partenaire->logoUrl() }}" alt="{{ $partenaire->name }}"
                     class="h-20 object-contain border border-gray-200 rounded-lg p-2 bg-gray-50">
            </div>
        @endif

        {{-- Replace logo --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ $partenaire->logoUrl() ? 'Remplacer le logo (optionnel)' : 'Logo (optionnel)' }}
            </label>
            <div
                @click="$refs.logoInput.click()"
                class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center cursor-pointer hover:border-blue-400 transition-colors"
            >
                <template x-if="!preview">
                    <div>
                        <i class="fas fa-image text-2xl text-gray-300 mb-1"></i>
                        <p class="text-sm text-gray-500">Cliquez pour choisir un logo</p>
                    </div>
                </template>
                <template x-if="preview">
                    <div>
                        <img :src="preview" class="max-h-20 mx-auto object-contain">
                        <p class="text-xs text-gray-500 mt-1" x-text="fileName"></p>
                    </div>
                </template>
                <input x-ref="logoInput" type="file" name="logo" accept="image/*"
                       @change="handleFile($event)" class="hidden">
            </div>
        </div>

        {{-- Website --}}
        <div>
            <label for="website_url" class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
            <input type="url" id="website_url" name="website_url"
                   value="{{ old('website_url', $partenaire->website_url) }}"
                   placeholder="https://..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Order --}}
        <div>
            <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Ordre d'affichage</label>
            <input type="number" id="order" name="order" value="{{ old('order', $partenaire->order) }}" min="0"
                   class="w-24 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Active --}}
        <div class="flex items-center gap-2">
            <input type="hidden" name="active" value="0">
            <input type="checkbox" id="active" name="active" value="1"
                   {{ $partenaire->active ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="active" class="text-sm text-gray-700">Visible sur le site</label>
        </div>

        <div class="flex gap-3 pt-2">
            <a href="{{ route('admin.partenaires.index') }}"
               class="flex-1 text-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                Annuler
            </a>
            <button type="submit"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium">
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
