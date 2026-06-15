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

        // Le circuit par défaut passe par le Secrétariat pour tous les types standards.
        // Les circuits personnalisés (liquidation, contrôle, etc.) seront configurés
        // via l'interface d'administration une fois les types concernés adaptés.
        $standardTypes = [
            'DEMANDE_VIREMENT_BANCAIRE',
            'DEMANDE_TRANSFERT_CHEQUE',
            'DEMANDE_PENSION',
            'DEMANDE_PENSION_REVERSION',
            'DEMANDE_ARRET_PAIEMENT',
            'DEMANDE_ARRET_VIREMENT',
            'DEMANDE_REINSERTION',
            'DEMANDE_ETAT_CARRIERE',
            'DEMANDE_ATTESTATION',
            'DEMANDE_PREUVE_EXISTENCE',
            'DEMANDE_ADHESION',
            // DEMANDE_RENCONTRE → formulaire public, aucun service requis
        ];

        $secretariatId = $S[Service::SECRETARIAT] ?? null;
        if (!$secretariatId) return;

        foreach ($standardTypes as $type) {
            RequiredCircuitService::updateOrCreate(
                ['service_id' => $secretariatId, 'type_demande' => $type],
            );
        }
    }
}
