<x-guest-layout>

    {{-- Session status --}}
    <x-auth-session-status class="mb-5" :status="session('status')" />

    {{-- Heading --}}
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-gray-800">Mot de passe oublié</h2>
        <p class="text-sm text-gray-500 mt-1">Saisissez votre e-mail pour recevoir un lien de réinitialisation.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-semibold text-sm rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 mt-2"
        >
            <i class="fas fa-paper-plane mr-2"></i>
            Envoyer le lien de réinitialisation
        </button>

        {{-- Back to login --}}
        <p class="text-center text-sm text-gray-500 pt-2">
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">
                <i class="fas fa-arrow-left text-xs mr-1"></i>
                Retour à la connexion
            </a>
        </p>
    </form>

</x-guest-layout>
