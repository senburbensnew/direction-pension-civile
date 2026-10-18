export function registerMediaAdmin(Alpine) {
    Alpine.data('mediaAdmin', () => ({
        open: false,
        editing: false,
        form: {
            id: null,
            title: '',
            type: 'image',
            description: '',
            url: '',
            file_path: null,
            order_column: 0,
            published: true,
            is_featured: false,
        },
        openCreate() {
            this.editing = false;
            this.form = {
                id: null,
                title: '',
                type: 'video',
                description: '',
                url: '',
                file_path: null,
                order_column: 0,
                published: true,
                is_featured: false,
            };
            this.open = true;
        },
        openEdit(item) {
            this.editing = true;
            this.form = {
                ...item,
                published: !!item.published,
                is_featured: !!item.is_featured,
            };
            this.open = true;
        },
    }));
}
