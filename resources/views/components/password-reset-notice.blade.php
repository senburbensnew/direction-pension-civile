@props(['notice'])

@php
    $kind = is_array($notice) ? ($notice['kind'] ?? null) : null;
    $email = is_array($notice) ? ($notice['email'] ?? null) : null;
    $phone = is_array($notice) ? ($notice['phone'] ?? null) : null;

    $icon = match ($kind) {
        'sms' => 'fa-sms',
        'both' => 'fa-paper-plane',
        default => 'fa-envelope-open-text',
    };

    $titleKey = match ($kind) {
        'sms' => 'messages.forgot_password_reset_notice_title_sms',
        'both' => 'messages.forgot_password_reset_notice_title_both',
        default => 'messages.forgot_password_reset_notice_title_email',
    };
@endphp

@if ($kind)
    <div
        {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-[#173052]/12 bg-gradient-to-br from-slate-50 via-white to-orange-50/40 shadow-sm']) }}
        role="status"
        aria-live="polite"
    >
        <div class="flex gap-3 p-4 sm:gap-4 sm:p-5">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#173052]/10 text-[#173052]"
                aria-hidden="true"
            >
                <i class="fas {{ $icon }} text-lg"></i>
            </div>

            <div class="min-w-0 flex-1 space-y-2 text-left">
                <p class="text-sm font-semibold leading-snug text-[#173052] sm:text-base">
                    {{ __($titleKey) }}
                </p>

                @if ($kind === 'email' && $email)
                    <p class="text-sm text-gray-600">
                        {{ __('messages.forgot_password_reset_notice_lead_email') }}
                    </p>
                    <p class="inline-flex max-w-full items-center truncate rounded-lg bg-white px-3 py-1.5 font-mono text-sm font-medium text-[#173052] ring-1 ring-[#173052]/15">
                        {{ $email }}
                    </p>
                @elseif ($kind === 'sms' && $phone)
                    <p class="text-sm text-gray-600">
                        {{ __('messages.forgot_password_reset_notice_lead_sms') }}
                    </p>
                    <p class="inline-flex items-center rounded-lg bg-white px-3 py-1.5 font-mono text-sm font-medium text-[#173052] ring-1 ring-[#173052]/15">
                        {{ $phone }}
                    </p>
                @elseif ($kind === 'both' && $email && $phone)
                    <p class="text-sm text-gray-600">
                        {{ __('messages.forgot_password_reset_notice_lead_both') }}
                    </p>
                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                        <span class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-1.5 text-sm text-gray-700 ring-1 ring-[#173052]/15">
                            <i class="fas fa-envelope text-xs text-[#173052]/70" aria-hidden="true"></i>
                            <span class="font-mono font-medium text-[#173052]">{{ $email }}</span>
                        </span>
                        <span class="inline-flex items-center gap-2 rounded-lg bg-white px-3 py-1.5 text-sm text-gray-700 ring-1 ring-[#173052]/15">
                            <i class="fas fa-sms text-xs text-[#173052]/70" aria-hidden="true"></i>
                            <span class="font-mono font-medium text-[#173052]">{{ $phone }}</span>
                        </span>
                    </div>
                @endif

                <p class="text-xs leading-relaxed text-gray-500">
                    {{ __('messages.forgot_password_reset_notice_hint') }}
                </p>
            </div>
        </div>
    </div>
@endif
