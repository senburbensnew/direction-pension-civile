/**
 * Enregistre les composants Alpine spécifiques à une page avant Alpine.start().
 */
export async function registerAlpinePageComponents() {
    const Alpine = window.Alpine;
    if (!Alpine) {
        return;
    }
    if (document.querySelector('[data-dpc-demande-compte-form]')) {
        const { registerDemandeCompteForm } = await import('../demande-compte-form.js');
        registerDemandeCompteForm(Alpine);
    }

    if (document.querySelector('[data-rdv-booking-form]')) {
        const { registerRdvBookingForm } = await import('../pages/rdv-booking-form.js');
        registerRdvBookingForm(Alpine);
    }

    if (document.querySelector('[data-faq-admin]')) {
        const { registerFaqAdmin } = await import('../admin/faq-index.js');
        registerFaqAdmin(Alpine);
    }

    if (document.querySelector('[data-glossaire-admin]')) {
        const { registerGlossaireAdmin } = await import('../admin/glossaire-index.js');
        registerGlossaireAdmin(Alpine);
    }

    if (document.querySelector('[data-media-admin]')) {
        const { registerMediaAdmin } = await import('../admin/mediatheque-index.js');
        registerMediaAdmin(Alpine);
    }

    if (document.querySelector('[data-pub-admin-config]')) {
        const { registerPubAdmin } = await import('../admin/publications-index.js');
        registerPubAdmin(Alpine);
    }

    if (document.querySelector('[data-dual-listbox]')) {
        const { registerDualListbox } = await import('../admin/dual-listbox.js');
        registerDualListbox(Alpine);
    }

    if (document.querySelector('[data-contact-subjects-config]')) {
        const { registerContactSubjectsAdmin } = await import('../admin/contact-parameters-index.js');
        registerContactSubjectsAdmin(Alpine);
    }
}
