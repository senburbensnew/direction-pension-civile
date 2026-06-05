<?php

namespace Database\Seeders;

use App\Models\RequiredCircuitService;
use App\Models\Service;
use Illuminate\Database\Seeder;

class RequiredCircuitSeeder extends Seeder
{
    public function run(): void
    {
        $S = Service::pluck('id', 'code');

        $entries = [
            // Full circuit: virement, chèque, pension, réversion → liquidation path
            [Service::SECRETARIAT,        'DEMANDE_VIREMENT_BANCAIRE'],
            [Service::LIQUIDATION,        'DEMANDE_VIREMENT_BANCAIRE'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_VIREMENT_BANCAIRE'],
            [Service::FORMALITE,          'DEMANDE_VIREMENT_BANCAIRE'],

            [Service::SECRETARIAT,        'DEMANDE_TRANSFERT_CHEQUE'],
            [Service::LIQUIDATION,        'DEMANDE_TRANSFERT_CHEQUE'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_TRANSFERT_CHEQUE'],
            [Service::FORMALITE,          'DEMANDE_TRANSFERT_CHEQUE'],

            [Service::SECRETARIAT,        'DEMANDE_PENSION'],
            [Service::LIQUIDATION,        'DEMANDE_PENSION'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_PENSION'],
            [Service::FORMALITE,          'DEMANDE_PENSION'],

            [Service::SECRETARIAT,        'DEMANDE_PENSION_REVERSION'],
            [Service::LIQUIDATION,        'DEMANDE_PENSION_REVERSION'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_PENSION_REVERSION'],
            [Service::FORMALITE,          'DEMANDE_PENSION_REVERSION'],

            // Arrêts → comptabilité path
            [Service::SECRETARIAT,        'DEMANDE_ARRET_PAIEMENT'],
            [Service::COMPTABILITE,       'DEMANDE_ARRET_PAIEMENT'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_ARRET_PAIEMENT'],
            [Service::FORMALITE,          'DEMANDE_ARRET_PAIEMENT'],

            [Service::SECRETARIAT,        'DEMANDE_ARRET_VIREMENT'],
            [Service::COMPTABILITE,       'DEMANDE_ARRET_VIREMENT'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_ARRET_VIREMENT'],
            [Service::FORMALITE,          'DEMANDE_ARRET_VIREMENT'],

            // Réinsertion → assurance path
            [Service::SECRETARIAT,        'DEMANDE_REINSERTION'],
            [Service::ASSURANCE,          'DEMANDE_REINSERTION'],
            [Service::CONTROLE_PLACEMENT, 'DEMANDE_REINSERTION'],
            [Service::FORMALITE,          'DEMANDE_REINSERTION'],

            // État de carrière
            [Service::SECRETARIAT,        'DEMANDE_ETAT_CARRIERE'],
            [Service::COMPTABILITE,       'DEMANDE_ETAT_CARRIERE'],
            [Service::FORMALITE,          'DEMANDE_ETAT_CARRIERE'],

            // Simplified: attestation, preuve, adhésion
            [Service::SECRETARIAT,        'DEMANDE_ATTESTATION'],
            [Service::FORMALITE,          'DEMANDE_ATTESTATION'],

            [Service::SECRETARIAT,        'DEMANDE_PREUVE_EXISTENCE'],
            [Service::FORMALITE,          'DEMANDE_PREUVE_EXISTENCE'],

            [Service::SECRETARIAT,        'DEMANDE_ADHESION'],
            [Service::FORMALITE,          'DEMANDE_ADHESION'],

            // DEMANDE_RENCONTRE → no required services (intentional omission)
        ];

        foreach ($entries as [$serviceCode, $typeDemande]) {
            $serviceId = $S[$serviceCode] ?? null;
            if (!$serviceId) continue;

            RequiredCircuitService::updateOrCreate(
                ['service_id' => $serviceId, 'type_demande' => $typeDemande],
            );
        }
    }
}
