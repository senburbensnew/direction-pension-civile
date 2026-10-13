@props([
    'sources',
    'alt' => '',
    'priority' => false,
    'sizes' => '100vw',
])

@php
    /** @var array{url: string, webp: ?string, srcset?: ?string, webp_srcset?: ?string} $sources */
    $loading = $priority ? 'eager' : 'lazy';
    $imgAttrs = $attributes->merge([
        'alt' => $alt,
        'loading' => $loading,
        'decoding' => 'async',
    ]);
    if ($priority) {
        $imgAttrs = $imgAttrs->merge(['fetchpriority' => 'high']);
    }
    if ($sources['srcset'] ?? null) {
        $imgAttrs = $imgAttrs->merge(['srcset' => $sources['srcset'], 'sizes' => $sizes]);
    }
@endphp

@if (($sources['webp'] ?? null) || ($sources['webp_srcset'] ?? null))
    <picture>
        @if ($sources['webp_srcset'] ?? null)
            <source type="image/webp" srcset="{{ $sources['webp_srcset'] }}" sizes="{{ $sizes }}">
        @elseif ($sources['webp'] ?? null)
            <source type="image/webp" srcset="{{ $sources['webp'] }}">
        @endif
        <img src="{{ $sources['url'] }}" {{ $imgAttrs }}>
    </picture>
@else
    <img src="{{ $sources['url'] }}" {{ $imgAttrs }}>
@endif
