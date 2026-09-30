<?php

namespace Database\Seeders\WorkflowSteps;

use App\Enums\WorkflowStepTypeEnum;
use App\Models\Service;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class RencontreWorkflowStepSeeder extends Seeder
{
    public function run(): void
    {
        $type = 'DEMANDE_RENCONTRE';

        $steps = [
            [
                'code'        => 'SOUMISE',
                'nom'         => 'Soumission',
                'description' => 'Demande de rendez-vous enregistrée',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 10,
                'type_noeud'  => WorkflowStepTypeEnum::INITIAL,
            ],
            [
                'code'        => 'ATTRIBUE',
                'nom'         => 'Attribué à un agent',
                'description' => 'Rendez-vous attribué à un agent du service responsable',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 20,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'ACTIF',
                'nom'         => 'Rendez-vous actif',
                'description' => 'Rendez-vous confirmé, en attente de réalisation',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 30,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'REPORTE',
                'nom'         => 'Rendez-vous reporté',
                'description' => 'Rendez-vous reporté à une date ultérieure',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 40,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'REFUSE',
                'nom'         => 'Refusé',
                'description' => 'Demande de rendez-vous refusée',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 90,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
            [
                'code'        => 'REALISE',
                'nom'         => 'Réalisé',
                'description' => 'Rendez-vous réalisé',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 91,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
            [
                'code'        => 'ANNULE',
                'nom'         => 'Annulé',
                'description' => 'Rendez-vous annulé',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 92,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
            [
                'code'        => 'NON_HONORE',
                'nom'         => 'Non honoré',
                'description' => 'Rendez-vous non honoré par le demandeur',
                'service'     => 'service_accueil_formalites',
                'ordre'       => 93,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
        ];

        foreach ($steps as $step) {
            $serviceId = $step['service']
                ? Service::where('code', $step['service'])->value('id')
                : null;

            WorkflowStep::updateOrCreate(
                ['code' => $step['code'], 'type_demande' => $type],
                [
                    'nom'         => $step['nom'],
                    'description' => $step['description'],
                    'service_id'  => $serviceId,
                    'ordre'       => $step['ordre'],
                    'type_noeud'  => $step['type_noeud'],
                ]
            );
        }
    }
}