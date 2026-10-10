@props([
    'name' => 'code_pension',
    'label' => 'Code pension',
    'demande' => null,
    'required' => false,
])

@php
    $default = old($name, $demande?->data[$name] ?? $demande?->data['code_pension'] ?? auth()->user()?->displayPensionCode());
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $label }}</label>
    <input
        type="text"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $default }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'w-full rounded-md border-gray-300']) }}
    />
    @error($name)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
