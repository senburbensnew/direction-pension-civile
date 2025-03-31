<?php

namespace App\Enums;

enum RequestTypeEnum: string
{
    case BANK_TRANSFER_REQUEST;
    case CHECK_TRANSFER_REQUEST;
    case PAYMENT_STOP_REQUEST;
    case EXISTENCE_PROOF_REQUEST;
}