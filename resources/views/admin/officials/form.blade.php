@extends('layouts.admin')

@section('title', isset($official->id) ? 'Modifier l\'officiel' : 'Ajouter un officiel')

@section('breadcrumb')
    <a href="{{ route('admin.officials.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">Présentations</a>
    <span class="text-gray-400 mx-1">/</span>
    <span class="text-gray-700 text-sm">{{ isset($official->id) ? 'Modifier' : 'Ajouter' }}</span>
@endsection

@push('styles')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<style>
    .ql-container { font-family: inherit; font-size: 0.875rem; border-bottom-left-radius: 0.5rem; border-bottom-right-radius: 0.5rem; }
    .ql-toolbar { border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem; background: #f9fafb; border-color: #d1d5db !important; }
    .ql-container { border-color: #d1d5db !important; }
    .ql-editor { min-height: 220px; line-height: 1.75; color: #374151; }
    .ql-editor.ql-blank::before { color: #9ca3af; font-style: normal; }
    .ql-toolbar button:hover, .ql-toolbar button.ql-active { color: #2563eb !important; }
    .ql-toolbar .ql-stroke { stroke: #6b7280; }
    .ql-toolbar button:hover .ql-stroke, .ql-toolbar button.ql-active .ql-stroke { stroke: #2563eb !important; }
    .ql-toolbar .ql-fill { fill: #6b7280; }
    .ql-toolbar button:hover .ql-fill, .ql-toolbar button.ql-active .ql-fill { fill: #2563eb !important; }
    .ql-snow .ql-picker { color: #6b7280; font-size: 0.8125rem; }
    /* discours editor taller */
    #editor-discours .ql-editor { min-height: 280px; }
</style>
@endpush

@push('scripts')
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const toolbarOptions = [
        [{ 'header': [2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
        ['blockquote', 'link'],
        ['clean'],
    ];

    function initEditor(editorId, inputId, initialContent) {
        const quill = new Quill('#' + editorId, {
            theme: 'snow',
            placeholder: editorId === 'editor-biographie'
                ? 'Rédigez la biographie ici…'
                : 'Rédigez le discours ici…',
            modules: { toolbar: toolbarOptions },
        });

        // Load initial content
        if (initialContent && initialContent.trim()) {
            const isHtml = /<[a-z][\s\S]*>/i.test(initialContent);
            if (isHtml) {
                quill.clipboard.dangerouslyPasteHTML(initialContent);
            } else {
                // Plain text from old seeder — convert paragraphs
                const html = initialContent.trim().split(/\n\n+/)
                    .map(p => '<p>' + p.replace(/\n/g, '<br>') + '</p>')
                    .join('');
                quill.clipboard.dangerouslyPasteHTML(html);
            }
        }

        // Sync to hidden textarea on any change
        quill.on('text-change', function () {
            document.getElementById(inputId).value = quill.root.innerHTML === '<p><br></p>'
                ? ''
                : quill.root.innerHTML;
        });

        // Also sync on form submit (safety)
        document.querySelector('form').addEventListener('submit', function () {
            document.getElementById(inputId).value = quill.root.innerHTML === '<p><br></p>'
                ? ''
                : quill.root.innerHTML;
        });

        return quill;
    }

    initEditor(
        'editor-biographie',
        'input-biographie',
        @json(old('biographie', $official->biographie ?? ''))
    );

    initEditor(
        'editor-discours',
        'input-discours',
        @json(old('discours', $official->discours ?? ''))
    );
});
</script>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <div class="flex items-center gap-3">
        <a href="{{ route('admin.officials.index') }}"
           class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center transition-colors">
            <i class="fas fa-arrow-left text-gray-600 text-xs"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                {{ isset($official->id) ? 'Modifier : '.$official->nom : 'Ajouter un officiel' }}
            </h1>
            <p class="text-sm text-gray-500">
                {{ isset($official->id) ? 'Slug : '.$official->slug : 'Nouveau profil officiel' }}
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li class="flex items-start gap-2">
                        <i class="fas fa-exclamation-circle mt-0.5 shrink-0"></i> {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ isset($official->id) ? route('admin.officials.update', $official) : route('admin.officials.store') }}"
          enctype="multipart/form-data"
          class="space-y-6">
        @csrf
        @if(isset($official->id)) @method('PUT') @endif

        {{-- ── Identité ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
            <div class="px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-id-card mr-1.5 text-blue-500"></i> Identité
                </h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                @unless(isset($official->id))
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Slug <span class="text-red-500">*</span>
                            <span class="ml-1 text-xs font-normal text-gray-400">lettres minuscules et tirets</span>
                        </label>
                        <input type="text" name="slug" value="{{ old('slug') }}" required
                               placeholder="ex: directeur-adjoint"
                               pattern="[a-z0-9\-]+"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                @endunless

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Intitulé du poste <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="role" value="{{ old('role', $official->role ?? '') }}" required
                           placeholder="ex: Directrice Générale"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nom complet <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nom" value="{{ old('nom', $official->nom ?? '') }}" required
                           placeholder="ex: Marie Jean PIERRE"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Genre</label>
                    <select name="sexe" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="M" @selected(old('sexe', $official->sexe ?? 'M') === 'M')>Masculin</option>
                        <option value="F" @selected(old('sexe', $official->sexe ?? 'M') === 'F')>Féminin</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordre d'affichage</label>
                    <input type="number" name="order" value="{{ old('order', $official->order ?? 0) }}" min="0"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="active" value="1"
                               @checked(old('active', $official->active ?? true))
                               class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm font-medium text-gray-700">Profil actif (visible sur le site)</span>
                    </label>
                </div>

            </div>
        </div>

        {{-- ── Photo ────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
            <div class="px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-camera mr-1.5 text-blue-500"></i> Photo
                </h2>
            </div>
            <div class="px-6 py-5" x-data="{ preview: null }">
                @if(isset($official->id) && $official->photo)
                    <div class="mb-4 flex items-center gap-4">
                        <img src="{{ $official->photoUrl() }}" alt="Photo actuelle"
                             class="w-20 h-20 rounded-xl object-cover border border-gray-200">
                        <p class="text-xs text-gray-500">Photo actuelle — téléversez une nouvelle image pour la remplacer.</p>
                    </div>
                @endif
                <input type="file" name="photo" accept="image/*"
                       @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                       class="block w-full text-sm text-gray-500 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <template x-if="preview">
                    <img :src="preview" class="mt-3 w-28 h-28 rounded-xl object-cover border border-gray-200">
                </template>
                <p class="text-xs text-gray-400 mt-2">JPG, PNG ou WebP · max 4 Mo</p>
            </div>
        </div>

        {{-- ── Citation ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
            <div class="px-6 py-4">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-quote-left mr-1.5 text-blue-500"></i> Citation
                    <span class="ml-1 text-xs font-normal text-gray-400">— courte phrase affichée en italique sous le titre du discours</span>
                </h2>
            </div>
            <div class="px-6 py-5">
                <input type="text" name="citation"
                       value="{{ old('citation', $official->citation ?? '') }}"
                       placeholder='ex : "Servir avec dévouement, c'est assurer l'avenir de ceux qui ont bâti notre nation."'
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- ── Biographie ───────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
            <div class="px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-user-alt mr-1.5 text-blue-500"></i> Biographie
                    <span class="ml-1 text-xs font-normal text-gray-400">— page Profil</span>
                </h2>
                <span class="text-xs text-gray-400">Gras, titres, listes, liens…</span>
            </div>
            <div class="px-6 py-5">
                {{-- Hidden input that receives Quill HTML --}}
                <textarea id="input-biographie" name="biographie" class="hidden">{{ old('biographie', $official->biographie ?? '') }}</textarea>
                {{-- Quill mount point --}}
                <div id="editor-biographie" class="rounded-lg"></div>
            </div>
        </div>

        {{-- ── Discours ─────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100">
            <div class="px-6 py-4 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fas fa-microphone mr-1.5 text-blue-500"></i> Discours / Mot officiel
                    <span class="ml-1 text-xs font-normal text-gray-400">— page Mots</span>
                </h2>
                <span class="text-xs text-gray-400">Gras, titres, listes, liens…</span>
            </div>
            <div class="px-6 py-5">
                <textarea id="input-discours" name="discours" class="hidden">{{ old('discours', $official->discours ?? '') }}</textarea>
                <div id="editor-discours" class="rounded-lg"></div>
            </div>
        </div>

        {{-- ── Submit ───────────────────────────────── --}}
        <div class="flex items-center gap-3 justify-end pb-6">
            <a href="{{ route('admin.officials.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
                Annuler
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fas fa-save mr-1.5"></i>
                {{ isset($official->id) ? 'Enregistrer les modifications' : 'Créer le profil' }}
            </button>
        </div>

    </form>
</div>
@endsection
