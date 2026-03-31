@props([
    'name',
    'id' => null,
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'label' => null,
    'hint' => null,
])

@php
    $inputId = $id ?? str_replace('[]', '', $name);
    $errorName = str_replace('[]', '', $name);
    $hasError = $errors->has($errorName) || $errors->has($errorName . '.*');
    $chooseLabel = $multiple ? __('messages.choose_files') : __('messages.choose_file');
    $noFileLabel = __('messages.no_file_selected');
    $filesSelectedLabel = __('messages.files_selected');
    $dropLabel = __('messages.drop_file_here');
@endphp

@if($label)
    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}@if($required) <span class="text-red-500">*</span>@endif
    </label>
@endif

<div
    x-data="{
        files: [],
        dragging: false,
        hasError: {{ $hasError ? 'true' : 'false' }},
        handleChange(e) {
            this.files = Array.from(e.target.files);
        },
        handleDrop(e) {
            this.dragging = false;
            const dt = e.dataTransfer;
            if (dt.files.length) {
                this.$refs.input.files = dt.files;
                this.files = Array.from(dt.files);
            }
        },
        get fileLabel() {
            if (this.files.length === 0) return '{{ $noFileLabel }}';
            if (this.files.length === 1) return this.files[0].name;
            return this.files.length + ' {{ $filesSelectedLabel }}';
        }
    }"
    @dragover.prevent="dragging = true"
    @dragleave.prevent="dragging = false"
    @drop.prevent="handleDrop($event)"
    :class="dragging
        ? 'border-blue-400 bg-blue-50'
        : (hasError ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white hover:bg-gray-50')"
    class="relative border-2 border-dashed rounded-lg transition-colors"
>
    <label for="{{ $inputId }}" class="flex items-center gap-4 p-4 cursor-pointer">
        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center">
            <svg class="w-5 h-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13" />
            </svg>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-blue-600">{{ $chooseLabel }}</p>
            <template x-if="files.length <= 1">
                <p x-text="fileLabel" class="text-xs text-gray-500 truncate mt-0.5">{{ $noFileLabel }}</p>
            </template>
            <template x-if="files.length > 1">
                <ul class="mt-1 space-y-0.5">
                    <template x-for="file in files" :key="file.name">
                        <li class="flex items-center gap-1 text-xs text-gray-600">
                            <svg class="w-3 h-3 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                            <span x-text="file.name" class="truncate"></span>
                        </li>
                    </template>
                </ul>
            </template>
            <p class="text-xs text-gray-400 mt-0.5">{{ $dropLabel }}</p>
        </div>
        <input
            x-ref="input"
            type="file"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            class="sr-only"
            @change="handleChange($event)"
            {{ $attributes->except(['class']) }}
        >
    </label>
</div>

@if($hint)
    <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
@endif

@error($errorName)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror

@error($errorName . '.*')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
