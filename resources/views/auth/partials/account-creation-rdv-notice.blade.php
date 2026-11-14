@props(['variant' => 'full'])

@php
    $isCompact = $variant === 'compact';
@endphp

<div
    @class([
        'rounded-lg border border-sky-200 bg-sky-50 text-sm text-sky-950',
        'mb-3 px-3 py-2.5' => $isCompact,
        'mb-4 px-4 py-3' => ! $isCompact,
    ])
    role="note"
>
    <div class="flex gap-3">
        <i
            class="fas fa-calendar-check text-sky-700 shrink-0 {{ $isCompact ? 'mt-0.5 text-sm' : 'mt-0.5' }}"
            aria-hidden="true"
        ></i>
        <div class="min-w-0">
            @unless ($isCompact)
                <p class="font-semibold text-sky-950">
                    {{ __('guest.account_request.rdv_notice_title') }}
                </p>
                <p class="mt-1 leading-relaxed text-sky-900/95">
                    {{ __('guest.account_request.rdv_notice') }}
                </p>
            @else
                <p class="leading-relaxed text-sky-900/95">
                    {{ __('guest.login.account_rdv_notice') }}
                </p>
            @endunless
        </div>
    </div>
</div>