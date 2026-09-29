@php
    $identite = $identite ?? [];
@endphp

<section x-show="modalite" x-cloak>
<details class="dpc-accordion bg-white border border-gray-200 card-shadow" @if($identiteHasError) open @endif>
    <summary>
        <span class="flex items-center gap-3">
            <span class="text-2xl text-navy flex items-center justify-center shrink-0">
                <i class="fa-solid fa-id-card" aria-hidden="true"></i>
            </span>
            <span>
                <span class="block text-xl font-bold text-navy">Confirmer vos informations</span>
                <span class="block text-gray-600 font-normal mt-1 text-base">Renseignez ou vérifiez les informations qui figureront sur le rendez-vous.</span>
            </span>
        </span>
    </summary>
    <div class="dpc-accordion-body">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <label for="prenom" class="block text-base font-medium text-gray-700">Prénom <span class="text-red-500">*</span></label>
            <input type="text" id="prenom" name="prenom" x-model="prenom" value="{{ $identite['prenom'] ?? '' }}"
                   class="{{ $fieldClass('prenom') }}" autocomplete="given-name">
            @error('prenom')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="nom" class="block text-base font-medium text-gray-700">Nom <span class="text-red-500">*</span></label>
            <input type="text" id="nom" name="nom" x-model="nom" value="{{ $identite['nom'] ?? '' }}"
                   class="{{ $fieldClass('nom') }}" autocomplete="family-name">
            @error('nom')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="telephone" class="block text-base font-medium text-gray-700">Téléphone <span class="text-red-500">*</span></label>
            <input type="tel" id="telephone" name="telephone" x-model="telephone"
                   value="{{ $identite['telephone'] ?? '' }}" placeholder="+509XXXXXXXX"
                   class="{{ $fieldClass('telephone') }}" autocomplete="tel">
            @error('telephone')<p class="mt-1 text-base text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="mt-5 text-sm text-gray-500">
        Motif déjà indiqué :
        <span class="font-medium text-navy" x-text="motifLabel()"></span>
    </p>
    </div>
</details>
</section>