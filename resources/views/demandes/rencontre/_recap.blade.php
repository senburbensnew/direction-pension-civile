<section x-show="modalite" x-cloak>
<details class="dpc-accordion bg-white border border-gray-200 card-shadow" @if($recapHasError) open @endif>
    <summary>
        <span class="flex items-center gap-3">
            <span class="text-2xl text-navy flex items-center justify-center shrink-0">
                <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
            </span>
            <span>
                <span class="block text-xl font-bold text-navy">Confirmation</span>
                <span class="block text-gray-600 font-normal mt-1 text-base">Vérifiez le récapitulatif avant d’envoyer la demande.</span>
            </span>
        </span>
    </summary>
    <div class="dpc-accordion-body">

    <dl class="divide-y divide-gray-100 border border-gray-100 rounded-xl overflow-hidden">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3 bg-gray-50">
            <dt class="text-sm font-medium text-gray-500">Type de rendez-vous</dt>
            <dd class="sm:col-span-2 text-base text-navy font-semibold" x-text="modalite === 'physique' ? 'Présentiel' : 'Visioconférence'"></dd>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3">
            <dt class="text-sm font-medium text-gray-500">Date et heure</dt>
            <dd class="sm:col-span-2 text-base text-gray-800" x-text="recapDateHeure()"></dd>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3 bg-gray-50">
            <dt class="text-sm font-medium text-gray-500" x-text="modalite === 'physique' ? 'Lieu' : 'Lien de visioconférence'"></dt>
            <dd class="sm:col-span-2 text-base text-gray-800" x-text="recapLieuOuLien()"></dd>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3">
            <dt class="text-sm font-medium text-gray-500">Motif</dt>
            <dd class="sm:col-span-2 text-base text-gray-800" x-text="motifLabel()"></dd>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3 bg-white">
            <dt class="text-sm font-medium text-gray-500">Service responsable</dt>
            <dd class="sm:col-span-2 text-base text-gray-800" x-text="serviceLabel()"></dd>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-1 px-4 py-3">
            <dt class="text-sm font-medium text-gray-500">Documents à préparer</dt>
            <dd class="sm:col-span-2">
                <ul class="list-disc list-inside text-base text-gray-800 space-y-1">
                    <template x-for="doc in documentsPourModalite()" :key="doc">
                        <li x-text="doc"></li>
                    </template>
                </ul>
            </dd>
        </div>
    </dl>

    <div class="mt-6 rounded-xl border border-navy/15 bg-slate-50 p-4">
        <label class="flex items-start gap-3 cursor-pointer select-none">
            <input type="checkbox" name="confirmation_lu_accepte" value="1" x-model="accepte"
                   {{ old('confirmation_lu_accepte') ? 'checked' : '' }}
                   class="mt-1 w-4 h-4 accent-[#173052]">
            <span class="text-base text-gray-800">
                J’ai lu le récapitulatif et j’accepte les informations de cette demande de rendez-vous.
            </span>
        </label>
        @error('confirmation_lu_accepte')<p class="mt-2 text-base text-red-600">{{ $message }}</p>@enderror
    </div>
    </div>
</details>
</section>