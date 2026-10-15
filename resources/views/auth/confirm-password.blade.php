<x-guest-layout :no-scroll="true">

    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-800">
            {{ __('guest.confirm_password.title') }}
        </h2>
    </div>

    @if ($reauthRequired)
        <div
            class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"
            role="status"
        >
            <p class="font-medium">{{ __('guest.confirm_password.reauth_required') }}</p>
            <p class="mt-2 text-amber-900/90">
                {{ __('guest.confirm_password.timeout_hint', ['minutes' => $confirmTimeoutMinutes]) }}
            </p>
            <p class="mt-2 text-amber-900/80">
                {{ __('guest.confirm_password.resume_hint') }}
            </p>
        </div>
    @else
        <p class="mb-4 text-sm text-gray-600">
            {{ __('guest.confirm_password.intro') }}
        </p>
        <p class="mb-4 text-xs text-gray-500">
            {{ __('guest.confirm_password.timeout_hint', ['minutes' => $confirmTimeoutMinutes]) }}
        </p>
    @endif

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <label for="password" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('messages.password') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-lock text-sm" aria-hidden="true"></i>
                </span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    class="w-full py-2.5 border rounded-lg text-base focus:outline-none focus:ring-2 transition {{ $errors->has('password') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 1rem;"
                >
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-1"
        >
            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>
            {{ __('guest.confirm_password.submit') }}
        </button>
    </form>
</x-guest-layout>
