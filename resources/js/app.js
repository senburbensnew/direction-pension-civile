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

        if (startInput && !startInput._flatpickr) {
            flatpickr(startInput, timeConfig);
        }

        const endInput = form.querySelector('[data-rdv-booking-end]');
        let endPicker = null;

        if (endInput && !endInput._flatpickr) {
            endPicker = flatpickr(endInput, {
                ...timeConfig,
                onOpen: () => {
                    if (endInput.value === '24:00') {
                        endPicker.clear(false);
                    }
                },
                onChange: () => {
                    delete endInput.dataset.midnightEnd;
                },
            });
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
document.addEventListener('DOMContentLoaded', initDatepickers);
document.addEventListener('DOMContentLoaded', initRdvBookingTimePickers);
document.addEventListener('livewire:navigated', initDatepickers);
document.addEventListener('livewire:navigated', initRdvBookingTimePickers);

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('dpc-chronogramme')) {
        import('./chronogramme.js').then(({ initDpcChronogramme }) => initDpcChronogramme());
    }
    if (document.getElementById('dpc-rdv-scheduler')) {
        import('./rdv-scheduler.js').then(({ initRdvScheduler }) => initRdvScheduler());
    }
    if (document.getElementById('dpc-agent-availability-calendar')) {
        import('./rdv-agent-availability.js').then(({ initAgentAvailabilityCalendar }) =>
            initAgentAvailabilityCalendar()
        );
    }
});



