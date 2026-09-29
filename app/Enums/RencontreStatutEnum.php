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

    /**
     * Libellé affiché à l'utilisateur.
     */
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

    /**
     * Classe CSS du badge.
     */
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
     * Code correspondant au workflow général du dossier.
     */
    public function workflowCode(): string
    {
        return match ($this) {
            self::DEMANDE => 'SOUMISE',
            self::EN_COURS => 'EN_COURS',
            self::ATTRIBUE => 'EN_ATTENTE',
            self::ACTIF => 'ACTIF',
            self::REFUSE => 'REJETEE',
            self::REALISE => 'FINALISEE',
            self::ANNULE => 'ANNULEE',
            self::REPORTE => 'EN_ATTENTE',
            self::NON_HONORE => 'FINALISEE',
        };
    }

    /**
     * Indique si le statut est terminal.
     */
    public function isTerminal(): bool
    {
        return in_array(
            $this,
            [
                self::REFUSE,
                self::REALISE,
                self::ANNULE,
                self::NON_HONORE,
            ],
            true
        );
    }

    /**
     * Indique si le créneau de rendez-vous doit rester réservé.
     */
    public function reservesSlot(): bool
    {
        return ! in_array(
            $this,
            [
                self::REFUSE,
                self::ANNULE,
                self::REALISE,
                self::NON_HONORE,
            ],
            true
        );
    }

    /**
     * Retourne les valeurs des statuts terminaux.
     *
     * @return list<string>
     */
    public static function terminalValues(): array
    {
        return [
            self::REFUSE->value,
            self::REALISE->value,
            self::ANNULE->value,
            self::NON_HONORE->value,
        ];
    }

    /**
     * Convertit un code du workflow général
     * en statut spécifique à une rencontre.
     */
    public static function fromWorkflow(?string $code): self
    {
        return match ($code) {
            'EN_COURS' => self::EN_COURS,
            'EN_ATTENTE' => self::ATTRIBUE,
            'ACTIF' => self::ACTIF,
            'REJETEE' => self::REFUSE,
            'FINALISEE' => self::REALISE,
            'ANNULEE' => self::ANNULE,

            default => self::DEMANDE,
        };
    }
}