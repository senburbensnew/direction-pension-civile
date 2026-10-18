<x-guest-layout>

    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">
            {{ __('guest.password.reset_code_title') }}
        </h2>

        <p class="text-base text-gray-700 mt-1">
            {{ __('guest.password.reset_code_intro') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.reset.code.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="login" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('messages.login_identifier') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-user text-sm"></i>
                </span>

                <input
                    id="login"
                    type="text"
                    name="login"
                    value="{{ old('login') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->has('login') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 1rem;"
                    placeholder="{{ __('messages.login_identifier_placeholder') }}"
                >
            </div>

            <x-input-error :messages="$errors->get('login')" class="mt-1.5" />
        </div>

        <div>
            <label for="code" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('guest.password.reset_code_label') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-sms text-sm"></i>
                </span>

                <input
                    id="code"
                    type="text"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    class="w-full py-2.5 border rounded-lg text-base tracking-widest focus:outline-none focus:ring-2 transition {{ $errors->has('code') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 1rem;"
                    placeholder="000000"
                >
            </div>

            <x-input-error :messages="$errors->get('code')" class="mt-1.5" />
        </div>

        <div x-data="{ show: false }">
            <label for="password" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('messages.password') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                </span>

                <input
                    id="password"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="new-password"
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->has('password') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="••••••••"
                >

                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="show ? @js(__('guest.login.hide_password')) : @js(__('guest.login.show_password'))"
                >
                    <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

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
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->has('password_confirmation') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="••••••••"
                >

                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="show ? @js(__('guest.login.hide_password')) : @js(__('guest.login.show_password'))"
                >
                    <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
        </div>

        <button
            type="submit"
            class="w-full py-3 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-2"
        >
            <i class="fas fa-key mr-2"></i>
            {{ __('guest.password.reset_button') }}
        </button>

        <p class="text-center text-sm text-gray-500 pt-1">
            <a
                href="{{ route('password.request') }}"
                class="text-[#173052] hover:text-orange-500 font-medium transition-colors"
            >
                <i class="fas fa-arrow-left text-xs mr-1"></i>
                {{ __('guest.password.back_to_forgot') }}
            </a>
        </p>

    </form>

</x-guest-layout>
