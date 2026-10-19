@php
    $identityFirstname = old('firstname', $identityUser?->firstname ?? '');
    $identityLastname = old('lastname', $identityUser?->lastname ?? '');
    $identityName = old('name', $identityUser?->name ?? '');
@endphp

<div
    class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4"
    x-data="{
        firstname: @js($identityFirstname),
        lastname: @js($identityLastname),
        name: @js($identityName),
        nameManual: false,
        composeName() {
            return [this.firstname, this.lastname]
                .map((part) => (part || '').trim())
                .filter(Boolean)
                .join(' ');
        },
        syncName() {
            if (! this.nameManual) {
                this.name = this.composeName();
            }
        },
        onNameInput() {
            const auto = this.composeName();
            const current = (this.name || '').trim();
            this.nameManual = current !== '' && current !== auto;
        },
        initIdentityName() {
            const auto = this.composeName();
            const current = (this.name || '').trim();
            if (current === '') {
                this.name = auto;
                this.nameManual = false;
            } else {
                this.nameManual = auto !== '' && current !== auto;
            }
        },
    }"
    x-init="initIdentityName()"
>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
            Prénom <span class="text-red-500">*</span>
        </label>
        <input type="text" name="firstname" x-model="firstname" @input="syncName()" required
            class="{{ $field('firstname') }}" placeholder="Jean">
        @error('firstname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
            Nom de famille <span class="text-red-500">*</span>
        </label>
        <input type="text" name="lastname" x-model="lastname" @input="syncName()" required
            class="{{ $field('lastname') }}" placeholder="Dupont">
        @error('lastname')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
            Nom d'affichage <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" x-model="name" @input="onNameInput()" required
            class="{{ $field('name') }}" placeholder="Jean Dupont">
        <p class="text-xs text-gray-400 mt-1">Par défaut : prénom et nom de famille.</p>
        @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">
            Sexe <span class="text-red-500">*</span>
        </label>
        <select name="gender_id" required class="{{ $field('gender_id') }}">
            <option value="">— Sélectionner —</option>
            @foreach($genders as $gender)
                <option value="{{ $gender->id }}"
                    {{ (string) old('gender_id', $identityUser?->gender_id ?? '') === (string) $gender->id ? 'selected' : '' }}>
                    {{ $gender->name }}
                </option>
            @endforeach
        </select>
        @error('gender_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    </div>
</div>
