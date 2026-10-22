<?php

namespace App\Enums;

enum LoginTwoFactorChannelEnum: string
{
    case Email = 'email';
    case Sms = 'sms';
}
