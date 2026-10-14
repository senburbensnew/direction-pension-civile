@props([
    'message',
    'type' => 'success',
    'duration' => 6000,
])

@php
    $styles = match ($type) {
        'error' => [
            'wrap' => 'bg-red-50 border-red-300 text-red-900',
            'icon' => 'fa-circle-exclamation text-red-500',
        ],
        'warning' => [
            'wrap' => 'bg-amber-50 border-amber-300 text-amber-950',
            'icon' => 'fa-triangle-exclamation text-amber-500',
        ],
        default => [
            'wrap' => 'bg-green-50 border-green-300 text-green-900',
            'icon' => 'fa-circle-check text-green-500',
        ],
    };
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-cloak
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-init="window.setTimeout(() => { show = false }, {{ (int) $duration }})"
    class="fixed top-20 right-4 left-4 sm:left-auto z-[100] max-w-md sm:max-w-sm pointer-events-none"
    role="alert"
    aria-live="polite"
>
    <div @class([
        'pointer-events-auto flex items-start gap-3 border rounded-lg px-4 py-3 text-sm shadow-lg card-shadow',
        $styles['wrap'],
    ])>
        <i @class(['fa-solid flex-shrink-0 mt-0.5', $styles['icon']]) aria-hidden="true"></i>
        <p class="flex-1 min-w-0 leading-snug">{{ $message }}</p>
        <button
            type="button"
            class="flex-shrink-0 text-current opacity-60 hover:opacity-100 transition-opacity -mr-1 p-1"
            @click="show = false"
            aria-label="Fermer"
        >
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>
</div>
