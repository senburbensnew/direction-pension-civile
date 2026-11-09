@props([
    'name' => 'nif',
    'id' => null,
    'value' => '',
    'label' => null,
    'required' => false,
    'showLabel' => true,
    'showHint' => true,
    'inputClass' => 'w-full rounded-md border-gray-300 shadow-sm focus:border-navy focus:ring-navy py-2.5 text-sm',
    'labelClass' => 'block text-sm font-medium text-gray-700 mb-1',
    'hintClass' => 'mt-1 text-xs text-gray-500',
    'errorClass' => 'text-red-600 text-sm mt-1',
    'clientErrorClass' => 'text-red-600 text-sm mt-1',
])

@php
    $inputId = $id ?? $name;
    $labelText = $label ?? 'NIF';
    $placeholder = '123-456-789-0';
    $formatMessage = \App\Rules\Nif::formatMessage();
    $hint = __('messages.format_hint_nif');
    $htmlPattern = \App\Rules\Nif::HTML_PATTERN;
    $serverInvalid = $errors->has($name);
@endphp

<div
    data-nif-field
    x-data="{
        value: @js(old($name, $value)),
        touched: false,
        required: @js((bool) $required),
        formatMessage: @js($formatMessage),
        serverInvalid: @js($serverInvalid),
        formatOk() {
            const v = (this.value || '').trim();
            if (v === '') {
                return ! this.required;
            }
            return /^\d{3}-\d{3}-\d{3}-\d{1}$/.test(v);
        },
        validateField() {
            const input = this.$refs.input;
            if (! input) {
                return true;
            }
            input.value = (this.value || '').trim();
            this.value = input.value;
            if (this.formatOk()) {
                input.setCustomValidity('');
                return true;
            }
            input.setCustomValidity(this.formatMessage);
            return false;
        },
        get showClientError() {
            return this.touched && ! this.formatOk() && ! this.serverInvalid;
        },
    }"
    x-init="
        $watch('value', () => { if (touched) validateField(); });
        $nextTick(() => {
            const form = $refs.input?.form;
            if (! form) {
                return;
            }
            if (! form._nifAlpineValidators) {
                form._nifAlpineValidators = [];
            }
            form._nifAlpineValidators.push(() => validateField());
            if (form.dataset.nifAlpineSubmit === '1') {
                return;
            }
            form.dataset.nifAlpineSubmit = '1';
            form.addEventListener('submit', (event) => {
                let ok = true;
                for (const run of form._nifAlpineValidators || []) {
                    if (! run()) {
                        ok = false;
                    }
                }
                if (! ok) {
                    event.preventDefault();
                    event.stopPropagation();
                    $refs.input?.reportValidity();
                }
            }, true);
        });
    "
>
    @if($showLabel)
        <label for="{{ $inputId }}" class="{{ $labelClass }}">
            {{ $labelText }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        x-ref="input"
        type="text"
        name="{{ $name }}"
        id="{{ $inputId }}"
        x-model="value"
        @blur="touched = true; validateField()"
        @input="touched = true; validateField()"
        @if($required) required @endif
        pattern="{{ $htmlPattern }}"
        title="{{ $formatMessage }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        inputmode="text"
        {{ $attributes->merge(['class' => trim($inputClass.' '.($serverInvalid ? 'border-red-400 focus:ring-red-400' : ''))]) }}
    >

    @if($showHint)
        <p class="{{ $hintClass }}">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="{{ $errorClass }}">{{ $message }}</p>
    @enderror

    <p
        x-show="showClientError"
        x-cloak
        class="{{ $clientErrorClass }}"
        x-text="formatMessage"
    ></p>
</div>
