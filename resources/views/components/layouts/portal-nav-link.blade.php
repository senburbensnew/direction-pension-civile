@props(['href', 'active' => false, 'icon', 'title' => null])

<a href="{{ $href }}"
   @if($title) title="{{ $title }}" @endif
   {{ $attributes->class([
       'flex items-center gap-2.5 min-h-9 py-1.5 px-2.5 rounded-md text-[0.8rem] font-medium transition-colors',
       'group-data-[collapsed=true]/body:justify-center group-data-[collapsed=true]/body:px-2',
       '[&>i]:w-5 [&>i]:shrink-0 [&>i]:text-center [&>i]:text-[0.85rem]',
       'bg-white/20 text-white' => $active,
       'text-slate-400 hover:bg-white/10 hover:text-slate-200' => ! $active,
   ]) }}>
    <i class="{{ $icon }}"></i>
    <span class="truncate group-data-[collapsed=true]/body:hidden">{{ $slot }}</span>
</a>
