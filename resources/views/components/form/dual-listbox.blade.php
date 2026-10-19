@props([
    'name' => 'items[]',
    'label' => null,
    'options' => [],
    'selected' => [],
    'availableTitle' => 'Disponibles',
    'selectedTitle' => 'Attribués',
    'hint' => null,
])

<div {{ $attributes->merge(['class' => 'sm:col-span-2']) }} data-dual-listbox>
    @if($label)
        <label class="block text-xs font-medium text-gray-600 mb-2">{{ $label }}</label>
    @endif
    @if($hint)
        <p class="text-xs text-gray-500 mb-2">{{ $hint }}</p>
    @endif

    <div
        x-data="dualListbox(@js(array_values($options)), @js(array_values($selected)))"
        class="flex flex-col md:flex-row gap-3 md:items-stretch"
    >
        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ $availableTitle }}</p>
            <select
                x-ref="availableList"
                multiple
                size="8"
                @change="syncPickedAvailable()"
                class="w-full rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30"
            >
                <template x-for="item in availableItems" :key="'av-' + item.value">
                    <option :value="item.value" x-text="item.label"></option>
                </template>
            </select>
        </div>

        <div class="flex md:flex-col flex-row items-center justify-center gap-2 shrink-0 py-1">
            <button type="button" title="Ajouter la sélection"
                @click="moveSelectedToAssigned()"
                class="w-9 h-9 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm font-semibold">
                &gt;
            </button>
            <button type="button" title="Retirer la sélection"
                @click="moveSelectedToAvailable()"
                class="w-9 h-9 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm font-semibold">
                &lt;
            </button>
            <button type="button" title="Tout ajouter"
                @click="moveAllToAssigned()"
                class="w-9 h-9 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                &gt;&gt;
            </button>
            <button type="button" title="Tout retirer"
                @click="moveAllToAvailable()"
                class="w-9 h-9 rounded border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-xs font-semibold">
                &lt;&lt;
            </button>
        </div>

        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 mb-1">{{ $selectedTitle }}</p>
            <select
                x-ref="assignedList"
                multiple
                size="8"
                @change="syncPickedAssigned()"
                class="w-full rounded-lg border border-gray-200 bg-white px-2 py-1 text-sm text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30"
            >
                <template x-for="item in assignedItems" :key="'as-' + item.value">
                    <option :value="item.value" x-text="item.label"></option>
                </template>
            </select>
        </div>

        <template x-for="value in assignedValues" :key="'input-' + value">
            <input type="hidden" name="{{ $name }}" :value="value">
        </template>
    </div>

    @error(str_replace('[]', '', $name))
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
    @error($name)
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
    @error($name.'.*')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
