@props([
    'class' => 'w-16 h-16 md:w-24 md:h-24 object-cover shrink-0',
    'loading' => 'lazy',
])

<x-optimized-image
    src="images/setting-logo-1-M13oPLiYoM.png"
    alt="Logo — Direction de la Pension Civile"
    :class="$class"
    :loading="$loading"
    width="96"
    height="96"
    {{ $attributes }}
></x-optimized-image>
