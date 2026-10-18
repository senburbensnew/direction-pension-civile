<x-guest-layout :no-scroll="true">

    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-800">
            {{ __('guest.password_required.title') }}
        </h2>
    </div>

    <p class="mb-4 text-sm text-gray-600">
        {{ __('guest.password_required.intro') }}
    </p>

    <form method="POST" action="{{ route('password.required.update') }}" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" :value="__('messages.password')" />

            <x-text-input id="current_password" class="block mt-1 w-full"
                            type="password"
                            name="current_password"
                            required autofocus autocomplete="current-password" />

            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('guest.password_required.new_password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('guest.password.confirm_label')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-primary-button>
                {{ __('guest.password_required.submit') }}
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-600 underline hover:text-gray-900">
            {{ __('guest.password_required.logout') }}
        </button>
    </form>
</x-guest-layout>
