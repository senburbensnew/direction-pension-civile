@extends('layouts.admin')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
@endpush

@section('title', 'Modifier la diapositive')
@section('breadcrumb')
    <a href="{{ route('admin.carousels.index') }}" class="hover:text-gray-800">Carrousel</a>
    <i class="fas fa-chevron-right text-xs text-gray-300"></i>
    <span class="text-gray-700">Modifier</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.carousels.index') }}"
           class="text-gray-400 hover:text-gray-600 transition-colors">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Modifier la diapositive</h1>
            <p class="text-sm text-gray-500">{{ $carousel->title ?: '(Sans titre)' }}</p>
        </div>
    </div>

    <form action="{{ route('admin.carousels.update', $carousel->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">

        @csrf @method('PUT')

        <div class="px-6 py-5 space-y-5">

            {{-- Image upload --}}
            <div x-data="{ preview: null }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Image actuelle</label>
                <div class="relative border-2 border-dashed border-gray-200 rounded-lg overflow-hidden cursor-pointer hover:border-blue-400 transition-colors"
                     style="min-height: 160px;"
                     @click="$refs.fileInput.click()">
                    <img :src="preview ?? '{{ $carousel->imageUrl() }}'"
                         class="w-full h-48 object-cover">
                    <div class="absolute inset-0 bg-black/0 hover:bg-black/20 transition-colors flex items-center justify-center opacity-0 hover:opacity-100">
                        <span class="bg-white/90 text-gray-700 text-xs font-medium px-3 py-1.5 rounded-full shadow">
                            <i class="fas fa-camera mr-1"></i> Changer l'image
                        </span>
                    </div>
                </div>
                <input x-ref="fileInput" type="file" name="image" accept="image/*" class="hidden"
                       @change="const f = $event.target.files[0]; if(f) { const r = new FileReader(); r.onload = e => preview = e.target.result; r.readAsDataURL(f); }">
                <p class="text-xs text-gray-400 mt-1">Laissez vide pour conserver l'image actuelle</p>
                <x-input-error :messages="$errors->get('image')" class="mt-1.5" />
            </div>

            {{-- Title --}}
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Titre</label>
                <input type="text" name="title" id="title" value="{{ old('title', $carousel->title) }}"
                       placeholder="Titre affiché sur la diapositive"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <x-input-error :messages="$errors->get('title')" class="mt-1.5" />
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Texte d'accompagnement (optionnel)"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('description', $carousel->description) }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1.5" />
            </div>

            {{-- Link + CTA --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700 mb-1">
                        Destination du bouton
                        <span class="text-gray-400 font-normal text-xs ml-1">— page ouverte au clic</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-link text-xs"></i>
                        </span>
                        <input type="url" name="link" id="link" value="{{ old('link', $carousel->link) }}"
                               placeholder="https://exemple.com"
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label for="cta_label" class="block text-sm font-medium text-gray-700 mb-1">Libellé du bouton</label>
                    <input type="text" name="cta_label" id="cta_label"
                           value="{{ old('cta_label', $carousel->cta_label) }}"
                           placeholder="ex: En savoir plus"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            {{-- Overlay position --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Position de l'overlay</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach(\App\Models\Carousel::POSITIONS as $value => $label)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="overlay_position" value="{{ $value }}"
                                   class="sr-only peer"
                                   {{ old('overlay_position', $carousel->overlay_position ?? 'bottom-left') === $value ? 'checked' : '' }}>
                            <div class="border-2 rounded-lg p-2 text-center text-xs transition-colors
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                        border-gray-200 text-gray-500 hover:border-gray-300">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Text size --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Taille du texte</label>
                <div class="grid grid-cols-4 gap-2">
                    @foreach(\App\Models\Carousel::TEXT_SIZES as $value => $label)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="text_size" value="{{ $value }}"
                                   class="sr-only peer"
                                   {{ old('text_size', $carousel->text_size ?? 'md') === $value ? 'checked' : '' }}>
                            <div class="border-2 rounded-lg py-2 text-center text-xs transition-colors
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                        border-gray-200 text-gray-500 hover:border-gray-300">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Couleur du texte --}}
            <div x-data="{ textColor: '{{ old('text_color', $carousel->text_color ?? '#ffffff') }}' }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Couleur du texte</label>
                <div class="flex items-center gap-3 flex-wrap">
                    <input type="color" name="text_color" x-model="textColor"
                           :value="textColor"
                           class="w-10 h-10 rounded-lg cursor-pointer border border-gray-200 p-0.5 bg-white">
                    <div class="flex gap-1.5">
                        @foreach(['#ffffff' => 'Blanc', '#fffde7' => 'Jaune clair', '#e3f2fd' => 'Bleu clair', '#ffd700' => 'Or', '#ffccbc' => 'Pêche', '#1a1a2e' => 'Sombre'] as $hex => $name)
                            <button type="button"
                                    @click="textColor = '{{ $hex }}'"
                                    title="{{ $name }}"
                                    :class="textColor === '{{ $hex }}' ? 'ring-2 ring-blue-500 ring-offset-1 scale-110' : 'ring-1 ring-gray-300'"
                                    class="w-6 h-6 rounded-full transition-all"
                                    style="background-color: {{ $hex }}">
                            </button>
                        @endforeach
                    </div>
                    <span x-text="textColor" class="text-xs text-gray-400 font-mono"></span>
                </div>
            </div>

            {{-- Styles du texte --}}
            <div x-data="{ textStyles: {{ json_encode(old('text_styles', $carousel->text_styles ?? [])) }} }">
                <label class="block text-sm font-medium text-gray-700 mb-2">Style du texte</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\Carousel::TEXT_STYLES as $value => $label)
                        <label class="relative cursor-pointer">
                            <input type="checkbox" name="text_styles[]" value="{{ $value }}"
                                   x-model="textStyles"
                                   class="sr-only peer">
                            <div class="border-2 rounded-lg px-3 py-1.5 text-xs transition-colors select-none
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                        border-gray-200 text-gray-500 hover:border-gray-300
                                        {{ $value === 'bold' ? 'font-bold' : '' }}
                                        {{ $value === 'italic' ? 'italic' : '' }}
                                        {{ $value === 'underline' ? 'underline' : '' }}
                                        {{ $value === 'uppercase' ? 'uppercase tracking-wide' : '' }}">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Typographie --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Typographie</label>
                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach(\App\Models\Carousel::FONT_FAMILIES as $key => $name)
                        <label class="relative cursor-pointer">
                            <input type="radio" name="font_family" value="{{ $key }}"
                                   class="sr-only peer"
                                   {{ old('font_family', $carousel->font_family ?? 'sans') === $key ? 'checked' : '' }}>
                            <div class="border-2 rounded-lg py-2 px-3 text-center text-sm transition-colors
                                        peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                        border-gray-200 text-gray-500 hover:border-gray-300"
                                 style="font-family: {{ \App\Models\Carousel::FONT_CSS[$key] }}">
                                {{ $name }}
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Order + Status row --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="order" class="block text-sm font-medium text-gray-700 mb-1">Ordre d'affichage</label>
                    <input type="number" name="order" id="order" value="{{ old('order', $carousel->order) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <x-input-error :messages="$errors->get('order')" class="mt-1.5" />
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" id="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="1" @selected($carousel->status)>Actif</option>
                        <option value="0" @selected(!$carousel->status)>Inactif</option>
                    </select>
                </div>
            </div>

        </div>

        {{-- Footer actions --}}
        <div class="px-6 py-4 bg-gray-50 flex items-center justify-between rounded-b-xl">
            <form action="{{ route('admin.carousels.destroy', $carousel->id) }}" method="POST"
                  onsubmit="return confirm('Supprimer cette diapositive ?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                    <i class="fas fa-trash-alt text-xs"></i> Supprimer
                </button>
            </form>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.carousels.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                    Annuler
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <i class="fas fa-save text-xs"></i>
                    Enregistrer
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
