function bootGlossairePublic() {
    const search = document.getElementById('search-terms');
    const cards = document.querySelectorAll('.term-card');
    const empty = document.getElementById('gloss-empty');
    const tabs = document.querySelectorAll('#gloss-tabs .tab-btn');
    let cat = 'all';

    const apply = () => {
        const q = (search?.value || '').trim().toLowerCase();
        let visible = 0;
        cards.forEach((card) => {
            const matchCat = cat === 'all' || card.dataset.category === cat;
            const matchQ = !q || (card.dataset.search || '').includes(q);
            const show = matchCat && matchQ;
            card.hidden = !show;
            if (show) {
                visible += 1;
            }
        });
        if (empty) {
            empty.classList.toggle('hidden', visible > 0 || cards.length === 0);
        }
    };

    tabs.forEach((tab) =>
        tab.addEventListener('click', function onTabClick() {
            tabs.forEach((t) => t.classList.remove('active'));
            this.classList.add('active');
            cat = this.dataset.cat;
            apply();
        })
    );
    search?.addEventListener('input', apply);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootGlossairePublic);
} else {
    bootGlossairePublic();
}
