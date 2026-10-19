<x-guest-layout :no-scroll="true">

    {{-- Heading --}}
    <div class="mb-3 text-center">
        <h2 class="text-xl font-bold text-gray-800">
            {{ __('guest.password_required.title') }}
        </h2>
        <p class="text-sm text-gray-700 mt-1">
            {{ __('guest.password_required.intro') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.required.update') }}" class="space-y-3.5">
        @csrf
        @method('PUT')

        {{-- Mot de passe actuel --}}
        <div x-data="{ show: false }">
            <label for="current_password" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('guest.password_required.current_password') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                </span>

                <input
                    id="current_password"
                    :type="show ? 'text' : 'password'"
                    name="current_password"
                    required
                    autofocus
                    autocomplete="current-password"
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->updatePassword->has('current_password') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="••••••••"
                >

                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="show ? @js(__('guest.login.hide_password')) : @js(__('guest.login.show_password'))"
                >
                    <i
                        :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"
                        class="text-sm"
                    ></i>
                </button>
            </div>

            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-1.5" />
        </div>

        {{-- Nouveau mot de passe --}}
        <div x-data="{ show: false }">
            <label for="password" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('guest.password_required.new_password') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-key text-sm"></i>
                </span>

                <input
                    id="password"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->updatePassword->has('password') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="••••••••"
                >

                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="show ? @js(__('guest.login.hide_password')) : @js(__('guest.login.show_password'))"
                >
                    <i
                        :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"
                        class="text-sm"
                    ></i>
                </button>
            </div>

            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-1.5" />
        </div>

        {{-- Confirmation --}}
        <div x-data="{ show: false }">
            <label for="password_confirmation" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('guest.password.confirm_label') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                </span>

                <input
                    id="password_confirmation"
                    :type="show ? 'text' : 'password'"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->updatePassword->has('password_confirmation') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="••••••••"
                >

                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="show ? @js(__('guest.login.hide_password')) : @js(__('guest.login.show_password'))"
                >
                    <i
                        :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"
                        class="text-sm"
                    ></i>
                </button>
            </div>

            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-1"
        >
            <i class="fas fa-save mr-2"></i>
            {{ __('guest.password_required.submit') }}
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <p class="text-center text-sm text-gray-500">
            <button
                type="submit"
                class="text-[#173052] hover:text-orange-500 font-medium transition-colors"
            >
                <i class="fas fa-sign-out-alt text-xs mr-1"></i>
                {{ __('guest.password_required.logout') }}
            </button>
        </p>
    </form>
</x-guest-layout>
