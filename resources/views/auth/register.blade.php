<x-guest-layout>
    @php
        // Get the pensionnaire type ID
        $pensionnaireType = $userTypes->firstWhere('name', 'pensionnaire');
        $pensionnaireId = $pensionnaireType ? $pensionnaireType->id : null;
    @endphp

    {{--     @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-md">
            <ul class="list-disc pl-5 space-y-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif --}}

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf
        <!-- Profile Picture -->
        <div>
            <x-profile-picture :showLabel="false" />
        </div>

        <!-- Institution Name Field -->
        <div id="name_container" class="hidden">
            <x-input-label for="name" :value="__('Nom')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                autocomplete="name" placeholder="Nom de l'institution" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Lastname Field -->
        <div class="mt-4" id="lastname_container">
            <x-input-label for="lastname" :value="__('Nom')" />
            <x-text-input id="lastname" autofocus class="block mt-1 w-full" type="text" name="lastname"
                :value="old('lastname')" autocomplete="lastname" placeholder="ex: MILORME" />
            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
        </div>

        <!-- Firstname Field -->
        <div class="mt-4" id="firstname_container">
            <x-input-label for="firstname" :value="__('Prenom')" />
            <x-text-input id="firstname" class="block mt-1 w-full" type="text" name="firstname" :value="old('firstname')"
                autocomplete="firstname" placeholder="ex: Pierre Rubens" />
            <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                autocomplete="email" placeholder="ex: example@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                autocomplete="new-password" placeholder="********" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" autocomplete="new-password" placeholder="********" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- User Type Selection -->
        <div class="mt-4">
            <x-input-label for="user_type" :value="__('Qui êtes-vous ?')" />
            <div class="flex justify-between">
                @foreach ($userTypes as $type)
                    <label for="user_type_{{ $type->name }}" class="{{ $loop->last ? 'ml-4' : '' }}">
                        <input type="radio" id="user_type_{{ $type->name }}" name="user_type_id"
                            value="{{ $type->id }}" data-type-name="{{ $type->name }}"
                            {{ old('user_type_id', request()->isMethod('get') ? $pensionnaireId : null) == $type->id ? 'checked' : '' }} />
                        {{ ucfirst($type->name) }}
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('user_type_id')" class="mt-2" />
        </div>

        <!-- Pension Code Field -->
        <div id="pension_code_container" class="mt-4 hidden">
            <x-input-label for="pension_code" :value="__('Code de pension')" />
            <x-text-input id="pension_code" class="block mt-1 w-full" type="text" name="pension_code"
                :value="old('pension_code')" placeholder="ex: PEN-987654321" />
            <x-input-error :messages="$errors->get('pension_code')" class="mt-2" />
        </div>

        <!-- NIF Field -->
        <div class="mt-4">
            <x-input-label for="nif" :value="__('NIF')" />
            <x-text-input id="nif" class="block mt-1 w-full" type="text" name="nif" :value="old('nif')"
                placeholder="ex: 123-456-789-0" />
            <x-input-error :messages="$errors->get('nif')" class="mt-2" />
        </div>

        <!-- Form Submission -->
        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>
            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        // Name field management
        function updateNameField() {
            const selectedRadio = document.querySelector('input[name="user_type_id"]:checked');
            if (selectedRadio?.dataset.typeName === 'institution') return;

            const lastname = document.getElementById('lastname').value.trim();
            const firstname = document.getElementById('firstname').value.trim();
            document.getElementById('name').value = [firstname, lastname].filter(Boolean).join(' ');
        }

        // User type change handler
        document.querySelectorAll('input[name="user_type_id"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const typeName = this.dataset.typeName;
                const isInstitution = typeName === 'institution';
                const isPensionnaire = typeName === 'pensionnaire';

                // Toggle pension code visibility
                document.getElementById('pension_code_container').classList.toggle('hidden', !
                    isPensionnaire);

                // Toggle name fields visibility
                document.getElementById('name_container').style.display = isInstitution ? 'block' : 'none';
                document.getElementById('lastname_container').style.display = isInstitution ? 'none' :
                    'block';
                document.getElementById('firstname_container').style.display = isInstitution ? 'none' :
                    'block';

                // Manage name field state
                const nameInput = document.getElementById('name');
                nameInput.readOnly = !isInstitution;
                nameInput.placeholder = isInstitution ? 'Nom de l\'institution' : 'Pierre Rubens MILORME';

                // Update name value if not institution
                if (!isInstitution) updateNameField();
            });
        });

        // Initial setup
        document.querySelector('input[name="user_type_id"]:checked')?.dispatchEvent(new Event('change'));
        document.getElementById('lastname').addEventListener('input', updateNameField);
        document.getElementById('firstname').addEventListener('input', updateNameField);
    </script>
</x-guest-layout>
