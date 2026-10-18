const visioPollSeen = new Set();
const unlockReloadSeen = new Set();

function scheduleVisioPoll(entry, delayMs) {
    window.setTimeout(async function tick() {
        if (document.hidden) {
            scheduleVisioPoll(entry, delayMs);
            return;
        }

        let nextDelay = delayMs;

        try {
            const response = await fetch(entry.url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                scheduleVisioPoll(entry, nextDelay);
                return;
            }

            const data = await response.json();

            if (data.status !== entry.while) {
                window.location.reload();
                return;
            }

            if (data.opens_at) {
                const msUntilOpen = new Date(data.opens_at).getTime() - Date.now();
                if (msUntilOpen > 0 && msUntilOpen <= 120000) {
                    nextDelay = 5000;
                } else if (msUntilOpen > 120000) {
                    nextDelay = 15000;
                }
            }
        } catch {
            /* retry on network errors */
        }

        scheduleVisioPoll(entry, nextDelay);
    }, delayMs);
}

function registerVisioPoll(entry) {
    if (!entry?.url || visioPollSeen.has(entry.url)) {
        return;
    }
    visioPollSeen.add(entry.url);
    scheduleVisioPoll(entry, 10000);
}

function scheduleUnlockReload(isoString) {
    if (!isoString || unlockReloadSeen.has(isoString)) {
        return;
    }
    unlockReloadSeen.add(isoString);

    const targetMs = new Date(isoString).getTime();
    if (Number.isNaN(targetMs)) {
        return;
    }

    const MAX_DELAY = 2147483647;
    const GRACE_MS = 500;

    const reloadIfDue = () => {
        if (Date.now() >= targetMs) {
            window.location.reload();
            return true;
        }
        return false;
    };

    const schedule = () => {
        if (reloadIfDue()) {
            return;
        }

        let delay = targetMs - Date.now() + GRACE_MS;
        if (delay <= 0) {
            window.location.reload();
            return;
        }
        if (delay > MAX_DELAY) {
            window.setTimeout(schedule, MAX_DELAY);
            return;
        }
        window.setTimeout(() => {
            window.location.reload();
        }, delay);
    };

    document.addEventListener('visibilitychange', function onVisible() {
        if (!document.hidden && reloadIfDue()) {
            document.removeEventListener('visibilitychange', onVisible);
        }
    });

    schedule();
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-visio-poll]').forEach((el) => {
        registerVisioPoll({
            url: el.dataset.visioPoll,
            while: el.dataset.visioWhile,
        });
    });

    document.querySelectorAll('[data-unlock-reload-at]').forEach((el) => {
        scheduleUnlockReload(el.dataset.unlockReloadAt);
    });
});
