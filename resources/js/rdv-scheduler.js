import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import frLocale from '@fullcalendar/core/locales/fr';

const frLocaleSundayFirst = {
    ...frLocale,
    week: {
        ...frLocale.week,
        dow: 0,
    },
};

function pad(value) {
    return String(value).padStart(2, '0');
}

function formatDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function formatTime(date) {
    return `${pad(date.getHours())}:${pad(date.getMinutes())}`;
}

function syncHiddenField(input, value) {
    if (!input) {
        return;
    }

    input.value = value;
    input.dispatchEvent(new Event('input', { bubbles: true }));
}

function parseSlot(dateStr, timeStr) {
    if (!dateStr || !timeStr) {
        return null;
    }

    const [year, month, day] = dateStr.split('-').map(Number);
    const [hour, minute] = timeStr.split(':').map(Number);

    if (
        !year ||
        !month ||
        !day ||
        Number.isNaN(hour) ||
        Number.isNaN(minute)
    ) {
        return null;
    }

    return new Date(
        year,
        month - 1,
        day,
        hour,
        minute,
        0,
        0
    );
}

function isPastSlot(start) {
    return start.getTime() < Date.now();
}

function isWeekend(date) {
    const day = date.getDay();

    return day === 0 || day === 6;
}

function isBlockedWeekend(date, allowWeekends) {
    return !allowWeekends && isWeekend(date);
}

function startOfLocalDay(date = new Date()) {
    return new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
        0,
        0,
        0,
        0
    );
}

function readHolidayDates(el) {
    try {
        const raw = JSON.parse(el.dataset.holidays || '[]');

        if (!Array.isArray(raw)) {
            return new Map();
        }

        const map = new Map();

        raw.forEach((item) => {
            if (typeof item === 'string') {
                map.set(item, 'Jour férié');
            } else if (item?.date) {
                map.set(item.date, item.name || 'Jour férié');
            }
        });

        return map;
    } catch {
        return new Map();
    }
}

function readReservedByService(el) {
    try {
        const raw = JSON.parse(
            el.dataset.reservedByService || '{}'
        );

        return typeof raw === 'object' && raw !== null ? raw : {};
    } catch {
        return {};
    }
}

function slotKey(start) {
    return `${formatDate(start)}|${formatTime(start)}`;
}

/*
 * Lit data-reserved et normalise TOUJOURS vers un tableau
 * d'objets { date, time }.
 *
 * Accepte trois formats (pour compatibilité ascendante) :
 *
 *   1. "2026-09-24 14:00"                        (backend actuel)
 *   2. { date: "2026-09-24", time: "14:00" }     (format objet)
 *   3. { slot: "2026-09-24 14:00", ... }         (format enrichi)
 *
 * Les créneaux retournés correspondent UNIQUEMENT aux créneaux
 * totalement saturés (tous les agents occupés).
 */
function readAllReserved(el) {
    try {
        const raw = JSON.parse(el.dataset.reserved || '[]');

        if (!Array.isArray(raw)) {
            return [];
        }

        return raw
            .map(item => {
                if (typeof item === 'string') {
                    const [date, time] = item.split(' ');

                    return {
                        date,
                        time: time ? time.slice(0, 5) : null,
                    };
                }

                if (item && item.date && item.time) {
                    return {
                        date: item.date,
                        time: String(item.time).slice(0, 5),
                    };
                }

                if (item && item.slot) {
                    const [date, time] = String(item.slot).split(' ');

                    return {
                        date,
                        time: time ? time.slice(0, 5) : null,
                    };
                }

                return null;
            })
            .filter(
                slot => slot && slot.date && slot.time
            );
    } catch {
        return [];
    }
}

/*
 * IMPORTANT :
 * Il n'y a plus de sélection d'agent.
 *
 * Les créneaux réservés sont donc identifiés uniquement
 * par leur date et leur heure.
 */
function reservedSlots(slots) {
    return new Set(
        slots
            .filter(
                slot =>
                    slot?.date &&
                    slot?.time
            )
            .map(
                slot => `${slot.date}|${slot.time}`
            )
    );
}

function formatSelectionLabel(start) {
    return new Intl.DateTimeFormat('fr-FR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false,
    }).format(start);
}

export function initRdvScheduler() {

    const el = document.getElementById('dpc-rdv-scheduler');

    if (!el || el.dataset.ready === '1') {
        return;
    }

    const dateInput =
        document.getElementById('date_souhaitee');

    const timeInput =
        document.getElementById('heure_souhaitee');

    const selectionEl =
        document.getElementById('dpc-rdv-selection');

    const slotMinutes =
        Number(el.dataset.slotMinutes || 15);

    const minTime =
        el.dataset.minTime || '14:00:00';

    const maxTime =
        el.dataset.maxTime || '16:00:00';

    const displayMinTime =
        el.dataset.displayMinTime || '08:00:00';

    const displayMaxTime =
        el.dataset.displayMaxTime || '16:00:00';

    const toSlotTime = (value) =>
        value.length === 5 ? `${value}:00` : value;

    const reservedByService =
        readReservedByService(el);

    const holidays =
        readHolidayDates(el);

    const allowWeekends =
        el.dataset.allowWeekends === '1';

    const bookingDaysLabel =
        el.dataset.bookingDaysLabel || 'du lundi au vendredi';

    const bookingHoursLabel =
        el.dataset.bookingHoursLabel || null;

    let activeServiceCode =
        el.dataset.defaultService || '';

    const slotsForService = (code) => {
        const list = reservedByService[code] || [];

        if (Array.isArray(list) && list.length && typeof list[0] === 'string') {
            return readAllReserved({
                dataset: {
                    reserved: JSON.stringify(list),
                },
            });
        }

        return list;
    };

    let allReserved = slotsForService(activeServiceCode);

    let reserved = reservedSlots(allReserved);

    const isHoliday = (start) => {
        return holidays.has(formatDate(start));
    };

    const isReserved = (start) => {
        return reserved.has(slotKey(start));
    };

    const timeToMinutes = (value) => {
        const [hour, minute] = value.slice(0, 5).split(':').map(Number);

        return hour * 60 + minute;
    };

    const isWithinBookingWindow = (start) => {
        const slotStart = start.getHours() * 60 + start.getMinutes();
        const slotEnd = slotStart + slotMinutes;
        const windowStart = timeToMinutes(minTime);
        const windowEnd = maxTime.startsWith('24:')
            ? 24 * 60
            : timeToMinutes(maxTime);

        return slotStart >= windowStart && slotEnd <= windowEnd;
    };

    const rdvWindowLabel = () => {
        const start = minTime.slice(0, 5);
        const end = maxTime.slice(0, 5);

        return `${start}–${end}`;
    };

    const bookingWindowMessage = () => {
        const hoursPart = bookingHoursLabel
            || `${rdvWindowLabel()} (créneaux de ${slotMinutes} minutes)`;

        return `Plage réservable : ${bookingDaysLabel}, ${hoursPart}.`;
    };

    const showMessage = (text, isError = false) => {

        if (!selectionEl) {
            return;
        }

        selectionEl.textContent = text;

        selectionEl.classList.toggle(
            'text-red-600',
            isError
        );

        selectionEl.classList.toggle(
            'text-navy',
            !isError
        );

        if (isError) {
            selectionEl.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest',
            });
        }
    };

    const setSelection = (start) => {

        if (isHoliday(start)) {
            showMessage(
                `Jour férié en Haïti (${holidays.get(formatDate(start))}). Choisissez une autre date.`,
                true
            );

            return;
        }

        if (isBlockedWeekend(start, allowWeekends) || !isWithinBookingWindow(start)) {
            showMessage(
                bookingWindowMessage(),
                true
            );

            return;
        }

        if (
            isReserved(start) ||
            isPastSlot(start)
        ) {
            showMessage(
                'Ce créneau est déjà indisponible. Veuillez en choisir un autre.',
                true
            );

            return;
        }

        const date = formatDate(start);
        const time = formatTime(start);

        syncHiddenField(
            dateInput,
            date
        );

        syncHiddenField(
            timeInput,
            time
        );

        showMessage(
            `Créneau enregistré (${slotMinutes} min). Vérifiez le récapitulatif avant d’envoyer.`
        );

        window.dispatchEvent(
            new CustomEvent('rdv-slot-selected', {
                detail: { date, time },
            })
        );

        calendar
            .getEventById('selection')
            ?.remove();

        calendar.addEvent({
            id: 'selection',
            title: 'Votre créneau',
            start,
            end: new Date(
                start.getTime() +
                slotMinutes * 60 * 1000
            ),
            classNames: ['dpc-rdv-selection'],
        });
    };

    const reservedEvents = () => {

        return [...reserved]
            .map((key, index) => {

                const [date, time] =
                    key.split('|');

                const start =
                    parseSlot(date, time);

                if (!start) {
                    return null;
                }

                return {
                    id: `reserved-${index}`,
                    title: 'Indisponible',
                    start,
                    end: new Date(
                        start.getTime() +
                        slotMinutes * 60 * 1000
                    ),
                    classNames: [
                        'dpc-rdv-reserved'
                    ],
                    display: 'auto',
                    overlap: false,
                    editable: false,
                    extendedProps: {
                        reserved: true,
                    },
                };
            })
            .filter(Boolean);
    };

    const refreshReserved = () => {

        reserved =
            reservedSlots(allReserved);

        calendar
            .getEvents()
            .filter(
                event => event.id !== 'selection'
            )
            .forEach(
                event => event.remove()
            );

        reservedEvents().forEach(
            event => calendar.addEvent(event)
        );

        if (
            dateInput?.value &&
            timeInput?.value
        ) {

            const selected =
                parseSlot(
                    dateInput.value,
                    timeInput.value
                );

            if (
                selected &&
                isReserved(selected)
            ) {

                calendar
                    .getEventById('selection')
                    ?.remove();

                syncHiddenField(
                    dateInput,
                    ''
                );

                syncHiddenField(
                    timeInput,
                    ''
                );

                showMessage(
                    'Ce créneau n’est plus disponible. Veuillez en choisir un autre.',
                    true
                );

                return;
            }
        }

        if (
            !dateInput?.value ||
            !timeInput?.value
        ) {
            showMessage(
                'Aucun créneau sélectionné.'
            );
        }
    };

    const goToToday = () => {
        calendar.gotoDate(startOfLocalDay(new Date()));
    };

    const explainUnselectableSlot = (start) => {
        if (isHoliday(start)) {
            showMessage(
                `Jour férié en Haïti (${holidays.get(formatDate(start))}). Choisissez une autre date.`,
                true
            );

            return;
        }

        if (isBlockedWeekend(start, allowWeekends)) {
            showMessage(
                'Les créneaux ne sont pas réservables le week-end.',
                true
            );

            return;
        }

        if (!isWithinBookingWindow(start)) {
            showMessage(
                bookingWindowMessage(),
                true
            );

            return;
        }

        if (isPastSlot(start)) {
            showMessage(
                'Cette heure est déjà passée. Choisissez un autre créneau ou un jour ultérieur.',
                true
            );

            return;
        }

        showMessage(
            bookingWindowMessage(),
            true
        );
    };

    const calendar = new Calendar(el, {

        plugins: [
            dayGridPlugin,
            timeGridPlugin,
            interactionPlugin
        ],

        locale: frLocaleSundayFirst,

        initialView: 'timeGridWeek',

        firstDay: 0,

        height: 480,

        expandRows: true,

        allDaySlot: false,

        nowIndicator: true,

        weekends: allowWeekends,

        scrollTime: toSlotTime(displayMinTime),

        scrollTimeReset: false,

        selectable: true,

        selectMirror: true,

        unselectAuto: false,

        selectMinDistance: 0,

        selectOverlap: false,

        eventOverlap: false,

        slotDuration:
            `00:${String(slotMinutes).padStart(2, '0')}:00`,

        snapDuration:
            `00:${String(slotMinutes).padStart(2, '0')}:00`,

        slotMinTime: toSlotTime(displayMinTime),

        slotMaxTime: toSlotTime(displayMaxTime),

        slotLabelInterval: '01:00:00',

        slotLabelFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        },

        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false,
        },

        headerToolbar: {
            left: 'prev,next dpcToday',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,dayGridMonth',
        },

        customButtons: {
            dpcToday: {
                text: "Aujourd'hui",
                click: goToToday,
            },
        },

        buttonText: {
            today: "Aujourd'hui",
            week: 'Semaine',
            day: 'Jour',
            month: 'Mois',
        },

        views: {
            dayGridMonth: {
                dayMaxEvents: 3,
                moreLinkClick: 'day',
            },
        },

        validRange: () => ({
            start: startOfLocalDay(new Date()),
        }),

        businessHours: [
            {
                daysOfWeek: allowWeekends
                    ? [0, 1, 2, 3, 4, 5, 6]
                    : [1, 2, 3, 4, 5],
                startTime: minTime.slice(0, 5),
                endTime: maxTime.startsWith('24:')
                    ? '24:00'
                    : maxTime.slice(0, 5),
            },
        ],

        selectConstraint: 'businessHours',

        /*
         * IMPORTANT :
         * Plus aucune condition sur agentId.
         */
        selectAllow: (selection) => {

            const start =
                selection.start;

            const duration =
                selection.end.getTime() -
                start.getTime();

            return (
                duration ===
                    slotMinutes * 60 * 1000 &&

                !isBlockedWeekend(start, allowWeekends) &&

                !isHoliday(start) &&

                !isPastSlot(start) &&

                !isReserved(start) &&

                isWithinBookingWindow(start)
            );
        },

        select: (info) => {

            calendar.unselect();

            setSelection(
                info.start
            );
        },

        dateClick: (info) => {

            if (info.view.type === 'dayGridMonth') {
                calendar.changeView('timeGridDay', info.date);

                return;
            }

            const start =
                info.date;

            if (isReserved(start)) {

                showMessage(
                    'Ce créneau est déjà indisponible. Veuillez en choisir un autre.',
                    true
                );

                return;
            }

            const end =
                new Date(
                    start.getTime() +
                    slotMinutes * 60 * 1000
                );

            const allow =
                calendar.getOption(
                    'selectAllow'
                );

            if (
                typeof allow === 'function' &&
                !allow({
                    start,
                    end
                })
            ) {
                explainUnselectableSlot(start);

                return;
            }

            setSelection(start);
        },

        eventClick: (info) => {

            info.jsEvent.preventDefault();

            if (
                info.event.id === 'selection'
            ) {
                return;
            }

            if (
                info.event.extendedProps.reserved
            ) {

                showMessage(
                    'Ce créneau est déjà indisponible. Veuillez en choisir un autre.',
                    true
                );
            }
        },

        events: reservedEvents(),
    });

    calendar.render();

    requestAnimationFrame(() => {
        calendar.updateSize();
    });

    el.dataset.ready = '1';

    window.addEventListener('rdv-service-changed', (event) => {
        const code = event.detail?.serviceCode || '';

        if (code === activeServiceCode) {
            return;
        }

        activeServiceCode = code;
        allReserved = slotsForService(activeServiceCode);
        refreshReserved();
    });

    const form =
        el.closest('form');

    if (form) {

        form.addEventListener(
            'submit',
            (event) => {

                /*
                 * L'agent n'est plus obligatoire.
                 *
                 * On vérifie uniquement le créneau.
                 */

                if (
                    dateInput?.value &&
                    timeInput?.value
                ) {

                    const selected =
                        parseSlot(
                            dateInput.value,
                            timeInput.value
                        );

                    if (
                        selected &&
                        (
                            isReserved(selected) ||
                            !isWithinBookingWindow(selected) ||
                            isBlockedWeekend(selected, allowWeekends) ||
                            isHoliday(selected)
                        )
                    ) {

                        event.preventDefault();

                        if (isReserved(selected)) {
                            showMessage(
                                'Ce créneau est déjà indisponible. Veuillez en choisir un autre.',
                                true
                            );
                        } else {
                            explainUnselectableSlot(selected);
                        }

                        el.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });

                        return;
                    }

                    return;
                }

                event.preventDefault();

                showMessage(
                    'Veuillez sélectionner un créneau dans l’agenda.',
                    true
                );

                el.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        );
    }

    const initial =
        parseSlot(
            el.dataset.date ||
                dateInput?.value,

            el.dataset.time ||
                timeInput?.value
        );

    if (
        initial &&
        !isPastSlot(initial) &&
        !isBlockedWeekend(initial, allowWeekends) &&
        !isHoliday(initial) &&
        !isReserved(initial) &&
        isWithinBookingWindow(initial)
    ) {

        calendar.gotoDate(initial);

        setSelection(initial);

    } else if (
        initial &&
        isReserved(initial)
    ) {

        showMessage(
            'Ce créneau n’est plus disponible. Veuillez en choisir un autre.',
            true
        );

        syncHiddenField(
            dateInput,
            ''
        );

        syncHiddenField(
            timeInput,
            ''
        );

    } else {

        showMessage(
            'Choisissez un jour à venir, puis un créneau dans la zone blanche (plage horaire). '
            + 'Les zones grisées ou déjà passées ne sont pas sélectionnables.'
        );
    }
}