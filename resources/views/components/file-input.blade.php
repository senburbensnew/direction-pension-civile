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
    $noFileLabel = __('messages.no_file_selected');
    $filesSelectedLabel = __('messages.files_selected');
    $browseLabel = __('actions.browse');
    $changeLabel = __('messages.change_file');
    $stashedLabel = app(\App\Services\Form\FormUploadStashService::class)
        ->displayNamesForRequest(request())[$errorName] ?? null;
    $keptHint = __('messages.file_kept_after_error');
@endphp

@if($label)
    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">
        {{ $label }}@if($required) <span class="text-red-500">*</span>@endif
    </label>
@endif

<div
    x-data="{
        files: [],
        stashedLabel: @js($stashedLabel),
        dragging: false,
        hasError: {{ $hasError ? 'true' : 'false' }},
        init() {
            if (this.stashedLabel) {
                this.files = [{ name: this.stashedLabel, kept: true }];
            }
        },
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
            if (this.files.length === 0) {
                return this.stashedLabel || @js($noFileLabel);
            }
            if (this.files.length === 1) return this.files[0].name;
            return this.files.length + ' ' + @js($filesSelectedLabel);
        },
        get showKeptHint() {
            return this.stashedLabel && this.files.length === 0;
        },
        get actionLabel() {
            return this.files.length ? @js($changeLabel) : @js($browseLabel);
        }
    }"
    @dragover.prevent="dragging = true"
    @dragleave.prevent="dragging = false"
    @drop.prevent="handleDrop($event)"
    class="mt-1"
>
    <label
        for="{{ $inputId }}"
        class="dpc-file-field"
        :class="{
            'is-error': hasError,
            'is-filled': files.length > 0 || stashedLabel,
            'is-dragging': dragging
        }"
    >
        <input
            x-ref="input"
            type="file"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            @change="handleChange($event)"
            {{ $attributes->except(['class']) }}
        >

        <span class="dpc-file-field-icon" aria-hidden="true">
            <i class="far fa-file"></i>
        </span>

        <span class="dpc-file-field-name" x-text="fileLabel">{{ $noFileLabel }}</span>

        <span class="dpc-file-field-action" x-text="actionLabel">{{ $browseLabel }}</span>
    </label>
</div>

@if($hint)
    <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
@endif

<p x-show="showKeptHint" x-cloak class="mt-1 text-xs text-emerald-700">
    {{ $keptHint }} <span class="font-medium" x-text="stashedLabel"></span>
</p>

@error($errorName)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror

@error($errorName . '.*')
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
