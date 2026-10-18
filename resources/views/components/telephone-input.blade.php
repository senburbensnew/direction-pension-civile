@props([
    'name' => 'telephone',
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
    $labelText = $label ?? __('messages.phone');
    $placeholder = '+50938123456';
    $formatMessage = \App\Rules\Telephone::formatMessage();
    $hint = __('messages.format_hint_telephone');
    $htmlPattern = '\+[1-9]\d{7,14}';
    $serverInvalid = $errors->has($name);
@endphp

<div
    data-telephone-field
    x-data="{
        value: @js(old($name, $value)),
        touched: false,
        required: @js((bool) $required),
        formatMessage: @js($formatMessage),
        serverInvalid: @js($serverInvalid),
        normalize() {
            this.value = (this.value || '').replace(/\s+/g, '');
        },
        formatOk() {
            const v = (this.value || '').replace(/\s+/g, '');
            if (v === '') {
                return ! this.required;
            }
            return /^\+[1-9]\d{7,14}$/.test(v);
        },
        validateField() {
            const input = this.$refs.input;
            if (! input) {
                return true;
            }
            this.normalize();
            input.value = this.value;
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
            if (! form._telephoneAlpineValidators) {
                form._telephoneAlpineValidators = [];
            }
            form._telephoneAlpineValidators.push(() => validateField());
            if (form.dataset.telephoneAlpineSubmit === '1') {
                return;
            }
            form.dataset.telephoneAlpineSubmit = '1';
            form.addEventListener('submit', (event) => {
                let ok = true;
                for (const run of form._telephoneAlpineValidators || []) {
                    if (! run()) {
                        ok = false;
                    }
                }
                if (! ok) {
                    event.preventDefault();
                    event.stopPropagation();
                    form.querySelector('[data-skip-telephone-validation]')?.reportValidity()
                        || $refs.input?.reportValidity();
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
        type="tel"
        name="{{ $name }}"
        id="{{ $inputId }}"
        x-model="value"
        data-skip-telephone-validation="1"
        @blur="normalize(); touched = true; validateField()"
        @input="touched = true; validateField()"
        @if($required) required @endif
        pattern="{{ $htmlPattern }}"
        title="{{ $formatMessage }}"
        placeholder="{{ $placeholder }}"
        autocomplete="tel"
        inputmode="tel"
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
