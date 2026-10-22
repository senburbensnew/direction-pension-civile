export function registerDualListbox(Alpine) {
    Alpine.data('dualListbox', (options, initialAssigned) => ({
        options: (Array.isArray(options) ? options : []).map((item) => ({
            value: item.value,
            label: item.label ?? item.value,
            description: item.description ?? null,
        })),
        assignedValues: Array.isArray(initialAssigned) ? [...initialAssigned] : [],

        pickedAvailable: [],
        pickedAssigned: [],

        tooltipVisible: false,
        tooltipText: '',
        tooltipStyle: '',

        get availableItems() {
            const assigned = new Set(this.assignedValues);

            return this.options
                .filter((item) => ! assigned.has(item.value))
                .sort((a, b) => String(a.label).localeCompare(String(b.label), 'fr'));
        },

        get assignedItems() {
            const assigned = new Set(this.assignedValues);

            return this.options
                .filter((item) => assigned.has(item.value))
                .sort((a, b) => String(a.label).localeCompare(String(b.label), 'fr'));
        },

        togglePick(event, side, value) {
            const key = side === 'available' ? 'pickedAvailable' : 'pickedAssigned';
            let current = [...this[key]];

            if (event.ctrlKey || event.metaKey) {
                if (current.includes(value)) {
                    current = current.filter((entry) => entry !== value);
                } else {
                    current.push(value);
                }
            } else if (current.length === 1 && current[0] === value) {
                current = [];
            } else {
                current = [value];
            }

            this[key] = current;
        },

        isPicked(side, value) {
            const key = side === 'available' ? 'pickedAvailable' : 'pickedAssigned';

            return this[key].includes(value);
        },

        showInfoTooltip(event, text) {
            if (! text || typeof window === 'undefined') {
                return;
            }

            const target = event.currentTarget;
            const rect = target.getBoundingClientRect();
            const gap = 8;
            const maxWidth = 280;
            const margin = 8;
            const spaceAbove = rect.top;
            const spaceBelow = window.innerHeight - rect.bottom;
            const showBelow = spaceAbove < 96 && spaceBelow >= spaceAbove;

            let top;
            let transform;

            if (showBelow) {
                top = rect.bottom + gap;
                transform = 'translate(-50%, 0)';
            } else {
                top = rect.top - gap;
                transform = 'translate(-50%, -100%)';
            }

            let left = rect.left + rect.width / 2;
            const half = maxWidth / 2;
            left = Math.max(margin + half, Math.min(window.innerWidth - margin - half, left));

            this.tooltipText = text;
            this.tooltipStyle = `top: ${top}px; left: ${left}px; transform: ${transform}; max-width: ${maxWidth}px;`;
            this.tooltipVisible = true;
        },

        hideInfoTooltip() {
            this.tooltipVisible = false;
        },

        moveSelectedToAssigned() {
            if (this.pickedAvailable.length === 0) {
                return;
            }
            this.assignedValues = [
                ...new Set([...this.assignedValues, ...this.pickedAvailable]),
            ];
            this.pickedAvailable = [];
        },

        moveAllToAssigned() {
            this.assignedValues = this.options.map((item) => item.value);
            this.pickedAvailable = [];
        },

        moveSelectedToAvailable() {
            if (this.pickedAssigned.length === 0) {
                return;
            }
            const remove = new Set(this.pickedAssigned);
            this.assignedValues = this.assignedValues.filter((value) => ! remove.has(value));
            this.pickedAssigned = [];
        },

        moveAllToAvailable() {
            this.assignedValues = [];
            this.pickedAssigned = [];
        },
    }));
}
