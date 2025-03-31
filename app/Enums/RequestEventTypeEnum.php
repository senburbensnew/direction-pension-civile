<?php

namespace App\Enums;

enum RequestEventTypeEnum: string
{
    case REQUEST_CREATED = 'REQUEST_CREATED';
    case REQUEST_EDITED = 'REQUEST_EDITED';
    case REQUEST_DELETED = 'REQUEST_DELETED';
}