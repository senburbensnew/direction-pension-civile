<x-guest-layout :no-scroll="true">

    <x-auth-session-status class="mb-3" :status="session('status')" />

    @if (session('warning'))
        <div
            class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950"
            role="status"
        >
            {{ session('warning') }}
        </div>
    @endif

    @php
        $deliveryKind = $destination['kind'] ?? null;
        $isEmail = $deliveryKind === 'email';
        $isSms = $deliveryKind === 'sms';
    @endphp

    <div class="mb-3 text-center">
        <div
            class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-[#173052]/10 text-[#173052]"
            aria-hidden="true"
        >
            @if ($isEmail)
                <i class="fas fa-envelope text-xl"></i>
            @elseif ($isSms)
                <i class="fas fa-sms text-xl"></i>
            @else
                <i class="fas fa-shield-alt text-xl"></i>
            @endif
        </div>

        <h2 class="text-xl font-bold text-gray-800">
            {{ __('guest.login.two_factor_title') }}
        </h2>

        <p class="mt-1 max-w-md mx-auto text-sm text-gray-700 leading-relaxed">
            @if ($isEmail)
                {{ __('guest.login.two_factor_intro_email', ['email' => $destination['email'] ?? '***']) }}
            @elseif ($isSms)
                {{ __('guest.login.two_factor_intro_sms', ['phone' => $destination['phone'] ?? '***']) }}
            @else
                {{ __('guest.login.two_factor_intro') }}
            @endif
        </p>

        <p class="mt-2 max-w-md mx-auto text-xs text-gray-500">
            {{ __('messages.login_two_factor_email_hint') }}
        </p>
    </div>

    <form method="POST" action="{{ route('login.two-factor.store') }}" class="space-y-3.5">
        @csrf

        <div>
            <label for="code" class="block text-base font-medium text-gray-700 mb-1">
                {{ __('guest.login.two_factor_code_label') }}
            </label>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <i class="fas fa-shield-alt text-sm" aria-hidden="true"></i>
                </span>

                <input
                    id="code"
                    type="text"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    autofocus
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    maxlength="6"
                    pattern="[0-9]{6}"
                    class="w-full py-2.5 border rounded-lg text-center text-lg font-mono tracking-[0.35em] focus:outline-none focus:ring-2 transition {{ $errors->has('code') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500' }}"
                    style="padding-left: 2.25rem; padding-right: 2.25rem;"
                    placeholder="000000"
                >
            </div>

            <x-input-error :messages="$errors->get('code')" class="mt-1.5" />
        </div>

        <button
            type="submit"
            class="w-full py-2.5 px-4 bg-[#173052] hover:bg-orange-600 active:bg-orange-700 text-white font-semibold text-base rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 mt-1"
        >
            <i class="fas fa-check-circle mr-2" aria-hidden="true"></i>
            {{ __('guest.login.two_factor_submit') }}
        </button>
    </form>

    <form method="POST" action="{{ route('login.two-factor.resend') }}" class="mt-4">
        @csrf
        <p class="text-center text-sm text-gray-500">
            <button
                type="submit"
                class="text-[#173052] hover:text-orange-500 font-medium transition-colors"
            >
                <i class="fas fa-redo text-xs mr-1" aria-hidden="true"></i>
                {{ __('guest.login.two_factor_resend') }}
            </button>
        </p>
    </form>

    <p class="mt-4 text-center text-sm text-gray-500">
        <a
            href="{{ route('login') }}"
            class="text-[#173052] hover:text-orange-500 font-medium transition-colors"
        >
            <i class="fas fa-arrow-left text-xs mr-1" aria-hidden="true"></i>
            {{ __('guest.password.back_to_login') }}
        </a>
    </p>

</x-guest-layout>
