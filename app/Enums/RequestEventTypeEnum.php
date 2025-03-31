<?php

namespace App\Enums;

enum RequestEventTypeEnum: string
{
    case REQUEST_CREATED;
    case REQUEST_EDITED;
    case REQUEST_DELETED;
}