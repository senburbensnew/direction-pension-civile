<x-guest-layout>

    {{-- Session status --}}
    <x-auth-session-status class="mb-5" :status="session('status')" />

    {{-- Heading --}}
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">Connexion</h2>
        <p class="text-base text-gray-700 mt-1">
            Bienvenue. Veuillez vous identifier pour continuer.
        </p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
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
                    placeholder="e-mail, NIF ou code pension"
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
                        Mot de passe oublié ?
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
                    :aria-label="show ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
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
            class="w-full py-3 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-2"
        >
            <i class="fas fa-sign-in-alt mr-2"></i>
            {{ __('messages.login') }}
        </button>

        {{-- Création de compte --}}
        <p class="text-center text-sm text-gray-500 pt-1">
            Pas encore de compte ?
            <a
                href="{{ route('demandes.compte.create') }}"
                class="text-navy hover:text-orange-500 font-medium"
            >
                {{ __('messages.request_account') }}
            </a>
        </p>

        {{-- Retour à l'accueil --}}
        <div class="pt-2 text-center">
            <a
                href="{{ url('/') }}"
                class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-[#173052] font-medium transition-colors"
            >
                <i class="fas fa-home text-xs"></i>
                <span>Retour à l’accueil</span>
            </a>
        </div>

    </form>
</x-guest-layout>