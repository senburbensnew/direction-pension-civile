<?php

// app/Helpers/CodeGeneratorService.php
namespace App\Helpers;

use App\Models\BankTransferRequests;

class CodeGeneratorService
{
    public static function generateUniqueRequestCode(): string
    {
        $prefix = 'VIR-' . now()->format('Ymd') . '-';
        
        do {
            $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $code = $prefix . $random;
        } while (BankTransferRequests::where('code', $code)->exists());

        return $code;
    }
}