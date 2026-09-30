<?php

namespace App\Enums;

enum RencontreStatutEnum: string
{
    case DEMANDE = 'demande';
    case EN_COURS = 'en_cours';
    case ATTRIBUE = 'attribue';
    case ACTIF = 'actif';
    case REFUSE = 'refuse';
    case REALISE = 'realise';
    case ANNULE = 'annule';
    case REPORTE = 'reporte';
    case NON_HONORE = 'non_honore';

    public function label(): string
    {
        return match ($this) {
            self::DEMANDE => 'Demandé',
            self::EN_COURS => 'En cours de traitement',
            self::ATTRIBUE => 'Attribué',
            self::ACTIF => 'Actif',
            self::REFUSE => 'Refusé',
            self::REALISE => 'Réalisé',
            self::ANNULE => 'Annulé',
            self::REPORTE => 'Reporté',
            self::NON_HONORE => 'Non honoré',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DEMANDE => 'bg-gray-100 text-gray-700',
            self::EN_COURS => 'bg-purple-100 text-purple-800',
            self::ATTRIBUE => 'bg-yellow-100 text-yellow-800',
            self::ACTIF => 'bg-blue-100 text-blue-800',
            self::REFUSE => 'bg-red-100 text-red-800',
            self::REALISE => 'bg-green-100 text-green-800',
            self::ANNULE => 'bg-red-100 text-red-800',
            self::REPORTE => 'bg-orange-100 text-orange-800',
            self::NON_HONORE => 'bg-slate-200 text-slate-700',
        };
    }

    /**
     * Code de l'étape workflow propre au type DEMANDE_RENCONTRE.
     */
    public function workflowCode(): string
    {
        return match ($this) {
            self::DEMANDE => 'SOUMISE',
            self::EN_COURS => 'EN_COURS',
            self::ATTRIBUE => 'ATTRIBUE',
            self::ACTIF => 'ACTIF',
            self::REFUSE => 'REFUSE',
            self::REALISE => 'REALISE',
            self::ANNULE => 'ANNULE',
            self::REPORTE => 'REPORTE',
            self::NON_HONORE => 'NON_HONORE',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::REFUSE,
            self::REALISE,
            self::ANNULE,
            self::NON_HONORE,
        ], true);
    }

    public function reservesSlot(): bool
    {
        return ! in_array($this, [
            self::REFUSE,
            self::ANNULE,
            self::REALISE,
            self::NON_HONORE,
        ], true);
    }

    /** @return list<string> */
    public static function terminalValues(): array
    {
        return [
            self::REFUSE->value,
            self::REALISE->value,
            self::ANNULE->value,
            self::NON_HONORE->value,
        ];
    }

    public static function fromWorkflow(?string $code): self
    {
        return match ($code) {
            'EN_COURS' => self::EN_COURS,
            'ATTRIBUE', 'EN_ATTENTE' => self::ATTRIBUE,
            'ACTIF' => self::ACTIF,
            'REFUSE', 'REJETEE' => self::REFUSE,
            'REALISE', 'FINALISEE' => self::REALISE,
            'ANNULE', 'ANNULEE' => self::ANNULE,
            'REPORTE' => self::REPORTE,
            'NON_HONORE' => self::NON_HONORE,
            default => self::DEMANDE,
        };
    }
}