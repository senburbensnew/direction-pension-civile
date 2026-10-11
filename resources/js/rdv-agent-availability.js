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

function parseTimeToMinutes(value) {
    const [hour, minute] = String(value).slice(0, 5).split(':').map(Number);

    return hour * 60 + minute;
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

function isWeekend(date) {
    const day = date.getDay();

    return day === 0 || day === 6;
}

function isBlockedWeekend(date, allowWeekends) {
    return !allowWeekends && isWeekend(date);
}

function readHolidays(el) {
    try {
        const raw = JSON.parse(el.dataset.holidays || '[]');
        const map = new Map();

        if (!Array.isArray(raw)) {
            return map;
        }

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

function isoWeekday(date) {
    const day = date.getDay();

    return day === 0 ? 7 : day;
}

function addDays(date, days) {
    const copy = new Date(date.getTime());

    copy.setDate(copy.getDate() + days);

    return copy;
}

function buildSlotDate(dateStr, timeStr) {
    const [year, month, day] = dateStr.split('-').map(Number);
    const [hour, minute] = timeStr.slice(0, 5).split(':').map(Number);

    return new Date(year, month - 1, day, hour, minute, 0, 0);
}

function expandRecurringEvents(recurring, rangeStart, rangeEnd) {
    const events = [];

    recurring.forEach((row, index) => {
        if (!row.start_time || !row.end_time) {
            return;
        }

        for (let cursor = new Date(rangeStart); cursor <= rangeEnd; cursor = addDays(cursor, 1)) {
            if (isoWeekday(cursor) !== Number(row.weekday)) {
                continue;
            }

            const dateStr = formatDate(cursor);
            const start = buildSlotDate(dateStr, row.start_time);
            const end = buildSlotDate(dateStr, row.end_time);

            if (end <= start) {
                continue;
            }

            events.push({
                id: `recurring-${index}-${dateStr}`,
                title: 'Récurrent',
                start,
                end,
                classNames: ['dpc-avail-recurring'],
                extendedProps: {
                    kind: 'recurring',
                    index,
                },
            });
        }
    });

    return events;
}

function buildEventsFromState(state, rangeStart, rangeEnd) {
    const events = [];

    expandRecurringEvents(state.recurring || [], rangeStart, rangeEnd).forEach((event) => {
        events.push(event);
    });

    (state.dates || []).forEach((row, index) => {
        if (!row.available_on || !row.start_time || !row.end_time) {
            return;
        }

        const start = buildSlotDate(row.available_on, row.start_time);
        const end = buildSlotDate(row.available_on, row.end_time);

        if (end <= start) {
            return;
        }

        events.push({
            id: `date-${index}`,
            title: 'Date précise',
            start,
            end,
            classNames: ['dpc-avail-date'],
            extendedProps: {
                kind: 'date',
                index,
            },
        });
    });

    (state.blocks || []).forEach((row, index) => {
        const startTime = row.all_day ? '00:00' : row.start_time;
        const endTime = row.all_day ? '23:59' : row.end_time;

        if (!startTime || !endTime) {
            return;
        }

        if (row.mode === 'weekday' || (!row.available_on && row.weekday)) {
            for (let cursor = new Date(rangeStart); cursor <= rangeEnd; cursor = addDays(cursor, 1)) {
                if (isoWeekday(cursor) !== Number(row.weekday)) {
                    continue;
                }

                const dateStr = formatDate(cursor);
                const start = buildSlotDate(dateStr, startTime);
                const end = buildSlotDate(dateStr, endTime);

                events.push({
                    id: `block-${index}-${dateStr}`,
                    title: 'Bloqué (récurrent)',
                    start,
                    end,
                    classNames: ['dpc-avail-block-weekday'],
                    extendedProps: {
                        kind: 'block',
                        index,
                    },
                });
            }

            return;
        }

        if (!row.available_on) {
            return;
        }

        const start = buildSlotDate(row.available_on, startTime);
        const end = buildSlotDate(row.available_on, endTime);

        events.push({
            id: `block-${index}`,
            title: 'Bloqué (date)',
            start,
            end,
            classNames: ['dpc-avail-block-date'],
            extendedProps: {
                kind: 'block',
                index,
            },
        });
    });

    return events;
}

const PAINT_MODE_OPTIONS = [
    { id: 'date', label: 'Disponible — date précise', short: 'Date précise' },
    { id: 'recurring', label: 'Disponible — récurrent', short: 'Récurrent' },
    { id: 'block_date', label: 'Indisponible — cette date', short: 'Bloquer date' },
    { id: 'block_weekday', label: 'Indisponible — chaque semaine', short: 'Bloquer jour' },
];

function ensureContextMenu() {
    let menu = document.getElementById('dpc-agent-avail-context-menu');

    if (!menu) {
        menu = document.createElement('div');
        menu.id = 'dpc-agent-avail-context-menu';
        menu.className = 'dpc-agent-avail-context-menu';
        menu.setAttribute('role', 'menu');
        document.body.appendChild(menu);

        document.addEventListener('click', () => hideContextMenu());
        document.addEventListener('scroll', () => hideContextMenu(), true);
        window.addEventListener('resize', () => hideContextMenu());
    }

    return menu;
}

function hideContextMenu() {
    const menu = document.getElementById('dpc-agent-avail-context-menu');

    if (menu) {
        menu.hidden = true;
        menu.innerHTML = '';
    }
}

function showContextMenu(clientX, clientY, items) {
    const menu = ensureContextMenu();

    menu.innerHTML = '';
    items.forEach((item) => {
        if (item.separator) {
            const hr = document.createElement('div');
            hr.className = 'dpc-agent-avail-context-menu__sep';
            menu.appendChild(hr);

            return;
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = `dpc-agent-avail-context-menu__item dpc-agent-avail-context-menu__item--${item.mode || 'default'}`;
        btn.textContent = item.label;
        btn.setAttribute('role', 'menuitem');
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            hideContextMenu();
            item.action();
        });
        menu.appendChild(btn);
    });

    menu.hidden = false;

    const padding = 8;
    const rect = menu.getBoundingClientRect();
    let left = clientX;
    let top = clientY;

    if (left + rect.width > window.innerWidth - padding) {
        left = window.innerWidth - rect.width - padding;
    }

    if (top + rect.height > window.innerHeight - padding) {
        top = window.innerHeight - rect.height - padding;
    }

    menu.style.left = `${Math.max(padding, left)}px`;
    menu.style.top = `${Math.max(padding, top)}px`;
}

function slotEndFromStart(start, slotMinutes) {
    return new Date(start.getTime() + slotMinutes * 60 * 1000);
}

export function initAgentAvailabilityCalendar() {
    const el = document.getElementById('dpc-agent-availability-calendar');

    if (!el || el.dataset.ready === '1') {
        return;
    }

    const alpine = () => window.__dpcAgentAvailability;

    if (!alpine()) {
        document.addEventListener(
            'dpc-agent-availability-ready',
            () => initAgentAvailabilityCalendar(),
            { once: true }
        );

        return;
    }

    const slotMinutes = Number(el.dataset.slotMinutes || 15);
    const minTime = el.dataset.minTime || '14:00:00';
    const maxTime = el.dataset.maxTime || '16:00:00';
    const allowWeekends = el.dataset.allowWeekends === '1';
    const displayMinTime = el.dataset.displayMinTime || '08:00:00';
    const displayMaxTime = el.dataset.displayMaxTime || '18:00:00';
    const maxDateStr = el.dataset.maxDate || null;
    const holidays = readHolidays(el);

    const bookingMinTime = minTime;
    const bookingMaxTime = maxTime === '24:00' || maxTime === '24:00:00'
        ? '24:00:00'
        : maxTime;

    const toSlotTime = (value) =>
        (value.length === 5 ? `${value}:00` : value);

    const isHoliday = (start) => holidays.has(formatDate(start));

    const isWithinWindow = (start, end) => {
        const startMin = start.getHours() * 60 + start.getMinutes();
        const endMin = end.getHours() * 60 + end.getMinutes();
        const windowStart = parseTimeToMinutes(bookingMinTime);
        const windowEnd = parseTimeToMinutes(bookingMaxTime);

        return startMin >= windowStart && endMin <= windowEnd;
    };

    const setHint = (text, isError = false) => {
        const hint = document.getElementById('dpc-agent-availability-hint');

        if (!hint) {
            return;
        }

        hint.textContent = text;
        hint.classList.toggle('text-red-600', isError);
        hint.classList.toggle('text-gray-600', !isError);
    };

    const refreshEvents = () => {
        const state = alpine();

        if (!state) {
            return;
        }

        const view = calendar.view;
        const rangeStart = view.activeStart;
        const rangeEnd = addDays(view.activeEnd, -1);

        calendar.getEvents().forEach((event) => event.remove());

        buildEventsFromState(state, rangeStart, rangeEnd).forEach((event) => {
            calendar.addEvent(event);
        });
    };

    const slotStartFromClick = (date, allDay) => {
        const start = new Date(date.getTime());

        if (allDay) {
            const [hour, minute] = minTime.slice(0, 5).split(':').map(Number);
            start.setHours(hour, minute, 0, 0);
        }

        return start;
    };

    const canPlaceSlot = (start, end) => {
        if (isBlockedWeekend(start, allowWeekends) || isHoliday(start)) {
            setHint('Créneau non autorisé (week-end ou jour férié).', true);

            return false;
        }

        if (start.getTime() < Date.now() - slotMinutes * 60 * 1000) {
            setHint('Ce créneau est déjà passé.', true);

            return false;
        }

        if (!isWithinWindow(start, end)) {
            setHint(
                `Hors plage autorisée (${bookingMinTime.slice(0, 5)}–${bookingMaxTime.slice(0, 5)}).`,
                true
            );

            return false;
        }

        return true;
    };

    const applySelection = (start, end, modeOverride = null) => {
        const state = alpine();

        if (!state) {
            return;
        }

        const mode = modeOverride || state.paintMode || 'date';
        const dateStr = formatDate(start);
        const startTime = formatTime(start);
        const endTime = formatTime(end);

        if (mode === 'recurring') {
            state.recurring.push({
                weekday: isoWeekday(start),
                start_time: startTime,
                end_time: endTime,
            });
            setHint(`Plage récurrente ajoutée (${startTime}–${endTime}).`);
        } else if (mode === 'date') {
            state.dates.push({
                available_on: dateStr,
                start_time: startTime,
                end_time: endTime,
            });
            setHint(`Disponibilité ajoutée le ${dateStr}, ${startTime}–${endTime}.`);
        } else if (mode === 'block_date') {
            const allDay =
                startTime === '00:00' &&
                (endTime === '23:59' || endTime === '00:00');

            state.blocks.push({
                mode: 'date',
                available_on: dateStr,
                weekday: '',
                start_time: allDay ? '00:00' : startTime,
                end_time: allDay ? '23:59' : endTime,
                all_day: allDay,
            });
            setHint(`Indisponibilité ajoutée le ${dateStr}.`);
        } else if (mode === 'block_weekday') {
            state.blocks.push({
                mode: 'weekday',
                available_on: '',
                weekday: isoWeekday(start),
                start_time: startTime,
                end_time: endTime,
                all_day: false,
            });
            setHint(`Indisponibilité récurrente (jour ${isoWeekday(start)}).`);
        }

        state.paintMode = mode;
        el.dataset.paintMode = mode;

        refreshEvents();
    };

    const handleSlotActivate = (start, end, modeOverride = null) => {
        if (!canPlaceSlot(start, end)) {
            return;
        }

        applySelection(start, end, modeOverride);
    };

    const openSlotContextMenu = (clientX, clientY, date, allDay) => {
        const start = slotStartFromClick(date, allDay);
        const endSlot = slotEndFromStart(start, slotMinutes);
        const [gsH, gsM] = minTime.slice(0, 5).split(':').map(Number);
        const [geH, geM] = maxTime.slice(0, 5).split(':').map(Number);
        const fullStart = new Date(start.getTime());
        fullStart.setHours(gsH, gsM, 0, 0);
        const fullEnd = new Date(start.getTime());
        fullEnd.setHours(geH, geM, 0, 0);

        const items = PAINT_MODE_OPTIONS.map((opt) => ({
            mode: opt.id,
            label: opt.label,
            action: () => handleSlotActivate(start, endSlot, opt.id),
        }));

        items.push({ separator: true });
        items.push({
            label: `Plage complète (${minTime.slice(0, 5)}–${maxTime.slice(0, 5)}) — mode actif`,
            action: () => {
                const state = alpine();
                handleSlotActivate(fullStart, fullEnd, state?.paintMode || 'date');
            },
        });

        showContextMenu(clientX, clientY, items);
    };

    const openEventContextMenu = (clientX, clientY, event) => {
        const { kind, index } = event.extendedProps || {};

        if (kind === undefined || index === undefined) {
            return;
        }

        showContextMenu(clientX, clientY, [
            {
                label: 'Retirer cette plage',
                action: () => {
                    const state = alpine();

                    if (!state) {
                        return;
                    }

                    if (kind === 'recurring') {
                        state.recurring.splice(index, 1);
                    } else if (kind === 'date') {
                        state.dates.splice(index, 1);
                    } else if (kind === 'block') {
                        state.blocks.splice(index, 1);
                    }

                    setHint('Plage retirée. Cliquez sur Enregistrer pour sauvegarder.');
                    refreshEvents();
                },
            },
            { separator: true },
            ...PAINT_MODE_OPTIONS.map((opt) => ({
                mode: opt.id,
                label: `Convertir en : ${opt.short}`,
                action: () => {
                    const state = alpine();

                    if (!state) {
                        return;
                    }

                    const start = new Date(event.start);
                    const end = new Date(event.end);

                    if (kind === 'recurring') {
                        state.recurring.splice(index, 1);
                    } else if (kind === 'date') {
                        state.dates.splice(index, 1);
                    } else if (kind === 'block') {
                        state.blocks.splice(index, 1);
                    }

                    handleSlotActivate(start, end, opt.id);
                },
            })),
        ]);
    };

    const resolveDateFromContextTarget = (target) => {
        const col = target.closest('[data-date]');

        if (!col) {
            return null;
        }

        const dateStr = col.getAttribute('data-date');
        const timeEl = target.closest('[data-time]');
        const timeStr = timeEl?.getAttribute('data-time')?.slice(0, 5) || minTime.slice(0, 5);

        if (!dateStr) {
            return null;
        }

        return buildSlotDate(dateStr, timeStr);
    };

    const calendar = new Calendar(el, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale: frLocaleSundayFirst,
        initialView: 'timeGridWeek',
        firstDay: 0,
        height: 640,
        expandRows: true,
        allDaySlot: true,
        nowIndicator: true,
        weekends: allowWeekends,
        scrollTime: toSlotTime(displayMinTime),
        scrollTimeReset: false,
        selectable: true,
        selectMirror: true,
        unselectAuto: true,
        selectMinDistance: 4,
        slotDuration: `00:${String(slotMinutes).padStart(2, '0')}:00`,
        snapDuration: `00:${String(slotMinutes).padStart(2, '0')}:00`,
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
                click: () => calendar.gotoDate(startOfLocalDay(new Date())),
            },
        },
        buttonText: {
            today: "Aujourd'hui",
            week: 'Semaine',
            day: 'Jour',
            month: 'Mois',
        },
        validRange: () => {
            const start = startOfLocalDay(new Date());
            const end = maxDateStr ? new Date(`${maxDateStr}T23:59:59`) : undefined;

            return end ? { start, end } : { start };
        },
        businessHours: [
            {
                daysOfWeek: allowWeekends
                    ? [0, 1, 2, 3, 4, 5, 6]
                    : [1, 2, 3, 4, 5],
                startTime: bookingMinTime.slice(0, 5),
                endTime: bookingMaxTime.slice(0, 5),
            },
        ],
        selectConstraint: 'businessHours',
        selectAllow: (selection) => {
            const { start, end } = selection;

            if (isBlockedWeekend(start, allowWeekends) || isHoliday(start)) {
                return false;
            }

            const durationMs = end.getTime() - start.getTime();
            const minMs = slotMinutes * 60 * 1000;

            if (durationMs < minMs) {
                return false;
            }

            return isWithinWindow(start, end);
        },
        select: (info) => {
            handleSlotActivate(info.start, info.end);
        },
        eventClick: (info) => {
            info.jsEvent.preventDefault();

            if (info.jsEvent.button !== 0) {
                return;
            }

            const { kind, index } = info.event.extendedProps || {};

            if (kind === undefined || index === undefined) {
                return;
            }

            const state = alpine();

            if (!state) {
                return;
            }

            if (kind === 'recurring') {
                state.recurring.splice(index, 1);
            } else if (kind === 'date') {
                state.dates.splice(index, 1);
            } else if (kind === 'block') {
                state.blocks.splice(index, 1);
            }

            setHint('Plage retirée. Clic droit sur un bloc : plus d’options.');
            refreshEvents();
        },
        eventDidMount: (info) => {
            info.el.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                e.stopPropagation();
                openEventContextMenu(e.clientX, e.clientY, info.event);
            });
        },
        datesSet: () => {
            refreshEvents();
        },
    });

    calendar.render();
    el.dataset.ready = '1';

    el.addEventListener('contextmenu', (e) => {
        if (e.target.closest('.fc-event')) {
            return;
        }

        e.preventDefault();

        const start = resolveDateFromContextTarget(e.target);

        if (!start) {
            setHint('Clic droit sur un créneau horaire pour voir les options.', true);

            return;
        }

        openSlotContextMenu(e.clientX, e.clientY, start, false);
    });

    document.addEventListener('dpc-availability-refresh', refreshEvents);

    setHint(
        'Glissez sur le calendrier pour ajouter une plage (mode actif). Clic droit : menu. Clic sur un bloc : retirer.'
    );
}
