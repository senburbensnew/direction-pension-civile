@props([
    /** Classes Tailwind pour le padding bas (ex. pb-8, pb-28). */
    'bottom' => 'pb-8',
])

<div {{ $attributes->merge([
    'class' => "dossier-page site-gutter pt-4 {$bottom}",
]) }}>
    {{ $slot }}
</div>
