@php
    $motifDocs = $motifDocuments ?? [];
@endphp

<div
    class="space-y-5"
    x-show="motif"
    x-cloak
>
    <div class="rounded-xl border border-navy/10 bg-slate-50/80 p-4">
        <p class="text-sm font-semibold text-navy flex items-center gap-2">
            <i class="fa-solid fa-paperclip"></i>
            Pièces à joindre selon le motif
        </p>
        <p class="text-xs text-gray-600 mt-1">
            Les documents requis changent selon le motif choisi. Vous pouvez ajouter des pièces complémentaires en bas de liste.
        </p>
    </div>

    @foreach ($motifs ?? [] as $motifKey => $motifLabel)
        @php
            $docs = $motifDocs[$motifKey] ?? ['single' => [], 'multiple' => []];
        @endphp

        <div x-show="motif === @js($motifKey)" x-cloak class="space-y-4">
            @foreach ($docs['single'] ?? [] as $docKey => $docCfg)
                @php
                    $field = 'doc_' . $docKey;
                    $required = $docCfg['required'] ?? false;
                @endphp
                <div>
                    <label class="block text-base font-medium text-gray-700" for="{{ $field }}">
                        {{ $docCfg['label'] ?? $docKey }}
                        @if($required)
                            <span class="text-red-500">*</span>
                        @endif
                    </label>
                    <x-file-input
                        :name="$field"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        :hint="'PDF ou image, ' . (int) ($docCfg['max_kb'] ?? 5120) / 1024 . ' Mo max.'"
                    />
                    @error($field)
                        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            @foreach ($docs['multiple'] ?? [] as $docKey => $docCfg)
                @php
                    $field = 'doc_' . $docKey;
                @endphp
                <div>
                    <label class="block text-base font-medium text-gray-700">
                        {{ $docCfg['label'] ?? $docKey }}
                    </label>
                    <x-file-input
                        :name="$field . '[]'"
                        accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                        multiple
                        hint="Vous pouvez sélectionner plusieurs fichiers."
                    />
                    @error($field)
                        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
                    @enderror
                    @error($field . '.*')
                        <p class="mt-1 text-base text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
        </div>
    @endforeach

    <div x-show="motif" x-cloak>
        <label class="block text-base font-medium text-gray-700">
            Documents complémentaires (optionnel)
        </label>
        <div class="mt-2">
            <x-file-input
                name="doc_supplement[]"
                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                multiple
                hint="Sélectionnez un ou plusieurs fichiers supplémentaires si besoin."
            />
        </div>
        @error('doc_supplement')
            <p class="mt-1 text-base text-red-600">{{ $message }}</p>
        @enderror
        @error('doc_supplement.*')
            <p class="mt-1 text-base text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
