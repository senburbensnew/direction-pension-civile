<?php

namespace App\Enums;

enum CategorieDossierEnum: string
{
    case DEMANDES_PENSION    = 'pension';
    case DOSSIERS_URGENTS    = 'urgent';
    case PRESTATIONS         = 'prestations';
    case ADMINISTRATIF       = 'administratif';
    case CORRESPONDANCES     = 'correspondances';
    case DEMANDE_RENCONTRE   = 'rencontre';
    case AUTRES              = 'autres';

    public function label(): string
    {
        return match($this) {
            self::DEMANDES_PENSION  => 'Demandes de pension',
            self::DOSSIERS_URGENTS  => 'Dossiers urgents',
            self::PRESTATIONS       => 'Demandes de prestations',
            self::ADMINISTRATIF     => 'Dossiers administratifs',
            self::CORRESPONDANCES   => 'Correspondances',
            self::DEMANDE_RENCONTRE => 'Demandes de rencontre',
            self::AUTRES            => 'Autres',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DEMANDES_PENSION  => 'badge-primary',
            self::DOSSIERS_URGENTS  => 'badge-error',
            self::PRESTATIONS       => 'badge-info',
            self::ADMINISTRATIF     => 'badge-warning',
            self::CORRESPONDANCES   => 'badge-secondary',
            self::DEMANDE_RENCONTRE => 'badge-success',
            self::AUTRES            => 'badge-neutral',
        };
    }
}
