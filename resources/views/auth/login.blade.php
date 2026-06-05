<x-guest-layout>

    {{-- Session status --}}
    <x-auth-session-status class="mb-5" :status="session('status')" />

    {{-- Heading --}}
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">Connexion</h2>
        <p class="text-sm text-gray-500 mt-1">Bienvenue. Veuillez vous identifier pour continuer.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('messages.email') }}
            </label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-envelope text-sm"></i>
                </span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 transition {{ $errors->has('email') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 1rem;"
                    placeholder="votre@email.com"
                >
            </div>
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Password --}}
        <div x-data="{ show: false }">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
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
                    autocomplete="current-password"
                    class="w-full py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 transition {{ $errors->has('password') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="••••••••"
                >
                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                    :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                >
                    <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Remember me --}}
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                >
                <span class="text-sm text-gray-600">{{ __('messages.remember_me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   class="text-sm text-blue-600 hover:text-blue-800 transition-colors">
                    {{ __('messages.forgot_password') }}
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mt-2"
        >
            <i class="fas fa-sign-in-alt mr-2"></i>
            {{ __('messages.login') }}
        </button>

    </form>

</x-guest-layout>
