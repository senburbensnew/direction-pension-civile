@extends('layouts.admin')

@section('title', 'Nouvelle diapositive')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Oswald:wght@400;600&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="max-w-6xl mx-auto"
     x-data="{
        preview: null,
        fileName: null,
        fileSize: null,
        title: '',
        description: '',
        link: '',
        ctaLabel: '',
        overlayPosition: 'bottom-left',
        textSize: 'md',
        textColor: '#ffffff',
        textStyles: [],
        fontFamily: 'sans',
        status: '1',

        get fontFamilyCss() {
            const map = {
                'sans':      'system-ui, -apple-system, sans-serif',
                'serif':     "Georgia, 'Times New Roman', serif",
                'playfair':  "'Playfair Display', Georgia, serif",
                'oswald':    "'Oswald', 'Arial Narrow', sans-serif",
                'condensed': "'Arial Narrow', Impact, sans-serif",
            };
            return map[this.fontFamily] || 'system-ui, sans-serif';
        },

        handleFile(e) {
            const f = e.target.files[0];
            if (!f) return;
            this.fileName = f.name;
            this.fileSize = (f.size / 1024 / 1024).toFixed(2) + ' Mo';
            const r = new FileReader();
            r.onload = ev => this.preview = ev.target.result;
            r.readAsDataURL(f);
        },

        handleDrop(e) {
            e.preventDefault();
            const f = e.dataTransfer.files[0];
            if (!f || !f.type.startsWith('image/')) return;
            this.$refs.fileInput.files = e.dataTransfer.files;
            this.handleFile({ target: { files: e.dataTransfer.files } });
        }
     }">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.carousels.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-500 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Nouvelle diapositive</h1>
            <p class="text-sm text-gray-500">Ajoutez une image au carrousel de la page d'accueil</p>
        </div>
    </div>

    <form action="{{ route('admin.carousels.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- ── Colonne gauche : formulaire ─────────────────────────── --}}
            <div class="lg:col-span-3 space-y-5">

                {{-- Upload zone --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-image text-blue-400"></i> Image
                        <span class="text-red-500">*</span>
                    </h2>

                    <div class="relative border-2 border-dashed rounded-xl transition-colors cursor-pointer"
                         :class="preview ? 'border-blue-300 bg-blue-50' : 'border-gray-200 hover:border-blue-400 bg-gray-50'"
                         style="min-height: 200px;"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent
                         @drop="handleDrop($event)">

                        <template x-if="!preview">
                            <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 text-gray-400 p-4">
                                <div class="w-14 h-14 rounded-full bg-gray-100 flex items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-2xl text-gray-300"></i>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm font-medium text-gray-500">Glissez une image ici</p>
                                    <p class="text-xs text-gray-400 mt-0.5">ou cliquez pour parcourir</p>
                                    <p class="text-xs text-gray-300 mt-2">JPEG, PNG, GIF · max 4 Mo</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="preview">
                            <div class="relative">
                                <img :src="preview" class="w-full rounded-xl object-cover" style="max-height: 220px;">
                                <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded-lg flex items-center gap-1.5">
                                    <i class="fas fa-check-circle text-green-400"></i>
                                    <span x-text="fileName"></span>
                                    <span class="text-gray-300">·</span>
                                    <span x-text="fileSize"></span>
                                </div>
                                <button type="button"
                                        @click.stop="preview = null; fileName = null; fileSize = null; $refs.fileInput.value = ''"
                                        class="absolute top-2 right-2 w-7 h-7 bg-black/60 hover:bg-black/80 text-white rounded-full flex items-center justify-center transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <input x-ref="fileInput" type="file" name="image" accept="image/*" class="hidden" required
                           @change="handleFile($event)">
                    @error('image')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Texte & lien --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <h2 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-font text-blue-400"></i> Contenu
                    </h2>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Titre <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        <input type="text" name="title" x-model="title"
                               value="{{ old('title') }}"
                               placeholder="ex: Direction de la Pension Civile"
                               maxlength="80"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-right text-xs text-gray-300 mt-1" x-text="title.length + '/80'"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Description <span class="text-gray-400 font-normal">(optionnel)</span>
                        </label>
                        <textarea name="description" x-model="description" rows="2"
                                  maxlength="160"
                                  placeholder="Sous-titre ou texte d'accompagnement"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('description') }}</textarea>
                        <p class="text-right text-xs text-gray-300 mt-1" x-text="description.length + '/160'"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">
                                Destination du bouton
                                <span class="text-gray-400 font-normal ml-1">— page ouverte au clic</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-300 pointer-events-none text-xs">
                                    <i class="fas fa-link"></i>
                                </span>
                                <input type="url" name="link" x-model="link"
                                       value="{{ old('link') }}"
                                       placeholder="https://exemple.com"
                                       class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Libellé du bouton</label>
                            <input type="text" name="cta_label" x-model="ctaLabel"
                                   value="{{ old('cta_label') }}"
                                   placeholder="ex: En savoir plus"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Position de l'overlay</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(\App\Models\Carousel::POSITIONS as $value => $label)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="overlay_position" value="{{ $value }}"
                                           x-model="overlayPosition"
                                           class="sr-only peer"
                                           {{ old('overlay_position', 'bottom-left') === $value ? 'checked' : '' }}>
                                    <div class="border-2 rounded-lg p-2 text-center text-xs transition-colors
                                                peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:text-blue-700
                                                border-gray-200 text-gray-500 hover:border-gray-300">
                                        {{ $label }}
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Taille du texte</label>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach(\App\Models\Carousel::TEXT_SIZES as $value => $label)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="text_size" value="{{ $value }}"
                                           x-model="textSize"
                                           class="sr-only peer"
                                           {{ old('text_size', 'md') === $value ? 'checked' : '' }}>
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
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Couleur du texte</label>
                        <div class="flex items-center gap-3 flex-wrap">
                            <input type="color" name="text_color" x-model="textColor"
                                   value="{{ old('text_color', '#ffffff') }}"
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
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Style du texte</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(\App\Models\Carousel::TEXT_STYLES as $value => $label)
                                <label class="relative cursor-pointer">
                                    <input type="checkbox" name="text_styles[]" value="{{ $value }}"
                                           x-model="textStyles"
                                           class="sr-only peer"
                                           {{ in_array($value, old('text_styles', [])) ? 'checked' : '' }}>
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
                        <label class="block text-xs font-medium text-gray-600 mb-2">Typographie</label>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            @foreach(\App\Models\Carousel::FONT_FAMILIES as $key => $name)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="font_family" value="{{ $key }}"
                                           x-model="fontFamily"
                                           class="sr-only peer"
                                           {{ old('font_family', 'sans') === $key ? 'checked' : '' }}>
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
                </div>

                {{-- Ordre & Statut --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                    <h2 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-sliders-h text-blue-400"></i> Paramètres
                    </h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ordre d'affichage</label>
                            <input type="number" name="order" value="{{ old('order', 0) }}" min="0"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                            <select name="status" x-model="status"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="1">Actif</option>
                                <option value="0">Inactif</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('admin.carousels.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        Annuler
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-save text-xs"></i>
                        Enregistrer la diapositive
                    </button>
                </div>

            </div>

            {{-- ── Colonne droite : preview live ──────────────────────── --}}
            <div class="lg:col-span-2">
                <div class="sticky top-6">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
                        <i class="fas fa-eye mr-1"></i> Aperçu
                    </p>

                    {{-- Frame du carousel --}}
                    <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm bg-gray-900"
                         style="aspect-ratio: 16/9; position: relative;">

                        {{-- Image de fond --}}
                        <template x-if="preview">
                            <img :src="preview" class="absolute inset-0 w-full h-full object-cover">
                        </template>
                        <template x-if="!preview">
                            <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900">
                                <div class="text-center text-slate-600">
                                    <i class="fas fa-image text-4xl mb-2"></i>
                                    <p class="text-xs">Aperçu de l'image</p>
                                </div>
                            </div>
                        </template>

                        {{-- Overlay gradient --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

                        {{-- Overlay gradient adapté à la position --}}
                        <div class="absolute inset-0 pointer-events-none"
                             :class="{
                                 'bg-gradient-to-t from-black/70 via-black/20 to-transparent': overlayPosition.startsWith('bottom'),
                                 'bg-gradient-to-b from-black/70 via-black/20 to-transparent': overlayPosition.startsWith('top'),
                                 'bg-black/40': overlayPosition === 'center',
                             }"></div>

                        {{-- Contenu overlay positionné --}}
                        <div class="absolute inset-0 p-4 flex"
                             :class="{
                                 'items-end justify-start':  overlayPosition === 'bottom-left',
                                 'items-end justify-center': overlayPosition === 'bottom-center',
                                 'items-end justify-end':    overlayPosition === 'bottom-right',
                                 'items-center justify-center': overlayPosition === 'center',
                                 'items-start justify-start':  overlayPosition === 'top-left',
                                 'items-start justify-center':  overlayPosition === 'top-center',
                             }">
                            <template x-if="title || description || ctaLabel">
                                <div :class="(overlayPosition === 'bottom-center' || overlayPosition === 'top-center' || overlayPosition === 'center') ? 'text-center' : 'text-left'">
                                    <p x-show="title" x-text="title"
                                       class="leading-tight drop-shadow-lg mb-1"
                                       :style="`color: ${textColor}; font-family: ${fontFamilyCss};`"
                                       :class="{
                                           'text-xs':   textSize === 'sm',
                                           'text-sm':   textSize === 'md',
                                           'text-base': textSize === 'lg',
                                           'text-xl':   textSize === 'xl',
                                           'font-bold':     textStyles.includes('bold'),
                                           'italic':        textStyles.includes('italic'),
                                           'underline':     textStyles.includes('underline'),
                                           'uppercase':     textStyles.includes('uppercase'),
                                           'tracking-wide': textStyles.includes('uppercase'),
                                       }"></p>
                                    <p x-show="description" x-text="description"
                                       class="leading-snug drop-shadow mb-2"
                                       :style="`color: ${textColor}; font-family: ${fontFamilyCss}; opacity: 0.85;`"
                                       :class="{
                                           'text-[10px]': textSize === 'sm',
                                           'text-xs':     textSize === 'md',
                                           'text-sm':     textSize === 'lg',
                                           'text-base':   textSize === 'xl',
                                           'italic':    textStyles.includes('italic'),
                                           'uppercase': textStyles.includes('uppercase'),
                                           'tracking-wide': textStyles.includes('uppercase'),
                                       }"></p>
                                    <template x-if="ctaLabel">
                                        <span class="inline-block bg-white text-gray-900 font-semibold px-3 py-1 rounded-full shadow"
                                              :class="{
                                                  'text-[10px]': textSize === 'sm',
                                                  'text-xs':     textSize === 'md',
                                                  'text-sm':     textSize === 'lg' || textSize === 'xl',
                                              }">
                                            <span x-text="ctaLabel"></span>
                                            <i class="fas fa-arrow-right ml-1 text-[9px]"></i>
                                        </span>
                                    </template>
                                </div>
                            </template>
                        </div>

                        {{-- Badge statut --}}
                        <div class="absolute top-3 right-3">
                            <template x-if="status === '1'">
                                <span class="inline-flex items-center gap-1 text-xs bg-green-500/90 text-white px-2 py-0.5 rounded-full">
                                    <i class="fas fa-circle text-[6px]"></i> Actif
                                </span>
                            </template>
                            <template x-if="status === '0'">
                                <span class="inline-flex items-center gap-1 text-xs bg-gray-500/90 text-white px-2 py-0.5 rounded-full">
                                    <i class="fas fa-circle text-[6px]"></i> Inactif
                                </span>
                            </template>
                        </div>

                        {{-- Dots indicateurs --}}
                        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 mb-1.5 flex gap-1">
                            <div class="w-4 h-1.5 bg-white rounded-full"></div>
                            <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
                            <div class="w-1.5 h-1.5 bg-white/40 rounded-full"></div>
                        </div>
                    </div>

                    {{-- Note --}}
                    <p class="text-xs text-gray-400 mt-3 text-center leading-relaxed">
                        L'aperçu reflète le rendu sur la page d'accueil.<br>
                        Le titre et la description s'affichent en superposition.
                    </p>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
