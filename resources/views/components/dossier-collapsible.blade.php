@props([
    'title',
    'subtitle' => null,
    /** Bloc ouvert au chargement. */
    'open' => false,
])

<section {{ $attributes->class(['dossier-card scroll-mt-24']) }}
    x-data="{ open: @js($open) }"
    x-init="if ($el.id && window.location.hash === '#' + $el.id) { open = true; }"
>
    <button
        type="button"
        @click="open = !open"
        class="dossier-card-head w-full text-left cursor-pointer hover:bg-gray-100/70 transition-colors"
        :aria-expanded="open"
    >
        <div class="min-w-0 flex-1">
            <h2>{{ $title }}</h2>
            @if(filled($subtitle))
                <p class="text-xs text-gray-500 mt-0.5 font-normal">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 shrink-0" @click.stop>
                {{ $actions }}
            </div>
        @endisset
        <svg
            class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200"
            :class="{ 'rotate-180': open }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            aria-hidden="true"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-cloak x-transition class="border-t border-gray-100">
        {{ $slot }}
    </div>
</section>
