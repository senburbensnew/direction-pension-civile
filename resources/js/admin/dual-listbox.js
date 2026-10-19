export function registerDualListbox(Alpine) {
    Alpine.data('dualListbox', (options, initialAssigned) => ({
        options: Array.isArray(options) ? options : [],
        assignedValues: Array.isArray(initialAssigned) ? [...initialAssigned] : [],

        pickedAvailable: [],
        pickedAssigned: [],

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

        readSelection(refName) {
            const el = this.$refs[refName];
            if (! el) {
                return [];
            }

            return Array.from(el.selectedOptions).map((option) => option.value);
        },

        syncPickedAvailable() {
            this.pickedAvailable = this.readSelection('availableList');
        },

        syncPickedAssigned() {
            this.pickedAssigned = this.readSelection('assignedList');
        },

        moveSelectedToAssigned() {
            this.syncPickedAvailable();
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
            this.syncPickedAssigned();
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
