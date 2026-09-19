@props(['kicker', 'title', 'intro' => null, 'tone' => 'navy'])

<div {{ $attributes->class('text-center mb-10 md:mb-12') }}>
    <span @class([
        'inline-flex items-center gap-3 text-sm font-bold uppercase tracking-[0.18em]',
        'text-orange-500' => $tone !== 'light',
        'text-orange-300' => $tone === 'light',
    ])>
        <span @class(['w-8 h-px', 'bg-orange-400' => $tone !== 'light', 'bg-orange-300/80' => $tone === 'light'])></span>
        {{ $kicker }}
        <span @class(['w-8 h-px', 'bg-orange-400' => $tone !== 'light', 'bg-orange-300/80' => $tone === 'light'])></span>
    </span>
    <h2 @class([
        'text-3xl md:text-4xl font-bold tracking-tight mt-3',
        'text-navy' => $tone !== 'light',
        'text-white' => $tone === 'light',
    ])>{{ $title }}</h2>
    @if($intro)
        <p @class([
            'max-w-2xl mx-auto mt-3 leading-relaxed text-base',
            'text-gray-600' => $tone !== 'light',
            'text-blue-100' => $tone === 'light',
        ])>{{ $intro }}</p>
    @endif
</div>
