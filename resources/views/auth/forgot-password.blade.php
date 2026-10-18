<x-guest-layout>

    @if (session('password_reset_notice'))
        <x-password-reset-notice class="mb-5" :notice="session('password_reset_notice')" />
    @else
        <x-auth-session-status class="mb-5" :status="session('status')" />
    @endif

    {{-- Heading --}}
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">
            {{ __('guest.password.forgot_title') }}
        </h2>

        <p class="mt-2 max-w-md mx-auto text-xs sm:text-sm text-gray-500 leading-relaxed">
            {{ __('messages.forgot_password_instructions') }}
        </p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        {{-- Identifiant --}}
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
                    value="{{ old('login', old('email')) }}"
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

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full py-3 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-2"
        >
            <i class="fas fa-paper-plane mr-2"></i>
            {{ __('messages.send_reset_link') }}
        </button>

        <p class="text-center text-sm text-gray-500 pt-1">
            <a
                href="{{ route('password.reset.code') }}"
                class="text-[#173052] hover:text-orange-500 font-medium transition-colors"
            >
                <i class="fas fa-sms text-xs mr-1"></i>
                {{ __('guest.password.reset_code_link') }}
            </a>
        </p>

        {{-- Retour à la connexion --}}
        <p class="text-center text-sm text-gray-500 pt-1">
            <a
                href="{{ route('login') }}"
                class="text-[#173052] hover:text-orange-500 font-medium transition-colors"
            >
                <i class="fas fa-arrow-left text-xs mr-1"></i>
                {{ __('guest.password.back_to_login') }}
            </a>
        </p>

    </form>

</x-guest-layout>
