export function registerGlossaireAdmin(Alpine) {
    Alpine.data('glossaireAdmin', () => ({
        open: false,
        editing: false,
        form: {
            id: null,
            term: '',
            definition: '',
            category: 'Général',
            icon: 'fa-book',
            order_column: 0,
            published: true,
        },
        openCreate() {
            this.editing = false;
            this.form = {
                id: null,
                term: '',
                definition: '',
                category: 'Général',
                icon: 'fa-book',
                order_column: 0,
                published: true,
            };
            this.open = true;
        },
        openEdit(item) {
            this.editing = true;
            this.form = {
                ...item,
                published: !!item.published,
                order_column: item.order_column ?? 0,
            };
            this.open = true;
        },
    }));
}
