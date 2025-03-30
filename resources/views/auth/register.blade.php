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

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" autofocus
                autocomplete="name" placeholder="ex: Pierre Rubens MILORME" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                autocomplete="username" placeholder="ex: example@gmail.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                autocomplete="new-password" placeholder="*********" />
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
                        <input type="radio" id="user_type_{{ $type->name }}" name="user_type"
                            value="{{ $type->id }}" {{ old('user_type') == $type->id ? 'checked' : '' }}
                            {{ old('user_type', request()->isMethod('get') ? 'pensionnaire' : null) == $type->name ? 'checked' : '' }} />
                        {{ ucfirst($type->name) }}
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('user_type')" class="mt-2" />
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
        document.querySelectorAll('input[name="user_type"]').forEach(function(radio) {
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
        document.querySelector('input[name="user_type"]:checked')?.dispatchEvent(new Event('change'));
    </script>
</x-guest-layout>
