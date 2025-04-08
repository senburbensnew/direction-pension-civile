<?php

namespace App\Helpers;

class RegexExpressions
{
    private static $rules = [
        'nif' => '/^\d{3}-\d{3}-\d{3}-\d{1}$/',
        'ninu' => '/^\d{3}-\d{3}-\d{3}-\d{1}$/',
        'id_number' => '/^\d{3}-\d{3}-\d{3}-\d{1}$|^\d{3}-\d{2}-\d{4}-\d{2}$/',
        'phone_number' => '/^(?:\+509)?(?:2[2-9]\d{5}|[34]\d{6}|8\d{6})$/',
        'pension_code' => '/^[A-Za-z0-9\-]{5,20}$/',
        'fiscal_year' => '/^20\d{2}\/20\d{2}$/',
        'account_number' => ''
    ];

    // Static method for NIF regex
    public static function nif(): string 
    {
        return self::$rules['nif'];
    }

    // Static method for Pension Code regex
    public static function pensionCode(): string 
    {
        return self::$rules['pension_code'];
    }

    // Optional: Add methods for other patterns if needed
    public static function ninu(): string 
    {
        return self::$rules['ninu'];
    }

    public static function phoneNumber(): string 
    {
        return self::$rules['phone_number'];
    }

    public static function fiscalYear(): string 
    {
        return self::$rules['fiscal_year'];
    }

    public static function accountNumber(): string 
    {
        return self::$rules['account_number'];
    }
}