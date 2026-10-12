@props([
    'name',
    'id' => null,
    'accept' => null,
    'multiple' => false,
    'required' => false,
    'label' => null,
    'hint' => null,
    'onChange' => null,
])

@php
    $inputId = $id ?? str_replace('[]', '', $name);
    $errorName = str_replace('[]', '', $name);
    $hasError = $errors->has($errorName) || $errors->has($errorName . '.*');
    $noFileLabel = __('messages.no_file_selected');
    $filesSelectedLabel = __('messages.files_selected');
    $chooseLabel = $multiple ? __('messages.choose_files') : __('messages.choose_file');
    $stashedLabel = app(\App\Services\Form\FormUploadStashService::class)
        ->displayNamesForRequest(request())[$errorName] ?? null;
    $keptHint = __('messages.file_kept_after_error');
    $wrapperClass = trim('mt-1 block w-full text-sm ' . ($hasError ? 'text-red-600' : 'text-gray-600') . ' ' . ($attributes->get('class') ?? ''));
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
        hasError: {{ $hasError ? 'true' : 'false' }},
        init() {
            if (this.stashedLabel) {
                this.files = [{ name: this.stashedLabel, kept: true }];
            }
        },
        handleChange(e) {
            this.files = Array.from(e.target.files);
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
        }
    }"
    class="{{ $wrapperClass }}"
>
    <label
        for="{{ $inputId }}"
        class="group flex w-full min-w-0 cursor-pointer items-center"
    >
        <input
            x-ref="input"
            type="file"
            name="{{ $name }}"
            id="{{ $inputId }}"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            @if($required) required @endif
            @change="handleChange($event)@if($onChange); {{ $onChange }}@endif"
            class="sr-only"
            {{ $attributes->except(['class']) }}
        >

        <span
            class="mr-3 shrink-0 border-0 bg-navy py-2 px-4 text-sm font-semibold text-white transition-colors group-hover:bg-orange-500"
        >{{ $chooseLabel }}</span>

        <span class="min-w-0 flex-1 truncate" x-text="fileLabel">{{ $noFileLabel }}</span>
    </label>

    <p x-show="showKeptHint" x-cloak class="mt-1 text-xs text-emerald-700">
        {{ $keptHint }} <span class="font-medium" x-text="stashedLabel"></span>
    </p>
</div>

@if($hint)
    <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
@endif

@error($errorName)
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror

@error($errorName . '.*')
    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
@enderror
