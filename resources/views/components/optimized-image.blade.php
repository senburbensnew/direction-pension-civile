@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'loading' => 'lazy',
    'priority' => false,
    'sizes' => '100vw',
])

@php
    $media = \App\Support\PublicMedia::resolve($src);
    $imgLoading = $priority ? 'eager' : $loading;
    $imgAttrs = $attributes->merge([
        'alt' => $alt,
        'loading' => $imgLoading,
        'decoding' => 'async',
    ]);
    if ($width) {
        $imgAttrs = $imgAttrs->merge(['width' => $width]);
    }
    if ($height) {
        $imgAttrs = $imgAttrs->merge(['height' => $height]);
    }
    if ($priority) {
        $imgAttrs = $imgAttrs->merge(['fetchpriority' => 'high']);
    }
    if ($media['srcset'] ?? null) {
        $imgAttrs = $imgAttrs->merge(['srcset' => $media['srcset'], 'sizes' => $sizes]);
    }
@endphp

@if ($media['webp'] || ($media['webp_srcset'] ?? null))
    <picture>
        @if ($media['webp_srcset'] ?? null)
            <source type="image/webp" srcset="{{ $media['webp_srcset'] }}" sizes="{{ $sizes }}">
        @elseif ($media['webp'])
            <source type="image/webp" srcset="{{ $media['webp'] }}">
        @endif
        <img src="{{ $media['url'] }}" {{ $imgAttrs }}>
    </picture>
@else
    <img src="{{ $media['url'] }}" {{ $imgAttrs }}>
@endif
