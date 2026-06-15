<?php

namespace Database\Seeders;

use App\Enums\WorkflowStepTypeEnum;
use App\Models\Service;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class WorkflowStepSeeder extends Seeder
{
    public function run(): void
    {
        $steps = [
            // ── Nœuds initiaux ──
            [
                'code'        => 'BROUILLON',
                'nom'         => 'Brouillon',
                'description' => 'Dossier en cours de préparation par l\'usager, non encore soumis',
                'service'     => null,
                'ordre'       => 1,
                'type_noeud'  => WorkflowStepTypeEnum::INITIAL,
            ],
            [
                'code'        => 'SOUMISE',
                'nom'         => 'Soumission initiale — Direction',
                'description' => 'Dossier soumis et pris en charge par la Direction (réception et décision finale)',
                'service'     => 'direction',
                'ordre'       => 5,
                'type_noeud'  => WorkflowStepTypeEnum::INITIAL,
            ],
            // ── Nœuds intermédiaires (par service) ──
            [
                'code'        => 'ACCUEIL_RECEPTION',
                'nom'         => 'Accueil et réception du dossier',
                'description' => 'Premier contact, enregistrement et orientation du demandeur',
                'service'     => 'reception',
                'ordre'       => 10,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'ENREGISTREMENT_SECRETARIAT',
                'nom'         => 'Enregistrement au secrétariat',
                'description' => 'Archivage administratif et numérotation du dossier',
                'service'     => 'secretariat',
                'ordre'       => 20,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            // Étape canonique du circuit par défaut (secrétariat)
            [
                'code'        => 'EN_INSTRUCTION_SECRETARIAT',
                'nom'         => 'Instruction au secrétariat',
                'description' => 'Dossier en cours d\'instruction par le secrétariat',
                'service'     => 'secretariat',
                'ordre'       => 25,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'VERIFICATION_FORMALITES',
                'nom'         => 'Vérification des formalités',
                'description' => 'Contrôle de la conformité et complétude des pièces justificatives',
                'service'     => 'service_formalite',
                'ordre'       => 30,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'INSTRUCTION_LIQUIDATION',
                'nom'         => 'Instruction — liquidation de pension',
                'description' => 'Calcul des droits, vérification des années de service et liquidation',
                'service'     => 'service_liquidation',
                'ordre'       => 40,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'CONTROLE_PLACEMENT',
                'nom'         => 'Contrôle et placement',
                'description' => 'Contrôle des calculs et placement du dossier pour signature',
                'service'     => 'service_controle_placement',
                'ordre'       => 50,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'ORDONNANCEMENT_COMPTABILITE',
                'nom'         => 'Ordonnancement comptable',
                'description' => 'Émission du titre de paiement et ordonnancement',
                'service'     => 'service_comptabilite',
                'ordre'       => 70,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'TRAITEMENT_ASSURANCE',
                'nom'         => 'Traitement assurance',
                'description' => 'Vérification des couvertures et enregistrement assurance',
                'service'     => 'service_assurance',
                'ordre'       => 80,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'ADMINISTRATION_INTERNE',
                'nom'         => 'Administration interne',
                'description' => 'Traitement administratif interne',
                'service'     => 'cellule_administration',
                'ordre'       => 90,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            // Étape canonique du circuit par défaut (décision direction)
            [
                'code'        => 'EN_DECISION',
                'nom'         => 'Soumis à la décision',
                'description' => 'Dossier soumis à la Direction pour décision finale',
                'service'     => 'direction',
                'ordre'       => 98,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            [
                'code'        => 'DECISION_FINALE',
                'nom'         => 'Décision finale',
                'description' => 'Approbation ou rejet définitif du dossier par la Direction',
                'service'     => 'direction',
                'ordre'       => 100,
                'type_noeud'  => WorkflowStepTypeEnum::INTERMEDIAIRE,
            ],
            // ── Nœuds terminaux (Direction) ──
            [
                'code'        => 'APPROUVEE',
                'nom'         => 'Approuvée',
                'description' => 'Dossier approuvé par la Direction',
                'service'     => 'direction',
                'ordre'       => 110,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
            [
                'code'        => 'FINALISEE',
                'nom'         => 'Clôturée',
                'description' => 'Dossier finalisé et clôturé par la Direction',
                'service'     => 'direction',
                'ordre'       => 120,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
            [
                'code'        => 'REJETEE',
                'nom'         => 'Rejetée',
                'description' => 'Dossier rejeté par la Direction',
                'service'     => 'direction',
                'ordre'       => 130,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
            [
                'code'        => 'ANNULEE',
                'nom'         => 'Annulée',
                'description' => 'Dossier annulé par la Direction',
                'service'     => 'direction',
                'ordre'       => 140,
                'type_noeud'  => WorkflowStepTypeEnum::TERMINAL,
            ],
        ];

        foreach ($steps as $step) {
            $serviceId = $step['service']
                ? Service::where('code', $step['service'])->value('id')
                : null;

            WorkflowStep::updateOrCreate(
                ['code' => $step['code']],
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
