export function registerRdvBookingForm(Alpine) {
    Alpine.data('rdvBookingForm', (initial) => ({

                modalite: initial.modalite || '',

                motif: initial.motif || '',

                dateSouhaitee:
                    initial.dateSouhaitee || '',

                heureSouhaitee:
                    initial.heureSouhaitee || '',

                lieuRdv:
                    initial.lieuRdv || '',

                accepte:
                    Boolean(initial.accepte),

                /*
                * État de soumission.
                *
                * false = bouton normal
                * true  = formulaire en cours d'envoi
                */
                submitting: false,

                motifs:
                    initial.motifs || {},

                motifServices:
                    initial.motifServices || {},

                motifServiceCodes:
                    initial.motifServiceCodes || {},

                motifPrepareHints:
                    initial.motifPrepareHints || {},


                motifLabel() {
                    return this.motifs[this.motif] || '—';
                },


                serviceLabel() {
                    const label = this.motifServices[this.motif];
                    if (label && label !== '—') {
                        return label;
                    }

                    const code = this.motifServiceCodes[this.motif];
                    const byCode = {
                        service_accueil_formalites: 'Service Formalités',
                        service_formalite: 'Service Formalités',
                    };

                    return byCode[code] || label || '—';
                },

                recapDateHeure() {
                    if (
                        !this.dateSouhaitee ||
                        !this.heureSouhaitee
                    ) {
                        return 'Aucun créneau sélectionné';
                    }

                    const parsed = new Date(
                        `${this.dateSouhaitee}T${this.heureSouhaitee}:00`
                    );

                    if (Number.isNaN(parsed.getTime())) {
                        return `${this.dateSouhaitee} à ${this.heureSouhaitee}`;
                    }

                    return new Intl.DateTimeFormat('fr-FR', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false,
                    }).format(parsed);
                },

                recapLieuOuLien() {

                    if (
                        this.modalite === 'physique'
                    ) {

                        return this.lieuRdv || '—';

                    }

                    return 'Lien sécurisé unique — actif 15 minutes avant le rendez-vous';

                },

                documentsPourModalite() {
                    const hints =
                        this.motifPrepareHints[this.motif]
                        || {};

                    return hints[this.modalite] || [];
                },

                syncSchedulerService() {
                    const code =
                        this.motifServiceCodes[this.motif]
                        || '';

                    window.dispatchEvent(
                        new CustomEvent('rdv-service-changed', {
                            detail: { serviceCode: code },
                        })
                    );
                },
    }));
}
