<?php

namespace Database\Seeders;

use App\Models\FluxTransition;
use App\Models\Service;
use Illuminate\Database\Seeder;

class FluxTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $transitions = [
            // ── Circuit principal ────────────────────────────────────────────
            // Initial submission → Direction (source is null)
            [null,                         Service::DIRECTION,          'Réception initiale'],

            // Direction annotates → Secrétariat
            [Service::DIRECTION,           Service::SECRETARIAT,        'Annoter'],

            // Secrétariat dispatches to processing services
            [Service::SECRETARIAT,         Service::LIQUIDATION,        'Dispatcher'],
            [Service::SECRETARIAT,         Service::COMPTABILITE,       'Dispatcher'],
            [Service::SECRETARIAT,         Service::ASSURANCE,          'Dispatcher'],

            // Processing services → Contrôle & Placement
            [Service::LIQUIDATION,         Service::CONTROLE_PLACEMENT, 'Transmettre'],
            [Service::COMPTABILITE,        Service::CONTROLE_PLACEMENT, 'Transmettre'],
            [Service::ASSURANCE,           Service::CONTROLE_PLACEMENT, 'Transmettre'],

            // Contrôle & Placement → Formalité
            [Service::CONTROLE_PLACEMENT,  Service::FORMALITE,          'Transmettre'],

            // Formalité → Direction for final validation / clôture
            [Service::FORMALITE,           Service::DIRECTION,          'Retour décision'],

            // ── Retours (renvois) ────────────────────────────────────────────
            [Service::SECRETARIAT,         Service::DIRECTION,          'Retourner'],
            [Service::LIQUIDATION,         Service::SECRETARIAT,        'Retourner'],
            [Service::COMPTABILITE,        Service::SECRETARIAT,        'Retourner'],
            [Service::ASSURANCE,           Service::SECRETARIAT,        'Retourner'],
            [Service::CONTROLE_PLACEMENT,  Service::SECRETARIAT,        'Retourner'],
            [Service::FORMALITE,           Service::CONTROLE_PLACEMENT, 'Retourner'],
        ];

        foreach ($transitions as [$sourceCode, $destCode, $action]) {
            $sourceId = $sourceCode ? Service::where('code', $sourceCode)->value('id') : null;
            $destId   = Service::where('code', $destCode)->value('id');

            if ($destId === null) {
                continue;
            }

            FluxTransition::updateOrCreate(
                [
                    'service_source_id'      => $sourceId,
                    'service_destination_id' => $destId,
                ],
                ['action' => $action]
            );
        }
    }
}
