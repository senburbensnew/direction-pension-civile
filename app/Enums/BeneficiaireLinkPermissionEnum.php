<?php

namespace App\Enums;

enum BeneficiaireLinkPermissionEnum: string
{
    case CONSULTER_DOSSIER = 'consulter_dossier';
    case DEPOSER_DOCUMENTS = 'deposer_documents';
    case SOUMETTRE_DEMANDE = 'soumettre_demande';

    public function label(): string
    {
        return match ($this) {
            self::CONSULTER_DOSSIER => 'Consulter le dossier',
            self::DEPOSER_DOCUMENTS => 'Déposer des documents',
            self::SOUMETTRE_DEMANDE => 'Soumettre une demande',
        };
    }

    /**
     * @return list<string>
     */
    public static function defaultGrant(): array
    {
        return array_column(self::cases(), 'value');
    }
}
