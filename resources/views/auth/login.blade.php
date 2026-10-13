<x-guest-layout :no-scroll="true">

    {{-- Session status --}}
    <x-auth-session-status class="mb-3" :status="session('status')" />

    {{-- Heading --}}
    <div class="mb-3 text-center">
        <h2 class="text-xl font-bold text-gray-800">{{ __('guest.login.title') }}</h2>
        <p class="text-sm text-gray-700 mt-1">
            {{ __('guest.login.welcome') }}
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-3.5">
        @csrf
        {{-- Identifiant --}}
        <div>
            <div class="flex items-center gap-1.5 mb-1">
                <label for="login" class="block text-base font-medium text-gray-700">
                    {{ __('messages.login_identifier') }}
                </label>
                <span class="relative inline-flex group">
                    <button
                        type="button"
                        class="inline-flex items-center justify-center text-[#173052]/70 hover:text-[#173052] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#173052]/40 rounded-full"
                        aria-describedby="login-phone-hint"
                    >
                        <i class="fas fa-info-circle text-sm" aria-hidden="true"></i>
                        <span class="sr-only">{{ __('messages.login_phone_format_hint') }}</span>
                    </button>
                    <span
                        id="login-phone-hint"
                        role="tooltip"
                        class="pointer-events-none invisible absolute left-1/2 bottom-full z-50 mb-2 w-72 max-w-[min(18rem,calc(100vw-2rem))] -translate-x-1/2 rounded-lg bg-[#173052] px-3 py-2 text-left text-xs font-normal leading-relaxed text-white whitespace-pre-line opacity-0 shadow-lg transition-opacity duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100"
                    >
                        {{ __('messages.login_phone_format_hint') }}
                    </span>
                </span>
            </div>
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

        {{-- Password --}}
        <div x-data="{ show: false }">
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-base font-medium text-gray-700">
                    {{ __('messages.password') }}
                </label>

                {{-- Mot de passe oublié --}}
                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm text-[#173052] hover:text-orange-500 font-medium transition-colors"
                    >
                        {{ __('messages.forgot_password') }}
                    </a>
                @endif
            </div>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm"></i>
                </span>

                <input
                    id="password"
                    :type="show ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
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
                    <i
                        :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"
                        class="text-sm"
                    ></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-1"
        >
            <i class="fas fa-sign-in-alt mr-2"></i>
            {{ __('messages.login') }}
        </button>

        {{-- Création de compte --}}
        <p class="text-center text-sm text-gray-500">
            {{ __('guest.login.no_account') }}
            <a
                href="{{ route('demandes.compte.create') }}"
                class="text-navy hover:text-orange-500 font-medium"
            >
                {{ __('messages.request_account') }}
            </a>
        </p>
    </form>
</x-guest-layout>