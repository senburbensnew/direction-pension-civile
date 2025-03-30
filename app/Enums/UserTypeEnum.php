<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case FONCTIONNAIRE = 'fonctionnaire';
    case PENSIONNAIRE = 'pensionnaire';
    case INSTITUTION = 'institution';
}