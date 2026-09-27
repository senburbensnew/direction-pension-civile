@extends('layouts.admin')

@section('title', 'Test OCR')
@section('breadcrumb')
    <span class="text-gray-800">Test OCR</span>
@endsection

@section('content')
    <div class="max-w-3xl">
        <h1 class="text-xl font-semibold text-gray-800 mb-1">Test OCR (Document AI)</h1>
        <p class="text-sm text-gray-500 mb-6">
            Envoyez une image ou un PDF pour vérifier la lecture. Rien n’est enregistré en base.
            Moteur actuel : <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $driver }}</code>
        </p>

        <form method="POST" action="{{ route('admin.ocr.test.store') }}" enctype="multipart/form-data"
              class="bg-white rounded-xl border border-gray-200 p-5 space-y-4 mb-6">
            @csrf
            <div>
                <label for="document" class="block text-sm font-medium text-gray-700 mb-1">Document</label>
                <input type="file" id="document" name="document" accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf" required
                       class="w-full text-sm text-gray-700 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#173052] file:text-white">
                <p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP ou PDF — 5 Mo max.</p>
                <x-input-error :messages="$errors->get('document')" class="mt-1" />
            </div>
            <button type="submit"
                    class="px-4 py-2.5 bg-[#173052] hover:bg-orange-600 text-white text-sm font-semibold rounded-lg">
                Analyser
            </button>
        </form>

        @if(!empty($error))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-6">
                {{ $error }}
            </div>
        @endif

        @if($text)
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Texte lu</h2>
                <pre class="whitespace-pre-wrap text-sm text-gray-800 bg-gray-50 rounded-lg p-4 max-h-80 overflow-y-auto">{{ $text }}</pre>
            </div>
        @endif

        @if(!empty($fields))
            <div class="bg-white rounded-xl border border-gray-200 p-5 mb-6" x-data="{ copied: false }">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <h2 class="text-sm font-semibold text-gray-800">JSON (clé / valeur)</h2>
                    <button type="button"
                            class="text-xs font-medium text-[#173052] hover:text-orange-600"
                            @click="navigator.clipboard.writeText($refs.json.textContent); copied = true; setTimeout(() => copied = false, 1500)">
                        <span x-show="!copied">Copier</span>
                        <span x-show="copied" x-cloak>Copié</span>
                    </button>
                </div>
                <pre x-ref="json" class="whitespace-pre-wrap text-sm text-gray-800 bg-gray-50 rounded-lg p-4 max-h-80 overflow-y-auto">{{ $fieldsJson }}</pre>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-sm font-semibold text-gray-800 mb-3">Champs extraits</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @foreach($fields as $key => $value)
                        <div>
                            <dt class="text-gray-500">{{ $key }}</dt>
                            <dd class="font-medium text-gray-900">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @elseif($text)
            <p class="text-sm text-gray-500">Aucun champ structuré n’a été reconnu (NIF, nom, téléphone, etc.).</p>
        @endif
    </div>
@endsection
