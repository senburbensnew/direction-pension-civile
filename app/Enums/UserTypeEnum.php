<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case FONCTIONNAIRE = 'fonctionnaire';
    case PENSIONNE = 'pensionne';
    case INSTITUTION = 'institution';
    case EXTERNE = 'externe';
}