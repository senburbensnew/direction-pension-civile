<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TypeDemande;

class TypeDemandeSeeder extends Seeder
{
    public function run()
    {
        TypeDemande::insert([
            [
                'code' => 'DEMANDE_VIREMENT_BANCAIRE',
                'label' => 'Demande de virement bancaire',
            ],
            [
                'code' => 'DEMANDE_ATTESTATION',
                'label' => 'Demande d’attestation',
            ],
            [
                'code' => 'DEMANDE_TRANSFERT_CHEQUE',
                'label' => 'Demande de transfert de chèque',
            ],
            [
                'code' => 'DEMANDE_ARRET_PAIEMENT',
                'label' => 'Demande d’arrêt de paiement',
            ],
            [
                'code' => 'DEMANDE_ARRET_VIREMENT',
                'label' => 'Demande d’arrêt de virement',
            ],
            [
                'code' => 'DEMANDE_PREUVE_EXISTENCE',
                'label' => 'Demande de preuve d’existence',
            ],
            [
                'code' => 'DEMANDE_REINSERTION',
                'label' => 'Demande de réinsertion',
            ],
            [
                'code' => 'DEMANDE_PENSION_REVERSION',
                'label' => 'Demande de pension de réversion',
            ],
            [
                'code' => 'DEMANDE_ETAT_CARRIERE',
                'label' => 'Demande d’état de carrière',
            ],
            [
                'code' => 'DEMANDE_PENSION',
                'label' => 'Demande de pension',
            ],
            [
                'code' => 'DEMANDE_ADHESION',
                'label' => 'Demande d’adhésion',
            ],
        ]);
    }
}