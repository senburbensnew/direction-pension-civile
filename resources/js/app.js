import './bootstrap';

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

window.Alpine = Alpine;
window.flatpickr = flatpickr;
window.French = French;

Alpine.start();

// Locale → { flatpickr locale, display format shown to user }
// The hidden input always submits Y-m-d (what Laravel expects).
const LOCALE_CONFIG = {
    fr: { locale: French,  altFormat: 'd/m/Y' },
    ht: { locale: French,  altFormat: 'd/m/Y' }, // Haitian Creole uses the same format
    en: { locale: 'default', altFormat: 'm/d/Y' },
};

function initDatepickers() {
    const lang   = document.documentElement.lang || 'fr';
    const config = LOCALE_CONFIG[lang] ?? LOCALE_CONFIG['fr'];

    flatpickr('input[type="date"]:not([data-no-picker])', {
        locale:     config.locale,
        dateFormat: 'Y-m-d',
        altInput:   true,
        altFormat:  config.altFormat,
        allowInput: true,
        disableMobile: false,
    });
}

// Run on initial load and after Livewire/Turbo navigations if present
document.addEventListener('DOMContentLoaded', initDatepickers);
document.addEventListener('livewire:navigated', initDatepickers);


