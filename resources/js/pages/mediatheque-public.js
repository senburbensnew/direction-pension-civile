document.addEventListener('DOMContentLoaded', () => {
    const grid = document.getElementById('media-grid');
    if (!grid) {
        return;
    }

    const tabs = document.querySelectorAll('.tab-btn');
    const cards = grid.querySelectorAll('.media-card');
    const empty = document.getElementById('media-empty');
    const searchEl = document.getElementById('media-search');
    const clearBtn = document.getElementById('media-search-clear');
    let activeTab = 'audio';

    function applyFilters() {
        if (!searchEl || !empty || cards.length === 0) {
            return;
        }

        const q = (searchEl.value || '').trim().toLowerCase();
        if (clearBtn) {
            clearBtn.classList.toggle('hidden', q === '');
        }
        let visible = 0;

        cards.forEach((card) => {
            const type = card.getAttribute('data-type');
            const text = card.getAttribute('data-searchable') || '';
            const show =
                (activeTab === 'all' || type === activeTab) &&
                (q === '' || text.includes(q));
            card.hidden = !show;
            if (show) {
                visible += 1;
            }
        });

        empty.classList.toggle('hidden', visible > 0);
        grid.classList.toggle('hidden', visible === 0);
    }

    tabs.forEach((tab) => {
        tab.addEventListener('click', function onTabClick() {
            tabs.forEach((t) => t.classList.remove('is-active'));
            this.classList.add('is-active');
            activeTab = this.getAttribute('data-tab');
            applyFilters();
        });
    });

    searchEl?.addEventListener('input', applyFilters);
    clearBtn?.addEventListener('click', () => {
        if (!searchEl) {
            return;
        }
        searchEl.value = '';
        applyFilters();
        searchEl.focus();
    });

    applyFilters();
});
