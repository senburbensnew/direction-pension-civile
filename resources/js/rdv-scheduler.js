import { Calendar } from '@fullcalendar/core';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import frLocale from '@fullcalendar/core/locales/fr';

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

    const allReserved =
        readAllReserved(el);

    /*
     * Les créneaux réservés ne dépendent plus d'un agent.
     */
    let reserved =
        reservedSlots(allReserved);

    const isReserved = (start) => {
        return reserved.has(slotKey(start));
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
    };

    const setSelection = (start) => {

        if (
            isReserved(start) ||
            isPastSlot(start) ||
            isWeekend(start)
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
            `Créneau sélectionné : ${formatSelectionLabel(start)} (${slotMinutes} minutes)`
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

    const calendar = new Calendar(el, {

        plugins: [
            timeGridPlugin,
            interactionPlugin
        ],

        locale: frLocale,

        initialView: 'timeGridWeek',

        firstDay: 1,

        height: 'auto',

        contentHeight: 'auto',

        expandRows: false,

        allDaySlot: false,

        nowIndicator: true,

        weekends: false,

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

        slotMinTime:
            minTime.length === 5
                ? `${minTime}:00`
                : minTime,

        slotMaxTime:
            maxTime.length === 5
                ? `${maxTime}:00`
                : maxTime,

        slotLabelInterval:
            `00:${String(slotMinutes).padStart(2, '0')}:00`,

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
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridWeek,timeGridDay',
        },

        buttonText: {
            today: "Aujourd'hui",
            week: 'Semaine',
            day: 'Jour',
        },

        validRange: {
            start: formatDate(new Date()),
        },

        businessHours: [
            {
                daysOfWeek: [
                    1,
                    2,
                    3,
                    4,
                    5
                ],
                startTime:
                    minTime.slice(0, 5),
                endTime:
                    maxTime.slice(0, 5),
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

                !isWeekend(start) &&

                !isPastSlot(start) &&

                !isReserved(start)
            );
        },

        select: (info) => {

            calendar.unselect();

            setSelection(
                info.start
            );
        },

        dateClick: (info) => {

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

    /*
     * Plus d'écouteur agentSelect.
     */

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
                        isReserved(selected)
                    ) {

                        event.preventDefault();

                        showMessage(
                            'Ce créneau est déjà indisponible. Veuillez en choisir un autre.',
                            true
                        );

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
        !isWeekend(initial) &&
        !isReserved(initial)
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
            'Aucun créneau sélectionné.'
        );
    }
}