<?php

namespace App\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;

class PersonalDemandeCatalog
{
    /**
     * Types affichés sur le tableau de bord usager, par profil.
     *
     * @return array<string, list<array{type: string, enum: TypeDemandeEnum, label: string}>>
     */
    public function sections(): array
    {
        return [
            'pensionne' => [
                $this->entry('bankTransferRequest', TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE),
                $this->entry('certificateRequest', TypeDemandeEnum::DEMANDE_ATTESTATION),
                $this->entry('checkTransferRequest', TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE),
                $this->entry('paymentStopRequest', TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT),
                $this->entry('reinstateRequest', TypeDemandeEnum::DEMANDE_REINSERTION),
                $this->entry('transferStopRequest', TypeDemandeEnum::DEMANDE_ARRET_VIREMENT),
                $this->entry('existenceProofRequest', TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE),
                $this->entry('informationUpdateRequest', TypeDemandeEnum::DEMANDE_MISE_A_JOUR),
                $this->entry('reversionaryPensionRequest', TypeDemandeEnum::DEMANDE_PENSION_REVERSION),
            ],
            'fonctionnaire' => [
                $this->entry('careerStateRequest', TypeDemandeEnum::DEMANDE_ETAT_CARRIERE),
                $this->entry('pensionRequest', TypeDemandeEnum::DEMANDE_PENSION),
            ],
            'institution' => [
                $this->entry('adhesionRequest', TypeDemandeEnum::DEMANDE_ADHESION),
            ],
        ];
    }

    public function find(string $requestType): ?array
    {
        foreach ($this->sections() as $entries) {
            foreach ($entries as $entry) {
                if ($entry['type'] === $requestType) {
                    return $entry;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, list<array{type: string, label: string, count: int}>>
     */
    public function statsCardsForUser(): array
    {
        $stats = [];

        foreach ($this->sections() as $section => $entries) {
            $stats[$section] = [];
            foreach ($entries as $entry) {
                $stats[$section][] = [
                    'type' => $entry['type'],
                    'label' => $entry['label'],
                    'count' => Demande::forUser()->ofType($entry['enum']->value)->count(),
                ];
            }
        }

        return $stats;
    }

    /**
     * @return array<string, array{enum: TypeDemandeEnum, label: string}>
     */
    public function corbeilleTypeMap(): array
    {
        $map = [];
        foreach ($this->sections() as $entries) {
            foreach ($entries as $entry) {
                $map[$entry['type']] = [
                    'enum' => $entry['enum'],
                    'label' => $entry['label'],
                ];
            }
        }

        $map['rencontreRequest'] = [
            'enum' => TypeDemandeEnum::DEMANDE_RENCONTRE,
            'label' => 'Attribution de rendez-vous',
        ];
        $map['accountCreationRequest'] = [
            'enum' => TypeDemandeEnum::DEMANDE_CREATION_COMPTE,
            'label' => 'Demande de création de compte',
        ];

        return $map;
    }

    private function entry(string $type, TypeDemandeEnum $enum): array
    {
        return [
            'type' => $type,
            'enum' => $enum,
            'label' => $enum->label(),
        ];
    }
}
