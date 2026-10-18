<span {{ $attributes->class(['relative inline-flex cursor-help group/tooltip']) }}>
    {{ $slot }}
    <span
        role="tooltip"
        class="pointer-events-none invisible absolute bottom-full left-1/2 z-10 mb-1 w-[7.5rem] -translate-x-1/2 rounded-md bg-black px-1.5 py-1 text-center text-sm text-white opacity-0 shadow-md transition-opacity group-hover/tooltip:visible group-hover/tooltip:opacity-100 after:absolute after:left-1/2 after:top-full after:-ml-1 after:border-[5px] after:border-transparent after:border-t-black after:content-['']">
        {{ $text }}
    </span>
</span>
