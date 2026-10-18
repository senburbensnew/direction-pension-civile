/** Mirrors App\Rules\Telephone */
export const TELEPHONE_REGEX = /^\+[1-9]\d{7,14}$/;

export const TELEPHONE_HTML_PATTERN = '\\+[1-9]\\d{7,14}';

const TELEPHONE_INPUT_SELECTOR =
    'input[name="telephone"], input[name="phone"], input[name$="_telephone"]';

export function normalizeTelephone(value) {
    return (value || '').replace(/\s+/g, '');
}

export function isValidTelephone(value, { required = false } = {}) {
    const normalized = normalizeTelephone(value);

    if (normalized === '') {
        return ! required;
    }

    return TELEPHONE_REGEX.test(normalized);
}

function readFormatMessage() {
    const meta = document.querySelector('meta[name="telephone-format-message"]');

    return (
        meta?.content?.trim()
        || 'Le numéro de téléphone doit être au format international avec indicatif (ex. +50938123456).'
    );
}

function bindTelephoneInput(input, formatMessage) {
    if (input.dataset.telephoneBound === '1') {
        return null;
    }

    input.dataset.telephoneBound = '1';

    const validate = () => {
        const normalized = normalizeTelephone(input.value);

        if (normalized !== input.value) {
            input.value = normalized;
        }

        const required = input.required;
        const empty = normalized === '';

        if (empty && ! required) {
            input.setCustomValidity('');

            return true;
        }

        if (isValidTelephone(normalized, { required })) {
            input.setCustomValidity('');

            return true;
        }

        input.setCustomValidity(formatMessage);

        return false;
    };

    if (! input.getAttribute('pattern')) {
        input.setAttribute('pattern', TELEPHONE_HTML_PATTERN);
    }

    if (! input.getAttribute('title')) {
        input.setAttribute('title', formatMessage);
    }

    if (! input.getAttribute('inputmode')) {
        input.setAttribute('inputmode', 'tel');
    }

    input.addEventListener('input', validate);
    input.addEventListener('blur', validate);

    return validate;
}

function bindLoginIdentifierInput(input, formatMessage) {
    if (input.dataset.telephoneBound === '1') {
        return null;
    }

    input.dataset.telephoneBound = '1';

    const validate = () => {
        const raw = (input.value || '').trim();

        if (raw === '' || ! raw.startsWith('+')) {
            input.setCustomValidity('');

            return true;
        }

        const normalized = normalizeTelephone(raw);

        if (normalized !== input.value) {
            input.value = normalized;
        }

        if (isValidTelephone(normalized, { required: true })) {
            input.setCustomValidity('');

            return true;
        }

        input.setCustomValidity(formatMessage);

        return false;
    };

    input.addEventListener('input', validate);
    input.addEventListener('blur', validate);

    return validate;
}

function bindFormSubmitValidation(form, validators) {
    if (form.dataset.telephoneSubmitBound === '1') {
        return;
    }

    form.dataset.telephoneSubmitBound = '1';

    form.addEventListener(
        'submit',
        (event) => {
            let ok = true;

            for (const validate of validators) {
                if (! validate()) {
                    ok = false;
                }
            }

            if (! ok) {
                event.preventDefault();
                event.stopPropagation();
                form.querySelector(':invalid')?.reportValidity();
            }
        },
        true,
    );
}

export function initTelephoneFields(root = document) {
    const formatMessage = readFormatMessage();
    const formValidators = new Map();

    const register = (input, validate) => {
        if (! validate || ! input.form) {
            return;
        }

        if (! formValidators.has(input.form)) {
            formValidators.set(input.form, []);
        }

        formValidators.get(input.form).push(validate);
    };

    root.querySelectorAll(TELEPHONE_INPUT_SELECTOR).forEach((input) => {
        if (
            input.type === 'hidden'
            || input.dataset.skipTelephoneValidation === '1'
            || input.closest('[data-telephone-field]')
        ) {
            return;
        }

        register(input, bindTelephoneInput(input, formatMessage));
    });

    root.querySelectorAll('input[name="login"]').forEach((input) => {
        if (input.type === 'hidden') {
            return;
        }

        register(input, bindLoginIdentifierInput(input, formatMessage));
    });

    formValidators.forEach((validators, form) => {
        bindFormSubmitValidation(form, validators);
    });
}
