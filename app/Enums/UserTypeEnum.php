<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case FONCTIONNAIRE = 'fonctionnaire';
    case PENSIONNE = 'pensionne';
    case INSTITUTION = 'institution';
    case PERSONNEL_DPC = 'personnel_dpc';
    case EXTERNE = 'externe';

    /**
     * Codes synchronisés avec user_categories (référentiel extensible).
     *
     * @return list<string>
     */
    public static function systemCodes(): array
    {
        return array_column(self::cases(), 'value');
    }
}