@props([
    'text',
    'hintId' => 'format-hint-' . uniqid(),
])

<span {{ $attributes->merge(['class' => 'relative inline-flex group align-middle']) }}>
    <button
        type="button"
        class="inline-flex items-center justify-center text-[#173052]/70 hover:text-[#173052] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#173052]/40 rounded-full"
        aria-describedby="{{ $hintId }}"
    >
        <i class="fas fa-info-circle text-sm" aria-hidden="true"></i>
        <span class="sr-only">{{ $text }}</span>
    </button>
    <span
        id="{{ $hintId }}"
        role="tooltip"
        class="pointer-events-none invisible absolute left-1/2 bottom-full z-50 mb-2 w-72 max-w-[min(18rem,calc(100vw-2rem))] -translate-x-1/2 rounded-lg bg-[#173052] px-3 py-2 text-left text-xs font-normal leading-relaxed text-white whitespace-pre-line opacity-0 shadow-lg transition-opacity duration-150 group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100"
    >
        {{ $text }}
    </span>
</span>
