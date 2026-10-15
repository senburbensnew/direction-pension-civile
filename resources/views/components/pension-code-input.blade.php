@props([
    'name' => 'code_pension',
    'id' => null,
    'value' => '',
    'label' => null,
    'required' => true,
    'showLabel' => true,
    'showHint' => true,
    'inputClass' => 'w-full rounded-none border-gray-300 shadow-sm focus:border-navy focus:ring-navy py-2.5 text-sm',
])

@php
    $inputId = $id ?? $name;
    $labelText = $label ?? __('guest.account_request.pension_code_label');
    $placeholder = __('guest.account_request.pension_code_placeholder');
    $formatMessage = \App\Rules\CodePension::formatMessage();
    $hint = __('messages.format_hint_pension_code');
    $htmlPattern = '7-[A-Za-z0-9]{5}|8-[0-9]{5}';
    $serverInvalid = $errors->has($name);
@endphp

<div
    x-data="{
        value: @js(old($name, $value)),
        touched: false,
        required: @js((bool) $required),
        formatMessage: @js($formatMessage),
        serverInvalid: @js($serverInvalid),
        normalize() {
            this.value = (this.value || '').trim().toUpperCase();
        },
        formatOk() {
            const v = (this.value || '').trim();
            if (v === '') {
                return ! this.required;
            }
            return /^(7-[A-Z0-9]{5}|8-\d{5})$/i.test(v);
        },
        validateField() {
            const input = this.$refs.input;
            if (! input) {
                return true;
            }
            this.normalize();
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
    x-init="$watch('value', () => { if (touched) validateField(); })"
>
    @if($showLabel)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700 mb-1">
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
        @blur="normalize(); touched = true; validateField()"
        @input="touched = true; validateField()"
        @if($required) required @endif
        pattern="{{ $htmlPattern }}"
        title="{{ $formatMessage }}"
        placeholder="{{ $placeholder }}"
        autocomplete="off"
        spellcheck="false"
        inputmode="text"
        {{ $attributes->merge(['class' => trim($inputClass.' '.($serverInvalid ? 'border-red-400 focus:ring-red-400' : ''))]) }}
    >

    @if($showHint)
        <p class="mt-1 text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
    @enderror

    <p
        x-show="showClientError"
        x-cloak
        class="text-red-600 text-sm mt-1"
        x-text="formatMessage"
    ></p>
</div>
