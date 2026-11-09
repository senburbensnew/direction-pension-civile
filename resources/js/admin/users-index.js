import Sortable from 'sortablejs';

export function registerAdminUsersIndex(Alpine) {
    Alpine.data('adminUsersIndex', (initial) => ({
        loading: false,
        columns: initial.columns,
        labels: initial.labels,
        sortable: initial.sortable,
        defaultDir: initial.defaultDir,
        sort: initial.sort,
        dir: initial.dir,
        explicitSort: initial.explicitSort,
        indexUrl: initial.indexUrl,
        showColumns: false,
        theadOffset: 48,
        _columnSortable: null,

        init() {
            this.measureTableHead();
            this.$nextTick(() => this.initColumnSortable());
        },

        initColumnSortable() {
            const row = this.$refs.headerRow;
            if (!row || this._columnSortable) {
                return;
            }

            this._columnSortable = Sortable.create(row, {
                animation: 150,
                handle: '[data-column-drag-handle]',
                draggable: 'th[data-column-key]',
                onMove(evt) {
                    return evt.related?.hasAttribute('data-column-key') ?? false;
                },
                onEnd: (evt) => {
                    if (evt.oldIndex == null || evt.newIndex == null || evt.oldIndex === evt.newIndex) {
                        return;
                    }

                    const maxIndex = this.columns.length - 1;
                    const from = Math.min(Math.max(evt.oldIndex, 0), maxIndex);
                    const to = Math.min(Math.max(evt.newIndex, 0), maxIndex);
                    this.moveColumn(from, to);
                },
            });
        },

        measureTableHead() {
            const head = this.$refs.tableHead;
            this.theadOffset = head ? head.getBoundingClientRect().height : 48;
        },

        startLoading() {
            this.measureTableHead();
            this.loading = true;
        },

        label(key) {
            return this.labels[key] || key;
        },

        isSortable(key) {
            return this.sortable.includes(key);
        },

        navigate(nextColumns, nextSort = this.sort, nextDir = this.dir, includeSort = this.explicitSort) {
            this.startLoading();
            const url = new URL(this.indexUrl);
            const params = new URLSearchParams(window.location.search);

            params.delete('columns[]');
            params.delete('columns');
            nextColumns.forEach((key) => params.append('columns[]', key));

            if (includeSort && nextSort) {
                params.set('sort', nextSort);
                params.set('dir', nextDir);
            } else {
                params.delete('sort');
                params.delete('dir');
            }

            url.search = params.toString();
            window.location = url.toString();
        },

        toggleSort(key) {
            if (!this.isSortable(key)) {
                return;
            }
            if (this.sort === key) {
                this.dir = this.dir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sort = key;
                this.dir = this.defaultDir[key] || 'asc';
            }
            this.explicitSort = true;
            this.navigate(this.columns, this.sort, this.dir);
        },

        moveColumn(from, to) {
            if (from === null || from === to || to < 0 || to >= this.columns.length) {
                return;
            }
            const next = [...this.columns];
            const [item] = next.splice(from, 1);
            next.splice(to, 0, item);
            this.navigate(next);
        },
    }));
}
