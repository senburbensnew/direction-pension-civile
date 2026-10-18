{{--
    Charge des entrées Vite via @stack('scripts') uniquement.
    Le CSS importé dans un .js est émis avec le même @vite.
    Usage :
        <x-push-vite :js="['resources/js/admin/faq-index.js']" />
--}}
@props([
    'css' => [],
    'js' => [],
])

@php
    $cssEntries = is_string($css) ? [$css] : array_values($css);
    $jsEntries = is_string($js) ? [$js] : array_values($js);
    $viteEntries = array_values(array_unique(array_merge($cssEntries, $jsEntries)));
@endphp

@if (count($viteEntries))
    @push('scripts')
        @vite($viteEntries)
    @endpush
@endif
