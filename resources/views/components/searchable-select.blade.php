@props([
    'name',
    'id' => null,
    'label' => null,
    'options' => [],
    'value' => '',
    'placeholder' => 'Sélectionner…',
    'searchPlaceholder' => 'Rechercher…',
    'searchThreshold' => 8,
    'forceSearch' => null,
    'required' => false,
    'inputClass' => 'dpc-filter-field',
    'labelClass' => 'block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1 truncate',
])

@php
    $inputId = $id ?? $name;
    $normalizedOptions = collect($options)
        ->map(fn ($opt) => [
            'value' => (string) ($opt['value'] ?? ''),
            'label' => (string) ($opt['label'] ?? ''),
        ])
        ->values()
        ->all();
    $optionCount = count($normalizedOptions);
    $useSearch = $forceSearch ?? ($optionCount >= $searchThreshold);
    $selectedValue = (string) old($name, $value);
@endphp

<div {{ $attributes->merge(['class' => 'min-w-0']) }}>
    @if($label)
        <label for="{{ $inputId }}" class="{{ $labelClass }}">{{ $label }}</label>
    @endif

    @if(! $useSearch)
        <select name="{{ $name }}" id="{{ $inputId }}" class="{{ $inputClass }}" @if($required) required @endif>
            @foreach($normalizedOptions as $opt)
                <option value="{{ $opt['value'] }}" @selected($selectedValue === $opt['value'])>{{ $opt['label'] }}</option>
            @endforeach
        </select>
    @else
        <div
            class="relative"
            x-data="searchableSelect({
                name: @js($name),
                selected: @js($selectedValue),
                options: @js($normalizedOptions),
                placeholder: @js($placeholder),
                searchPlaceholder: @js($searchPlaceholder),
            })"
            @keydown.escape.window="open = false"
        >
            <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" :value="selectedValue" @if($required) required @endif>

            <button
                type="button"
                class="{{ $inputClass }} flex items-center justify-between gap-2"
                @click="toggle()"
                :aria-expanded="open"
                aria-haspopup="listbox"
            >
                <span class="truncate text-gray-800" x-text="selectedLabel"></span>
                <i class="fas fa-chevron-down dpc-filter-field-chevron shrink-0 transition-transform" :class="{ 'rotate-180': open }" aria-hidden="true"></i>
            </button>

            <div
                x-show="open"
                x-cloak
                @click.outside="open = false"
                class="absolute z-50 mt-1 w-full min-w-[12rem] border border-gray-200 bg-white shadow-lg"
            >
                <div class="p-2 border-b border-gray-100">
                    <input
                        type="search"
                        x-ref="searchInput"
                        x-model="query"
                        :placeholder="searchPlaceholder"
                        class="w-full rounded-none border-gray-300 py-1.5 px-2 text-sm focus:border-navy focus:ring-navy/20"
                        @keydown.stop
                    >
                </div>
                <ul class="max-h-48 overflow-y-auto py-1 text-sm" role="listbox">
                    <template x-for="option in filteredOptions" :key="option.value + option.label">
                        <li>
                            <button
                                type="button"
                                class="w-full px-3 py-1.5 text-left hover:bg-gray-50 truncate"
                                :class="{ 'bg-sky-50 text-navy font-medium': option.value === selectedValue }"
                                @click="select(option.value)"
                                x-text="option.label"
                            ></button>
                        </li>
                    </template>
                    <li x-show="filteredOptions.length === 0" class="px-3 py-2 text-gray-400 text-xs">
                        Aucun résultat
                    </li>
                </ul>
            </div>
        </div>
    @endif
</div>
