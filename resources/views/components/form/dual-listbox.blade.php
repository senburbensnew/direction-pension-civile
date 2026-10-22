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
            <div
                class="h-48 max-w-full overflow-auto rounded-lg border border-gray-200 bg-white focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-400/30"
                role="listbox"
                aria-multiselectable="true"
            >
                <template x-for="item in availableItems" :key="'av-' + item.value">
                    <div
                        role="option"
                        @click="togglePick($event, 'available', item.value)"
                        :aria-selected="isPicked('available', item.value)"
                        :class="isPicked('available', item.value) ? 'bg-indigo-100 text-indigo-900' : 'hover:bg-gray-50'"
                        class="flex items-center gap-2 px-2 py-1.5 cursor-pointer text-sm text-gray-800 min-w-max"
                    >
                        <span class="font-mono text-xs whitespace-nowrap" x-text="item.label"></span>
                        <template x-if="item.description">
                            <span
                                class="inline-flex shrink-0 p-0.5"
                                @click.stop
                                @mouseenter="showInfoTooltip($event, item.description)"
                                @mouseleave="hideInfoTooltip()"
                            >
                                <i class="fas fa-info-circle text-gray-400 hover:text-indigo-600 text-xs" aria-hidden="true"></i>
                            </span>
                        </template>
                    </div>
                </template>
                <p x-show="availableItems.length === 0" class="px-2 py-3 text-xs text-gray-400 italic">Aucun élément.</p>
            </div>
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
            <div
                class="h-48 max-w-full overflow-auto rounded-lg border border-gray-200 bg-white focus-within:border-indigo-400 focus-within:ring-2 focus-within:ring-indigo-400/30"
                role="listbox"
                aria-multiselectable="true"
            >
                <template x-for="item in assignedItems" :key="'as-' + item.value">
                    <div
                        role="option"
                        @click="togglePick($event, 'assigned', item.value)"
                        :aria-selected="isPicked('assigned', item.value)"
                        :class="isPicked('assigned', item.value) ? 'bg-indigo-100 text-indigo-900' : 'hover:bg-gray-50'"
                        class="flex items-center gap-2 px-2 py-1.5 cursor-pointer text-sm text-gray-800 min-w-max"
                    >
                        <span class="font-mono text-xs whitespace-nowrap" x-text="item.label"></span>
                        <template x-if="item.description">
                            <span
                                class="inline-flex shrink-0 p-0.5"
                                @click.stop
                                @mouseenter="showInfoTooltip($event, item.description)"
                                @mouseleave="hideInfoTooltip()"
                            >
                                <i class="fas fa-info-circle text-gray-400 hover:text-indigo-600 text-xs" aria-hidden="true"></i>
                            </span>
                        </template>
                    </div>
                </template>
                <p x-show="assignedItems.length === 0" class="px-2 py-3 text-xs text-gray-400 italic">Aucun élément.</p>
            </div>
        </div>

        <template x-for="value in assignedValues" :key="'input-' + value">
            <input type="hidden" name="{{ $name }}" :value="value">
        </template>

        <div
            x-show="tooltipVisible"
            x-cloak
            x-transition.opacity.duration.150ms
            class="fixed z-[200] pointer-events-none rounded-md bg-gray-900 px-2 py-1.5 text-xs font-normal normal-case leading-snug text-white shadow-lg whitespace-normal"
            :style="tooltipStyle"
            x-text="tooltipText"
            role="tooltip"
        ></div>
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
