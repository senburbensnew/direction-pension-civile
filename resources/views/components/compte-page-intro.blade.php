@props([
    'eyebrow' => 'Compte pension',
    'title',
    'description' => null,
])

<div class="text-center">
    <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">{{ $eyebrow }}</p>
    <h1 class="text-3xl sm:text-4xl font-bold text-navy mt-2 mb-2">{{ $title }}</h1>
    @if($description)
        <p class="text-sm text-gray-500 max-w-2xl mx-auto">{{ $description }}</p>
    @endif
</div>
