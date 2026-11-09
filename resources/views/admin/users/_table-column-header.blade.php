@php
    /** @var \App\Services\Admin\AdminUserColumnCatalog $columnCatalog */
    $columnKey = $columnKey ?? '';
    $headerSortable = $columnCatalog->isSortable($columnKey);
    $headerLabel = $columnCatalog->label($columnKey);
    $sortActiveAsc = ($displaySortColumn ?? '') === $columnKey && ($displaySortDirection ?? '') === 'asc';
    $sortActiveDesc = ($displaySortColumn ?? '') === $columnKey && ($displaySortDirection ?? '') === 'desc';
@endphp
<th
    data-column-key="{{ $columnKey }}"
    class="px-2 py-2 font-semibold text-gray-600 align-top {{ $columnKey === 'status_label' ? 'text-center' : 'text-left' }}"
>
    <div class="inline-flex items-center gap-1 max-w-[14rem]">
        <span
            data-column-drag-handle
            class="cursor-grab active:cursor-grabbing text-gray-400 hover:text-gray-600 touch-none"
            title="Glisser pour déplacer la colonne"
        >
            <i class="fas fa-grip-vertical text-xs"></i>
        </span>
        <button
            type="button"
            class="inline-flex items-center gap-1 text-left text-xs sm:text-sm hover:text-indigo-700 disabled:cursor-default disabled:hover:text-gray-600 {{ $columnKey === 'status_label' ? 'justify-center w-full' : '' }}"
            @if(! $headerSortable) disabled @endif
            @click="toggleSort(@js($columnKey))"
            title="{{ $headerSortable ? 'Trier (cliquer pour changer le sens)' : 'Tri non disponible' }}"
        >
            <span>{{ $headerLabel }}</span>
            @if($headerSortable)
                <span class="inline-flex flex-col items-center leading-none shrink-0" aria-hidden="true">
                    <i
                        class="fas fa-caret-up text-xs -mb-1 {{ $sortActiveAsc ? 'text-indigo-600' : 'text-gray-300' }}"
                        :class="sort === @js($columnKey) && dir === 'asc' ? 'text-indigo-600' : 'text-gray-300'"
                    ></i>
                    <i
                        class="fas fa-caret-down text-xs {{ $sortActiveDesc ? 'text-indigo-600' : 'text-gray-300' }}"
                        :class="sort === @js($columnKey) && dir === 'desc' ? 'text-indigo-600' : 'text-gray-300'"
                    ></i>
                </span>
            @endif
        </button>
    </div>
</th>
