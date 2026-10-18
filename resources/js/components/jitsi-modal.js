export function initJitsiModal() {
    const root = document.querySelector('[data-dpc-jitsi-modal]');
    if (!root || root.dataset.jitsiReady === '1') {
        return;
    }
    root.dataset.jitsiReady = '1';

    window.openJitsiModal = (roomName) => {
        const alpineRoot = root.closest('[x-data]') || root;
        const alpineData = alpineRoot.__x?.$data;
        if (alpineData) {
            alpineData.open = true;
        }

        if (typeof window.JitsiMeetExternalAPI !== 'function') {
            console.error('Jitsi Meet External API not loaded.');
            return;
        }

        const domain = root.dataset.jitsiDomain || 'meet.jit.si';
        const container = root.querySelector('#jitsi-container');
        if (!container) {
            return;
        }

        container.innerHTML = '';
        // eslint-disable-next-line no-new
        new window.JitsiMeetExternalAPI(domain, {
            roomName,
            parentNode: container,
        });
    };
}
