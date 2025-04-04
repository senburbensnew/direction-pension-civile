<x-guest-layout>
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-md shadow-md">
            <ul class="list-disc pl-5 space-y-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf
        <!-- Name -->
        <div>
            <x-profile-picture :showLabel="false" />
        </div>
        <div class="hidden">
            <x-input-label for="name" :value="__('Nom complet')" />
            <x-text-input id="name" class="bg-gray-100 block mt-1 w-full" type="text" name="name"
                :value="old('name')" autocomplete="name" placeholder="Pierre Rubens MILORME" readonly />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="lastname" :value="__('Nom')" />
            <x-text-input id="lastname" autofocus class="block mt-1 w-full" type="text" name="lastname"
                :value="old('lastname')" autofocus autocomplete="lastname" placeholder="ex: MILORME" />
            <x-input-error :messages="$errors->get('lastname')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="firstname" :value="__('Prenom')" />
            <x-text-input id="firstname" class="block mt-1 w-full" type="text" name="firstname" :value="old('firstname')"
                autofocus autocomplete="firstname" placeholder="ex: Pierre Rubens" />
            <x-input-error :messages="$errors->get('firstname')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                autocomplete="email" placeholder="ex: example@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{--         <div class="mt-4">
            <x-input-label for="phone" :value="__('Telephone')" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')"
                autocomplete="phone" placeholder="" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="gender" :value="__('Sexe')" />
            <x-text-input id="gender" class="block mt-1 w-full" type="text" name="gender" :value="old('gender')"
                autocomplete="gender" placeholder="" />
            <x-input-error :messages="$errors->get('gender')" class="mt-2" />
        </div> --}}

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

        <!-- User Type (Radio Buttons) -->
        <div class="mt-4">
            <x-input-label for="user_type" :value="__('Qui êtes-vous ?')" />
            <!-- Radio buttons for user types -->
            <div class="flex justify-between">
                @foreach ($userTypes as $type)
                    <label for="user_type_{{ $type->name }}" class="{{ $loop->last ? 'ml-4' : '' }}">
                        <input type="radio" id="user_type_{{ $type->name }}" name="user_type_id"
                            value="{{ $type->id }}" {{ old('user_type_id') == $type->id ? 'checked' : '' }}
                            {{ old('user_type_id', request()->isMethod('get') ? 'pensionnaire' : null) == $type->name ? 'checked' : '' }} />
                        {{ ucfirst($type->name) }}
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('user_type_id')" class="mt-2" />
        </div>

        <!-- Pension Code (only visible when "Pensionnaire" is selected) -->
        <div id="pension_code_container" class="mt-4 hidden">
            <x-input-label for="pension_code" :value="__('Code de pension')" />
            <x-text-input id="pension_code" class="block mt-1 w-full" type="text" name="pension_code"
                :value="old('pension_code')" placeholder="ex: PEN-987654321" />
            <x-input-error :messages="$errors->get('pension_code')" class="mt-2" />
        </div>

        <!-- NIF -->
        <div class="mt-4">
            <x-input-label for="nif" :value="__('NIF')" />
            <x-text-input id="nif" class="block mt-1 w-full" type="text" name="nif" :value="old('nif')"
                placeholder="ex: 123-456-789-0" />
            <x-input-error :messages="$errors->get('nif')" class="mt-2" />
        </div>

        {{--         <div class="mt-4">
            <x-input-label for="ninu" :value="__('NINU')" />
            <x-text-input id="ninu" class="block mt-1 w-full" type="text" name="ninu" :value="old('ninu')"
                placeholder="ex: 123-456-789-0" />
            <x-input-error :messages="$errors->get('ninu')" class="mt-2" />
        </div> --}}
        {{-- 
        <div class="mt-4">
            <x-input-label for="signature" :value="__('Signature')" />
            <x-signature-pad />
        </div> --}}

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
        // Show or hide pension code field based on selected user type (radio buttons)
        document.querySelectorAll('input[name="user_type_id"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var pensionCodeContainer = document.getElementById('pension_code_container');
                var pensionCodeInput = document.getElementById('pension_code');

                if (document.getElementById('user_type_pensionnaire').checked) {
                    pensionCodeContainer.classList.remove('hidden');
                } else {
                    pensionCodeContainer.classList.add('hidden');
                }
            });
        });

        // Trigger change event on page load to set the initial visibility state
        document.querySelector('input[name="user_type_id"]:checked')?.dispatchEvent(new Event('change'));
    </script>
    <script>
        // Function to update the 'name' field
        function updateNameField() {
            const lastname = document.getElementById('lastname').value.trim();
            const firstname = document.getElementById('firstname').value.trim();
            document.getElementById('name').value = [firstname, lastname].filter(Boolean).join(' ');
        }

        // Initial update when the page loads
        updateNameField();

        // Add event listeners to both fields
        document.getElementById('lastname').addEventListener('input', updateNameField);
        document.getElementById('firstname').addEventListener('input', updateNameField);
    </script>
</x-guest-layout>
