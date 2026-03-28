<?php

namespace App\Enums;

enum TypeDemandeEnum: string
{
    // PENSIONNAIRE
    case DEMANDE_VIREMENT_BANCAIRE = 'DEMANDE_VIREMENT_BANCAIRE';
    case DEMANDE_ATTESTATION = 'DEMANDE_ATTESTATION';
    case DEMANDE_TRANSFERT_CHEQUE = 'DEMANDE_TRANSFERT_CHEQUE';
    case DEMANDE_ARRET_PAIEMENT = 'DEMANDE_ARRET_PAIEMENT';
    case DEMANDE_REINSERTION = 'DEMANDE_REINSERTION';
    case DEMANDE_ARRET_VIREMENT = 'DEMANDE_ARRET_VIREMENT';
    case DEMANDE_PREUVE_EXISTENCE = 'DEMANDE_PREUVE_EXISTENCE';
    case DEMANDE_PENSION_REVERSION = 'DEMANDE_PENSION_REVERSION';

    // FONCTIONNAIRE
    case DEMANDE_ETAT_CARRIERE = 'DEMANDE_ETAT_CARRIERE';
    case DEMANDE_PENSION = 'DEMANDE_PENSION';

    // INSTITUTION
    case DEMANDE_ADHESION = 'DEMANDE_ADHESION';

    public function label(): string
    {
        return match($this) {
            self::DEMANDE_VIREMENT_BANCAIRE => 'Demande de virement bancaire',
            self::DEMANDE_ATTESTATION       => "Demande d'attestation",
            self::DEMANDE_TRANSFERT_CHEQUE  => 'Demande de transfert de chèque',
            self::DEMANDE_ARRET_PAIEMENT    => "Demande d'arrêt de paiement",
            self::DEMANDE_REINSERTION       => 'Demande de réinsertion',
            self::DEMANDE_ARRET_VIREMENT    => "Demande d'arrêt de virement",
            self::DEMANDE_PREUVE_EXISTENCE  => "Preuve d'existence",
            self::DEMANDE_PENSION_REVERSION => 'Demande de pension de réversion',
            self::DEMANDE_ETAT_CARRIERE     => "Demande d'état de carrière",
            self::DEMANDE_PENSION           => 'Demande de pension',
            self::DEMANDE_ADHESION          => "Demande d'adhésion",
        };
    }
}