export function registerFaqAdmin(Alpine) {
    Alpine.data('faqAdmin', () => ({
        open: false,
        editing: false,
        form: {
            id: null,
            question: '',
            answer: '',
            category: 'Général',
            order_column: 0,
            published: true,
        },
        openCreate() {
            this.editing = false;
            this.form = {
                id: null,
                question: '',
                answer: '',
                category: 'Général',
                order_column: 0,
                published: true,
            };
            this.open = true;
        },
        openEdit(item) {
            this.editing = true;
            this.form = { ...item, published: !!item.published };
            this.open = true;
        },
    }));
}
