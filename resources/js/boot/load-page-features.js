/**
 * Imports dynamiques (libs lourdes / pages) après le démarrage d'Alpine.
 */
export async function loadPageFeatures() {
    if (document.getElementById('dpc-chronogramme')) {
        const { initDpcChronogramme } = await import('../chronogramme.js');
        initDpcChronogramme();
    }

    const agentAvailCal = document.getElementById('dpc-agent-availability-calendar');
    if (agentAvailCal) {
        const { initAgentAvailabilityCalendar } = await import('../rdv-agent-availability.js');
        initAgentAvailabilityCalendar();
    }

    if (document.querySelector('.dpc-rdv-scheduler')) {
        const { initRdvScheduler } = await import('../rdv-scheduler.js');
        initRdvScheduler();
    }

    if (document.querySelector('[data-dpc-home-graphs]')) {
        const { initHomeGraphs } = await import('../components/home-graphs.js');
        initHomeGraphs();
    }

    if (document.querySelector('[data-dpc-home-stats]')) {
        const { initHomeStatsCharts } = await import('../components/home-stats-charts.js');
        initHomeStatsCharts();
    }

    if (document.querySelector('[data-dpc-jitsi-modal]')) {
        const { initJitsiModal } = await import('../components/jitsi-modal.js');
        initJitsiModal();
    }

    if (document.querySelector('[data-dpc-profile-picture]')) {
        const { initProfilePictureCrop } = await import('../components/profile-picture.js');
        initProfilePictureCrop();
    }

    if (document.querySelector('[data-dpc-existence-proof]')) {
        const { initExistenceProof } = await import('../components/existence-proof.js');
        initExistenceProof();
    }
}
