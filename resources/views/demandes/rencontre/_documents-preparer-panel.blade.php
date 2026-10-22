@php
    $documentsPreparer = array_values(array_filter($documents ?? []));
    $modeLabel = $modeLabel ?? null;
@endphp

@if($documentsPreparer !== [])
    <div
        class="rounded-xl border-2 border-indigo-200 bg-indigo-50/90 p-4 sm:p-5 shadow-sm"
        role="region"
        aria-labelledby="rdv-docs-preparer-title-{{ $panelId ?? 'default' }}"
    >
        <div class="flex items-start gap-3">
            <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-white border border-indigo-200 flex items-center justify-center text-indigo-700">
                <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
            </span>
            <div class="min-w-0 flex-1">
                <h3
                    id="rdv-docs-preparer-title-{{ $panelId ?? 'default' }}"
                    class="text-base font-bold text-gray-900"
                >
                    Documents à préparer pour ce rendez-vous
                </h3>
                <p class="text-sm text-gray-700 mt-1">
                    @if($modeLabel)
                        Préparez ces pièces avant la date du rendez-vous ({{ $modeLabel }}).
                    @else
                        Préparez ces pièces avant la date du rendez-vous.
                    @endif
                    @if($showUploadNote ?? true)
                        Les fichiers déjà téléversés lors de la demande restent enregistrés dans votre dossier.
                        @if($showEnvoiHint ?? false)
                            Vous pouvez en ajouter ou en remplacer via le formulaire de pièces jointes ci-dessous.
                        @endif
                    @endif
                </p>
                <ul class="mt-3 space-y-2">
                    @foreach($documentsPreparer as $document)
                        <li class="flex items-start gap-2 text-sm sm:text-base text-gray-900">
                            <i class="fa-solid fa-circle-check text-indigo-600 mt-1 shrink-0 text-xs" aria-hidden="true"></i>
                            <span>{{ $document }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
