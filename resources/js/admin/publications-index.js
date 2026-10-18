function readPubAdminConfig() {
    const el = document.querySelector('[data-pub-admin-config]');
    if (!el) {
        return {
            defaultType: 'document',
            typesOpen: false,
            typeOrderDefault: 0,
        };
    }

    try {
        return JSON.parse(el.dataset.pubAdminConfig || '{}');
    } catch {
        return {
            defaultType: 'document',
            typesOpen: false,
            typeOrderDefault: 0,
        };
    }
}

export function registerPubAdmin(Alpine) {
    Alpine.data('pubAdmin', () => {
        const config = readPubAdminConfig();
        const defaultType = config.defaultType || 'document';

        return {
            open: false,
            editing: false,
            typesOpen: !!config.typesOpen,
            typeOpen: false,
            typeEditing: false,
            form: {
                id: null,
                title: '',
                type: defaultType,
                description: '',
                url: '',
                file_path: null,
                published: true,
            },
            typeForm: {
                id: null,
                label: '',
                code: '',
                icon: 'fa-file',
                badge_class: 'bg-gray-100 text-gray-700',
                order_column: config.typeOrderDefault ?? 0,
            },
            openCreate() {
                this.editing = false;
                this.form = {
                    id: null,
                    title: '',
                    type: defaultType,
                    description: '',
                    url: '',
                    file_path: null,
                    published: true,
                };
                this.open = true;
            },
            openEdit(item) {
                this.editing = true;
                this.form = { ...item, published: !!item.published };
                this.open = true;
            },
            openTypeCreate() {
                this.typeEditing = false;
                this.typeForm = {
                    id: null,
                    label: '',
                    code: '',
                    icon: 'fa-file',
                    badge_class: 'bg-gray-100 text-gray-700',
                    order_column: config.typeOrderDefault ?? 0,
                };
                this.typeOpen = true;
            },
            openTypeEdit(item) {
                this.typeEditing = true;
                this.typeForm = { ...item };
                this.typeOpen = true;
            },
        };
    });
}
