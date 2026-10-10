{{--
    Récapitulatif des documents à préparer pour le rendez-vous (selon motif + modalité).
    S’appuie sur config/rencontre.php → motifs.*.prepare
--}}
<div
    x-show="motif && modalite"
    x-cloak
    class="mb-5 rounded-xl border border-indigo-100 bg-indigo-50/70 p-4"
>
    <div class="flex items-start gap-3">
        <span class="flex-shrink-0 w-9 h-9 rounded-lg bg-white border border-indigo-100 flex items-center justify-center text-indigo-700">
            <i class="fa-solid fa-folder-open text-sm" aria-hidden="true"></i>
        </span>
        <div class="min-w-0 flex-1">
            <h4 class="text-sm font-bold text-gray-900">
                Documents à préparer pour ce rendez-vous
            </h4>
            <p class="text-xs text-gray-600 mt-1">
                Préparez ces pièces avant la date du rendez-vous
                (<span x-text="modalite === 'physique' ? 'présentiel' : 'visioconférence'"></span>).
                Les fichiers demandés plus bas peuvent être téléversés dès maintenant.
            </p>
            <ul class="mt-3 space-y-1.5 text-sm text-gray-800 list-disc list-inside">
                <template x-for="(doc, idx) in documentsPourModalite()" :key="'prep-' + idx">
                    <li x-text="doc"></li>
                </template>
                <template x-if="!documentsPourModalite().length">
                    <li class="text-gray-500 list-none -ml-0">
                        Sélectionnez un motif pour afficher la liste des documents.
                    </li>
                </template>
            </ul>
        </div>
    </div>
</div>
