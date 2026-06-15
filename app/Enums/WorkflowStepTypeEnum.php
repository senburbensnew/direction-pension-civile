<?php

namespace App\Enums;

enum WorkflowStepTypeEnum: string
{
    case INITIAL      = 'initial';
    case INTERMEDIAIRE = 'intermediaire';
    case TERMINAL     = 'terminal';

    public function label(): string
    {
        return match($this) {
            self::INITIAL       => 'Initial',
            self::INTERMEDIAIRE => 'Intermédiaire',
            self::TERMINAL      => 'Terminal',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::INITIAL       => 'badge-info',
            self::INTERMEDIAIRE => 'badge-warning',
            self::TERMINAL      => 'badge-neutral',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::TERMINAL;
    }

    public function isInitial(): bool
    {
        return $this === self::INITIAL;
    }
}
