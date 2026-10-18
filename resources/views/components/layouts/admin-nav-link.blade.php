@props(['href', 'active' => false, 'icon'])

<a href="{{ $href }}"
   {{ $attributes->class([
       'flex items-center gap-2.5 py-[0.45rem] px-3.5 rounded-md text-[0.825rem] font-medium whitespace-nowrap transition-colors',
       '[&>i]:w-4 [&>i]:shrink-0 [&>i]:text-center [&>i]:text-[0.85rem]',
       'bg-white/20 text-white' => $active,
       'text-slate-400 hover:bg-white/10 hover:text-slate-200' => ! $active,
   ]) }}>
    <i class="{{ $icon }}"></i>
    {{ $slot }}
</a>
