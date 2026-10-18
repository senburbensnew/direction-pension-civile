function readContactSubjectsConfig() {
    const el = document.querySelector('[data-contact-subjects-config]');
    if (!el) {
        return { defaultPosition: 1 };
    }

    try {
        return JSON.parse(el.dataset.contactSubjectsConfig || '{}');
    } catch {
        return { defaultPosition: 1 };
    }
}

export function registerContactSubjectsAdmin(Alpine) {
    Alpine.data('contactSubjectsAdmin', () => {
        const { defaultPosition } = readContactSubjectsConfig();

        return {
            open: false,
            editing: false,
            form: {
                id: null,
                label: '',
                position: defaultPosition,
                is_active: true,
                allows_custom: false,
            },
            openCreate() {
                this.editing = false;
                this.form = {
                    id: null,
                    label: '',
                    position: defaultPosition,
                    is_active: true,
                    allows_custom: false,
                };
                this.open = true;
            },
            openEdit(item) {
                this.editing = true;
                this.form = {
                    id: item.id,
                    label: item.label,
                    position: item.position,
                    is_active: !!item.is_active,
                    allows_custom: !!item.allows_custom,
                };
                this.open = true;
            },
        };
    });
}
