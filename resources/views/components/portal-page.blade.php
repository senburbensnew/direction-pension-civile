@props([
    /** Classes Tailwind pour le padding bas (ex. pb-8, pb-28). */
    'bottom' => 'pb-8',
])

<div {{ $attributes->merge([
    'class' => "dossier-page max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 pt-4 {$bottom}",
]) }}>
    {{ $slot }}
</div>
