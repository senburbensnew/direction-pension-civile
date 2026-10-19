@props([
    'name',
    'options' => [],
    'selected' => [],
    'label' => null,
    'labelKey' => 'label',
    'valueKey' => 'value',
    'cols' => 'flex flex-wrap gap-3',
])

<div {{ $attributes->merge(['class' => 'sm:col-span-2']) }}>
    @if($label)
        <label class="block text-xs font-medium text-gray-600 mb-2">{{ $label }}</label>
    @endif
    <div class="{{ $cols }}">
        @foreach($options as $option)
            @php
                $value = is_array($option) ? ($option[$valueKey] ?? '') : $option;
                $text = is_array($option)
                    ? ($option[$labelKey] ?? $value)
                    : (is_string($option) ? ucfirst(str_replace('_', ' ', $option)) : (string) $option);
            @endphp
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" name="{{ $name }}" value="{{ $value }}"
                    {{ in_array($value, $selected, true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-400">
                {{ $text }}
            </label>
        @endforeach
    </div>
    @error($name)<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
    @error($name.'.*')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
</div>
