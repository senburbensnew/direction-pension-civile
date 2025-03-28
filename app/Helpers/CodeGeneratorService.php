<?php

// app/Helpers/CodeGeneratorService.php
namespace App\Helpers;

use App\Models\BankTransferRequests;
use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
    public static function generateUniqueRequestCode($pref, $tableName): string
    {
        $prefix = $pref . '-' . now()->format('Ymd') . '-';
        
        do {
            $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $code = $prefix . $random;
        } while (DB::table($tableName)->where('code', $code)->exists());
    
        return $code;
    }
}