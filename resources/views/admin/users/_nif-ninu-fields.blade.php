@php
    /** @var \App\Models\User|null $user */
    $user = $user ?? null;
@endphp

<div>
    <x-nif-input
        name="nif"
        :value="$user?->nif ?? ''"
        label-class="block text-xs font-medium text-gray-600 mb-1"
        hint-class="text-xs text-gray-400 mt-1"
        error-class="text-red-500 text-xs mt-1"
        client-error-class="text-red-500 text-xs mt-1"
        :input-class="$field('nif')"
    />
</div>
<div>
    <x-ninu-input
        name="ninu"
        :value="$user?->ninu ?? ''"
        label-class="block text-xs font-medium text-gray-600 mb-1"
        hint-class="text-xs text-gray-400 mt-1"
        error-class="text-red-500 text-xs mt-1"
        client-error-class="text-red-500 text-xs mt-1"
        :input-class="$field('ninu')"
    />
</div>
