import { DataSet, Timeline } from 'vis-timeline/standalone';
import 'vis-timeline/styles/vis-timeline-graph2d.min.css';

export function initDpcChronogramme() {
    const el = document.getElementById('dpc-chronogramme');
    if (!el || el.dataset.ready === '1') {
        return;
    }

    let events = [];
    try {
        events = JSON.parse(el.dataset.events || '[]');
    } catch {
        events = [];
    }

    if (!events.length) {
        return;
    }

    const yearCount = {};
    const items = new DataSet(
        events.map((event, index) => {
            const year = Number(event.annee);
            yearCount[year] = (yearCount[year] || 0) + 1;
            const month = Math.min(10, (yearCount[year] - 1) * 5);

            return {
                id: index + 1,
                content: String(event.annee),
                start: new Date(year, month, 1),
                title: `${event.annee} — ${event.texte}`,
                className: 'dpc-chrono-item',
            };
        }),
    );

    const timeline = new Timeline(el, items, {
        min: new Date(1838, 0, 1),
        max: new Date(2018, 11, 31),
        start: new Date(1838, 0, 1),
        end: new Date(2018, 11, 31),
        showCurrentTime: false,
        moveable: true,
        zoomable: true,
        stack: true,
        height: 360,
        margin: { item: 12, axis: 24 },
        orientation: 'top',
        tooltip: { followMouse: true },
    });

    timeline.on('select', ({ items: selected }) => {
        if (!selected.length) {
            return;
        }

        document
            .getElementById(`date-importante-${selected[0]}`)
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });

    el.dataset.ready = '1';
    window.addEventListener('resize', () => timeline.redraw());
}
