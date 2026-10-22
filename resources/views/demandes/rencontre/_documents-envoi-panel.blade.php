@php
    $motifService = app(\App\Services\RencontreMotifService::class);
    $canUpload = $demande->canUploadRencontreDocuments();
    $documentGroups = $canUpload ? $motifService->documentGroupsForDemande($demande) : [];
    $uploadFields = $canUpload ? $motifService->uploadableDocumentFields($demande) : ['single' => [], 'multiple' => [], 'supplement_remaining' => 0];
    $hasExisting = collect($documentGroups)->contains(fn ($g) => $g['items']->isNotEmpty());
    $hasUploadSlot = ! empty($uploadFields['single'])
        || ! empty($uploadFields['multiple'])
        || ($uploadFields['supplement_remaining'] ?? 0) > 0;
@endphp

@if($canUpload && ($hasExisting || $hasUploadSlot))
    <div
        class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm"
        role="region"
        aria-labelledby="rdv-docs-envoi-title-{{ $panelId ?? $demande->id }}"
    >
        <div class="flex items-start gap-3 mb-4">
            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-navy">
                <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
            </span>
            <div class="min-w-0 flex-1">
                <h3
                    id="rdv-docs-envoi-title-{{ $panelId ?? $demande->id }}"
                    class="text-base font-bold text-gray-900"
                >
                    Pièces jointes au rendez-vous
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    Vous pouvez compléter ou remplacer vos documents tant que le rendez-vous n’est pas annulé ou refusé.
                </p>
            </div>
        </div>

        @if($hasExisting)
            <div class="mb-5 space-y-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Déjà enregistrés</p>
                @foreach($documentGroups as $group)
                    @if($group['items']->isNotEmpty())
                        <div class="text-sm">
                            <p class="font-medium text-gray-800">{{ $group['label'] }}</p>
                            <ul class="mt-1 space-y-1">
                                @foreach($group['items'] as $media)
                                    <li>
                                        <a href="{{ $demande->urlForMedia($media) }}"
                                           target="_blank"
                                           rel="noopener"
                                           class="text-navy font-medium hover:underline inline-flex items-center gap-1.5">
                                            <i class="fa-solid fa-file-lines text-xs" aria-hidden="true"></i>
                                            {{ $media->file_name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        @if($hasUploadSlot)
            <form
                method="POST"
                action="{{ route('demandes.rencontre.documents', $demande) }}"
                enctype="multipart/form-data"
                class="space-y-4 border-t border-gray-100 pt-4"
            >
                @csrf

                @error('documents')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                @foreach($uploadFields['single'] as $docKey => $docCfg)
                    @php
                        $field = 'doc_' . $docKey;
                        $existing = $demande->getFirstMedia($docKey);
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="{{ $field }}-{{ $panelId ?? $demande->id }}">
                            {{ $docCfg['label'] ?? $docKey }}
                            @if($existing)
                                <span class="text-gray-500 font-normal">(remplace le fichier actuel)</span>
                            @endif
                        </label>
                        <x-file-input
                            :id="$field . '-' . ($panelId ?? $demande->id)"
                            :name="$field"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            :hint="'PDF ou image, ' . (int) ($docCfg['max_kb'] ?? 5120) / 1024 . ' Mo max.'"
                        />
                        @error($field)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                @foreach($uploadFields['multiple'] as $docKey => $docCfg)
                    @php
                        $field = 'doc_' . $docKey;
                        $remaining = max(0, (int) ($docCfg['max_files'] ?? 5) - $demande->getMedia($docKey)->count());
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            {{ $docCfg['label'] ?? $docKey }}
                            <span class="text-gray-500 font-normal">({{ $remaining }} fichier(s) max. supplémentaire(s))</span>
                        </label>
                        <x-file-input
                            :name="$field . '[]'"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            multiple
                            hint="Vous pouvez sélectionner plusieurs fichiers."
                        />
                        @error($field)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error($field . '.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endforeach

                @if(($uploadFields['supplement_remaining'] ?? 0) > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            Documents complémentaires (optionnel)
                            <span class="text-gray-500 font-normal">({{ $uploadFields['supplement_remaining'] }} restant(s))</span>
                        </label>
                        <x-file-input
                            name="doc_supplement[]"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            multiple
                            hint="Sélectionnez un ou plusieurs fichiers supplémentaires si besoin."
                        />
                        @error('doc_supplement')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @error('doc_supplement.*')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div class="flex justify-end pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-sm font-semibold rounded-lg hover:bg-orange-600 transition-colors"
                    >
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        Envoyer les documents
                    </button>
                </div>
            </form>
        @elseif($hasExisting)
            <p class="text-sm text-gray-500 border-t border-gray-100 pt-3">
                Tous les emplacements de téléversement autorisés sont utilisés pour ce rendez-vous.
            </p>
        @endif
    </div>
@endif
