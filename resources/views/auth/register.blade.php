<x-guest-layout>
    @php
        $pensionnaireType = $userTypes->firstWhere('name', 'pensionnaire');
        $pensionnaireId   = $pensionnaireType ? $pensionnaireType->id : null;
    @endphp

    <div class="mb-5 text-center">
        <h2 class="text-xl font-bold text-gray-800">Créer un compte</h2>
        <p class="text-sm text-gray-500 mt-0.5">Remplissez les informations ci-dessous pour vous inscrire.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- Row 1 : avatar + user type side by side --}}
        <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
            <div class="flex-shrink-0">
                <x-profile-picture :showLabel="false" />
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-700 mb-2">Qui êtes-vous ?</p>
                <div class="flex flex-col gap-2">
                    @foreach ($userTypes as $type)
                        @php
                            $icons   = ['pensionnaire' => 'fa-user-clock', 'fonctionnaire' => 'fa-briefcase', 'institution' => 'fa-building'];
                            $icon    = $icons[$type->name] ?? 'fa-user';
                            $checked = old('user_type_id', request()->isMethod('get') ? $pensionnaireId : null) == $type->id;
                        @endphp
                        <label for="user_type_{{ $type->name }}"
                               class="user-type-card flex items-center gap-2 cursor-pointer border-2 rounded-lg px-3 py-2 transition-all select-none {{ $checked ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                               data-type="{{ $type->name }}">
                            <input type="radio" id="user_type_{{ $type->name }}" name="user_type_id"
                                   value="{{ $type->id }}" data-type-name="{{ $type->name }}"
                                   {{ $checked ? 'checked' : '' }} class="sr-only">
                            <i class="fas {{ $icon }} text-sm w-4 text-center"></i>
                            <span class="text-xs font-medium">{{ ucfirst($type->name) }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('user_type_id')" class="mt-1" />
            </div>
        </div>

        {{-- Institution name --}}
        <div id="name_container" class="hidden">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'institution</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-building text-sm"></i>
                </span>
                <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name"
                       placeholder="Nom de l'institution"
                       class="w-full py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                       style="padding-left:2.25rem;padding-right:1rem;">
            </div>
            <x-input-error :messages="$errors->get('name')" class="mt-1" />
        </div>

        {{-- Last name + First name --}}
        <div id="lastname_container" class="grid grid-cols-2 gap-3">
            <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                <input id="lastname" type="text" name="lastname" value="{{ old('lastname') }}" autofocus
                       autocomplete="family-name" placeholder="MILORME"
                       class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <x-input-error :messages="$errors->get('lastname')" class="mt-1" />
            </div>
            <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom(s)</label>
                <input id="firstname" type="text" name="firstname" value="{{ old('firstname') }}"
                       autocomplete="given-name" placeholder="Pierre Rubens"
                       class="w-full py-2.5 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                <x-input-error :messages="$errors->get('firstname')" class="mt-1" />
            </div>
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Adresse e-mail</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-envelope text-sm"></i>
                </span>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email"
                       placeholder="exemple@gmail.com"
                       class="w-full py-2.5 border {{ $errors->has('email') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500' }} rounded-lg text-sm focus:outline-none focus:ring-2 transition"
                       style="padding-left:2.25rem;padding-right:1rem;">
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1" />
        </div>

        {{-- Password + Confirm side by side --}}
        <div class="grid grid-cols-2 gap-3">
            <div x-data="{ show: false }">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input id="password" :type="show ? 'text' : 'password'" name="password"
                           autocomplete="new-password" placeholder="••••••••"
                           class="w-full py-2.5 border {{ $errors->has('password') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500' }} rounded-lg text-sm focus:outline-none focus:ring-2 transition"
                           style="padding-left:2.25rem;padding-right:2.25rem;">
                    <button type="button" @click="show=!show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i :class="show?'fas fa-eye-slash':'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-1" />
            </div>
            <div x-data="{ show: false }">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-lock text-sm"></i>
                    </span>
                    <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                           autocomplete="new-password" placeholder="••••••••"
                           class="w-full py-2.5 border {{ $errors->has('password_confirmation') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500' }} rounded-lg text-sm focus:outline-none focus:ring-2 transition"
                           style="padding-left:2.25rem;padding-right:2.25rem;">
                    <button type="button" @click="show=!show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                        <i :class="show?'fas fa-eye-slash':'fas fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
            </div>
        </div>

        {{-- NIF + Pension code side by side --}}
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="nif" class="block text-sm font-medium text-gray-700 mb-1">NIF</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-hashtag text-sm"></i>
                    </span>
                    <input id="nif" type="text" name="nif" value="{{ old('nif') }}"
                           placeholder="123-456-789-0"
                           class="w-full py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           style="padding-left:2.25rem;padding-right:1rem;">
                </div>
                <x-input-error :messages="$errors->get('nif')" class="mt-1" />
            </div>
            <div id="pension_code_container" class="hidden">
                <label for="pension_code" class="block text-sm font-medium text-gray-700 mb-1">Code pension</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-id-card text-sm"></i>
                    </span>
                    <input id="pension_code" type="text" name="pension_code" value="{{ old('pension_code') }}"
                           placeholder="PEN-987654321"
                           class="w-full py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                           style="padding-left:2.25rem;padding-right:1rem;">
                </div>
                <x-input-error :messages="$errors->get('pension_code')" class="mt-1" />
            </div>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <i class="fas fa-user-plus mr-2"></i>Créer mon compte
        </button>

        <p class="text-center text-sm text-gray-500">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">Se connecter</a>
        </p>
    </form>

    <script>
        document.querySelectorAll('input[name="user_type_id"]').forEach(radio => {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.user-type-card').forEach(card => {
                    card.classList.remove('border-blue-500', 'bg-blue-50', 'text-blue-700');
                    card.classList.add('border-gray-200', 'text-gray-600');
                });
                const activeCard = document.querySelector(`.user-type-card[data-type="${this.dataset.typeName}"]`);
                if (activeCard) {
                    activeCard.classList.add('border-blue-500', 'bg-blue-50', 'text-blue-700');
                    activeCard.classList.remove('border-gray-200', 'text-gray-600');
                }

                const isInstitution  = this.dataset.typeName === 'institution';
                const isPensionnaire = this.dataset.typeName === 'pensionnaire';

                document.getElementById('pension_code_container').classList.toggle('hidden', !isPensionnaire);
                document.getElementById('name_container').style.display     = isInstitution ? 'block' : 'none';
                document.getElementById('lastname_container').style.display = isInstitution ? 'none'  : 'grid';

                const nameInput = document.getElementById('name');
                nameInput.readOnly    = !isInstitution;
                nameInput.placeholder = isInstitution ? "Nom de l'institution" : 'Pierre Rubens MILORME';
                if (!isInstitution) updateNameField();
            });
        });

        function updateNameField() {
            const selected = document.querySelector('input[name="user_type_id"]:checked');
            if (selected?.dataset.typeName === 'institution') return;
            const last  = document.getElementById('lastname')?.value.trim()  ?? '';
            const first = document.getElementById('firstname')?.value.trim() ?? '';
            const name  = document.getElementById('name');
            if (name) name.value = [first, last].filter(Boolean).join(' ');
        }

        document.getElementById('lastname')?.addEventListener('input', updateNameField);
        document.getElementById('firstname')?.addEventListener('input', updateNameField);
        document.querySelector('input[name="user_type_id"]:checked')?.dispatchEvent(new Event('change'));
    </script>
</x-guest-layout>
