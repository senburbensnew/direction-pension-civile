@props(['items' => [], 'columns' => true])

<ul {{ $attributes->class([$columns ? 'grid sm:grid-cols-2 gap-2' : 'space-y-2']) }}>
    @foreach($items as $item)
        <li class="flex items-start gap-2 text-base text-gray-700 leading-relaxed">
            <i class="fas fa-check text-orange-500 mt-1 text-xs shrink-0"></i>
            <span>{{ __($item) }}</span>
        </li>
    @endforeach
</ul>
