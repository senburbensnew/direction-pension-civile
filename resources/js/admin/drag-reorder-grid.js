function readCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function initDragReorderGrid(grid) {
    if (!grid || grid.dataset.dragReorderReady === '1') {
        return;
    }
    grid.dataset.dragReorderReady = '1';

    const cardSelector = grid.dataset.cardSelector || '.dpc-drag-card';
    const reorderUrl = grid.dataset.reorderUrl;
    const useFlip = grid.dataset.reorderFlip === '1';

    const bannerHint = document.getElementById(grid.dataset.bannerHintId || 'banner-hint');
    const bannerSaving = document.getElementById(grid.dataset.bannerSavingId || 'banner-saving');
    const bannerSaved = document.getElementById(grid.dataset.bannerSavedId || 'banner-saved');

    function showBanner(el, autohide = 0) {
        [bannerHint, bannerSaving, bannerSaved].forEach((b) => {
            if (!b) {
                return;
            }
            b.classList.add('hidden');
            b.classList.remove('flex');
        });
        if (!el) {
            return;
        }
        el.classList.remove('hidden');
        el.classList.add('flex');
        if (autohide) {
            setTimeout(() => {
                el.classList.add('hidden');
                el.classList.remove('flex');
                if (bannerHint) {
                    bannerHint.classList.remove('hidden');
                    bannerHint.classList.add('flex');
                }
            }, autohide);
        }
    }

    let dragged = null;

    const cards = () => [...grid.querySelectorAll(cardSelector)];

    const clearDropClasses = () => {
        cards().forEach((c) => c.classList.remove('drop-before', 'drop-after'));
    };

    const flipMove = (moveFn) => {
        const all = cards();
        const first = new Map(all.map((c) => [c, c.getBoundingClientRect()]));
        moveFn();
        const last = new Map(cards().map((c) => [c, c.getBoundingClientRect()]));
        cards().forEach((c) => {
            const f = first.get(c);
            const l = last.get(c);
            if (!f || !l) {
                return;
            }
            const dx = f.left - l.left;
            const dy = f.top - l.top;
            if (dx === 0 && dy === 0) {
                return;
            }
            c.style.transition = 'none';
            c.style.transform = `translate(${dx}px, ${dy}px)`;
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    c.style.transition = 'transform 300ms cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                    c.style.transform = '';
                });
            });
        });
    };

    const insertCard = (target, before) => {
        if (!dragged) {
            return;
        }
        if (before) {
            grid.insertBefore(dragged, target);
        } else {
            grid.insertBefore(dragged, target.nextSibling);
        }
    };

    const saveOrder = () => {
        if (!reorderUrl) {
            return;
        }
        showBanner(bannerSaving);
        const ids = cards().map((c) => c.dataset.id);
        cards().forEach((c, i) => {
            const badge = c.querySelector('.order-badge');
            if (badge) {
                badge.textContent = `#${i + 1}`;
            }
        });
        fetch(reorderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': readCsrfToken(),
            },
            body: JSON.stringify({ order: ids }),
        })
            .then((r) => r.json())
            .then((data) => showBanner(data.success ? bannerSaved : bannerHint, data.success ? 2500 : 0))
            .catch(() => showBanner(bannerHint, 0));
    };

    cards().forEach((card) => {
        card.addEventListener('dragstart', (e) => {
            dragged = card;
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', card.dataset.id || '');
            requestAnimationFrame(() => card.classList.add('dragging'));
        });

        card.addEventListener('dragend', () => {
            if (dragged) {
                dragged.classList.remove('dragging');
            }
            clearDropClasses();
            dragged = null;
            saveOrder();
        });

        card.addEventListener('dragover', (e) => {
            e.preventDefault();
            if (!dragged || card === dragged) {
                return;
            }
            clearDropClasses();
            const { left, width } = card.getBoundingClientRect();
            card.classList.add(e.clientX < left + width / 2 ? 'drop-before' : 'drop-after');
        });

        card.addEventListener('dragleave', () => clearDropClasses());

        card.addEventListener('drop', (e) => {
            e.preventDefault();
            if (!dragged || card === dragged) {
                return;
            }
            clearDropClasses();
            const { left, width } = card.getBoundingClientRect();
            const before = e.clientX < left + width / 2;
            if (useFlip) {
                flipMove(() => insertCard(card, before));
            } else {
                insertCard(card, before);
            }
        });
    });
}

function bootDragReorderGrids() {
    document.querySelectorAll('[data-dpc-drag-reorder]').forEach(initDragReorderGrid);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootDragReorderGrids);
} else {
    bootDragReorderGrids();
}
