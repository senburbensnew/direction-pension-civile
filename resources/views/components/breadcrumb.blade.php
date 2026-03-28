<nav aria-label="Breadcrumb" class="mb-4 text-sm text-gray-600">
    <ol class="flex items-center space-x-2">
        @foreach ($items as $item)
            <li class="font-medium">
                @if (isset($item['url']) && !$loop->last)
                    <a href="{{ $item['url'] }}"
                       class="text-gray-500 hover:text-gray-800 transition">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="text-gray-800">
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>

            @if (!$loop->last)
                <li class="text-gray-400">›</li>
            @endif
        @endforeach
    </ol>
</nav>