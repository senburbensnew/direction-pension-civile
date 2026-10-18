import './bootstrap';
import { initDomEnhancements } from './dom-enhancements';
import { registerAlpinePageComponents } from './boot/register-alpine-pages.js';
import { loadPageFeatures } from './boot/load-page-features.js';
import { initMenubar } from './components/menubar.js';

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';

window.Alpine = Alpine;
window.flatpickr = flatpickr;
window.French = French;

document.addEventListener('alpine:init', () => {
    Alpine.data('searchableSelect', (config) => ({
        open: false,
        query: '',
        selectedValue: String(config.selected ?? ''),
        options: config.options ?? [],
        placeholder: config.placeholder ?? '',
        searchPlaceholder: config.searchPlaceholder ?? 'Rechercher…',

        toggle() {
            this.open = ! this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.searchInput?.focus());
            } else {
                this.query = '';
            }
        },

        select(value) {
            this.selectedValue = String(value);
            this.open = false;
            this.query = '';
        },

        get filteredOptions() {
            const q = this.query.trim().toLowerCase();
            if (q === '') {
                return this.options;
            }

            return this.options.filter((option) =>
                option.label.toLowerCase().includes(q)
                || String(option.value).toLowerCase().includes(q)
            );
        },

        get selectedLabel() {
            const match = this.options.find(
                (option) => String(option.value) === String(this.selectedValue)
            );

            if (match?.label) {
                return match.label;
            }

            return this.placeholder;
        },
    }));
});

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

function resolveRdvBookingTimeInput(form, input, datasetKey) {
    if (!input) {
        return '';
    }

    const fromInput = (input.value || '').trim();
    if (fromInput !== '') {
        return fromInput;
    }

    const fromDataset = (form.dataset[datasetKey] || '').trim();
    if (fromDataset !== '') {
        input.value = fromDataset;
    }

    return fromDataset;
}

function initRdvBookingTimePickers() {
    document.querySelectorAll('[data-rdv-booking-times]').forEach((form) => {
        if (form.dataset.rdvTimesReady === '1') {
            return;
        }

        form.dataset.rdvTimesReady = '1';

        const minuteIncrement = Math.max(
            1,
            Number(form.dataset.slotMinutes || 15)
        );

        const timeConfig = {
            locale: French,
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            allowInput: true,
            minuteIncrement,
            disableMobile: true,
            maxTime: '23:45',
        };

        const startInput = form.querySelector('[data-rdv-booking-start]');
        const initialStart = resolveRdvBookingTimeInput(form, startInput, 'initialStart');

        if (startInput && !startInput._flatpickr) {
            const startPicker = flatpickr(startInput, {
                ...timeConfig,
                defaultDate: initialStart || undefined,
            });

            if (initialStart && startPicker.selectedDates.length === 0) {
                startPicker.setDate(initialStart, false);
            }
        }

        const endInput = form.querySelector('[data-rdv-booking-end]');
        const initialEnd = resolveRdvBookingTimeInput(form, endInput, 'initialEnd');
        let endPicker = null;

        if (endInput && !endInput._flatpickr) {
            const endIsMidnight = initialEnd === '24:00';

            endPicker = flatpickr(endInput, {
                ...timeConfig,
                defaultDate: endIsMidnight ? undefined : (initialEnd || undefined),
                onOpen: () => {
                    if (endInput.value === '24:00') {
                        endPicker.clear(false);
                    }
                },
                onChange: () => {
                    delete endInput.dataset.midnightEnd;
                },
            });

            if (endIsMidnight) {
                endInput.value = '24:00';
                endInput.dataset.midnightEnd = '1';
            } else if (initialEnd && endPicker.selectedDates.length === 0) {
                endPicker.setDate(initialEnd, false);
            }
        } else if (endInput?._flatpickr) {
            endPicker = endInput._flatpickr;
        }

        form.querySelectorAll('[data-rdv-set-end-midnight]').forEach((button) => {
            button.addEventListener('click', () => {
                if (!endInput) {
                    return;
                }

                endInput.dataset.midnightEnd = '1';
                endInput.value = '24:00';
                endPicker?.close();
            });
        });

        if (endInput?.value === '24:00') {
            endInput.dataset.midnightEnd = '1';
        }

        form.addEventListener('submit', () => {
            if (endInput?.dataset.midnightEnd === '1') {
                endInput.value = '24:00';
            }
        });
    });
}

// Run on initial load and after Livewire/Turbo navigations if present
function initUiWidgets() {
    initDomEnhancements();
    initDatepickers();
    initRdvBookingTimePickers();
    initMenubar();
}

async function boot() {
    await registerAlpinePageComponents();
    Alpine.start();
    initUiWidgets();
    await loadPageFeatures();
}

boot();

document.addEventListener('livewire:navigated', () => {
    initDatepickers();
    initRdvBookingTimePickers();
});



