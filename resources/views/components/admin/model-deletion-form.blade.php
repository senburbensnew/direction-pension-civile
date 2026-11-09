@props([
    'action',
    'model' => null,
    'label' => 'Supprimer',
    'confirm' => 'Confirmer la suppression ?',
    'permanentConfirm' => 'Supprimer définitivement ? Cette action est irréversible.',
    'buttonClass' => 'px-2 py-1 bg-red-100 text-red-700 hover:bg-red-200 rounded text-xs font-medium',
    'showLabel' => false,
])

@php
    $softDeletes = $model && \App\Support\Admin\AdminModelDeletion::appliesSoftDeletes($model);
    $trashed = $softDeletes && $model->trashed();
@endphp

@if ($trashed)
    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-200 text-gray-700 mr-1" title="Archivé">
        Archivé
    </span>
    <form action="{{ $action }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <input type="hidden" name="restore" value="1">
        <button type="submit" class="{{ $buttonClass }} bg-green-100 text-green-800 hover:bg-green-200" title="Restaurer">
            <i class="fas fa-undo"></i>@if($showLabel) Restaurer @endif
        </button>
    </form>
    <form action="{{ $action }}" method="POST" class="inline"
        onsubmit="return confirm(@json($permanentConfirm))">
        @csrf
        @method('DELETE')
        <input type="hidden" name="permanent" value="1">
        <button type="submit" class="{{ $buttonClass }}" title="Supprimer définitivement">
            <i class="fas fa-trash"></i>@if($showLabel) Définitif @endif
        </button>
    </form>
@else
    <form action="{{ $action }}" method="POST" class="inline"
        x-data="{ permanent: false }"
        @submit="return permanent ? confirm(@json($permanentConfirm)) : confirm(@json($confirm))">
        @csrf
        @method('DELETE')
        @if ($softDeletes)
            <label class="sr-only">
                <input type="checkbox" name="permanent" value="1" x-model="permanent">
                Suppression définitive
            </label>
            <button type="button"
                @click="permanent = !permanent"
                :class="permanent ? 'ring-2 ring-red-500' : ''"
                class="mr-1 px-1.5 py-1 rounded text-xs text-gray-500 hover:bg-gray-100"
                title="Basculer suppression définitive">
                <i class="fas" :class="permanent ? 'fa-lock text-red-600' : 'fa-archive text-gray-500'"></i>
            </button>
        @endif
        <button type="submit" class="{{ $buttonClass }}" :title="permanent ? 'Supprimer définitivement' : '{{ $label }}'">
            <i class="fas fa-trash"></i>@if($showLabel) {{ $label }} @endif
        </button>
    </form>
@endif
