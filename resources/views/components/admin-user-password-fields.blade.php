@props([
    'passwordRequired' => false,
    'passwordLabel' => null,
    'passwordOptionalHint' => null,
    'passwordInputClass' => '',
    'confirmationInputClass' => 'w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400',
    'labelClass' => 'block text-xs font-medium text-gray-600 mb-1',
    'hintClass' => 'text-xs text-gray-400 mt-1',
    'errorClass' => 'text-red-500 text-xs mt-1',
    'clientErrorClass' => 'text-red-500 text-xs mt-1',
])

@php
    $passwordLabelText = $passwordLabel ?? __('messages.password');
    $passwordHint = __('messages.admin_user_password_hint');
    $mismatchMessage = __('messages.admin_user_password_mismatch');
    $passwordServerInvalid = $errors->has('password');
    $confirmationServerInvalid = $errors->has('password_confirmation');
@endphp

<div
    class="contents"
    x-data="{
        passwordRequired: @js((bool) $passwordRequired),
        passwordHint: @js($passwordHint),
        mismatchMessage: @js($mismatchMessage),
        passwordTouched: false,
        confirmationTouched: false,
        passwordServerInvalid: @js($passwordServerInvalid),
        confirmationServerInvalid: @js($confirmationServerInvalid),
        passwordOk() {
            const value = (this.$refs.password?.value || '').trim();
            if (value === '') {
                return ! this.passwordRequired;
            }
            return value.length >= 8 && value.length <= 72;
        },
        confirmationOk() {
            const password = (this.$refs.password?.value || '').trim();
            const confirmation = (this.$refs.confirmation?.value || '').trim();
            if (password === '') {
                return confirmation === '';
            }
            return confirmation !== '' && password === confirmation;
        },
        validatePassword() {
            const input = this.$refs.password;
            if (! input) {
                return true;
            }
            if (this.passwordOk()) {
                input.setCustomValidity('');
                return true;
            }
            if ((input.value || '').trim() === '' && this.passwordRequired) {
                input.setCustomValidity('');
                return false;
            }
            input.setCustomValidity(this.passwordHint);
            return false;
        },
        validateConfirmation() {
            const input = this.$refs.confirmation;
            if (! input) {
                return true;
            }
            if (this.confirmationOk()) {
                input.setCustomValidity('');
                return true;
            }
            input.setCustomValidity(this.mismatchMessage);
            return false;
        },
        validateAll() {
            const passwordValid = this.validatePassword();
            const confirmationValid = this.validateConfirmation();
            return passwordValid && confirmationValid;
        },
        get showPasswordClientError() {
            return this.passwordTouched && ! this.passwordOk() && ! this.passwordServerInvalid;
        },
        get showConfirmationClientError() {
            return this.confirmationTouched && ! this.confirmationOk() && ! this.confirmationServerInvalid;
        },
    }"
    x-init="
        $nextTick(() => {
            const form = $refs.password?.form;
            if (! form) {
                return;
            }
            if (! form._adminPasswordValidators) {
                form._adminPasswordValidators = [];
            }
            form._adminPasswordValidators.push(() => validateAll());
            if (form.dataset.adminPasswordAlpineSubmit === '1') {
                return;
            }
            form.dataset.adminPasswordAlpineSubmit = '1';
            form.addEventListener('submit', (event) => {
                let ok = true;
                for (const run of form._adminPasswordValidators || []) {
                    if (! run()) {
                        ok = false;
                    }
                }
                if (! ok) {
                    event.preventDefault();
                    event.stopPropagation();
                    $refs.confirmation?.reportValidity()
                        || $refs.password?.reportValidity();
                }
            }, true);
        });
    "
>
    <div>
        <label for="admin_user_password" class="{{ $labelClass }}">
            {{ $passwordLabelText }}
            @if($passwordRequired)
                <span class="text-red-500">*</span>
            @elseif(filled($passwordOptionalHint))
                <span class="text-gray-400 font-normal">{{ $passwordOptionalHint }}</span>
            @endif
        </label>
        <input
            x-ref="password"
            type="password"
            name="password"
            id="admin_user_password"
            @if($passwordRequired) required @endif
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
            @blur="passwordTouched = true; validatePassword(); validateConfirmation()"
            @input="passwordTouched = true; validatePassword(); validateConfirmation()"
            placeholder="{{ $passwordHint }}"
            class="{{ trim($passwordInputClass.' '.($passwordServerInvalid ? 'border-red-400' : '')) }}"
        >
        <p class="{{ $hintClass }}">{{ $passwordHint }}</p>
        @error('password')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
        <p
            x-show="showPasswordClientError"
            x-cloak
            class="{{ $clientErrorClass }}"
            x-text="passwordHint"
        ></p>
    </div>

    <div>
        <label for="admin_user_password_confirmation" class="{{ $labelClass }}">
            {{ __('validation.attributes.password_confirmation') }}
            @if($passwordRequired)
                <span class="text-red-500">*</span>
            @endif
        </label>
        <input
            x-ref="confirmation"
            type="password"
            name="password_confirmation"
            id="admin_user_password_confirmation"
            @if($passwordRequired) required @endif
            maxlength="72"
            autocomplete="new-password"
            @blur="confirmationTouched = true; validateConfirmation()"
            @input="confirmationTouched = true; validateConfirmation()"
            placeholder="{{ __('guest.password.confirm_label') }}"
            class="{{ trim($confirmationInputClass.' '.($confirmationServerInvalid ? 'border-red-400' : '')) }}"
        >
        @error('password_confirmation')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
        <p
            x-show="showConfirmationClientError"
            x-cloak
            class="{{ $clientErrorClass }}"
            x-text="mismatchMessage"
        ></p>
    </div>
</div>
