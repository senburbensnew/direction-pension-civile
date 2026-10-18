<?php

namespace App\Support\Newsletter;

final class NewsletterEmailNormalizer
{
    public static function normalize(string $email): string
    {
        $email = trim(mb_strtolower($email));

        if ($email === '') {
            return '';
        }

        if (function_exists('idn_to_ascii')) {
            $parts = explode('@', $email, 2);
            if (count($parts) === 2) {
                $local = $parts[0];
                $domain = idn_to_ascii($parts[1], IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
                if ($domain !== false) {
                    $email = $local.'@'.$domain;
                }
            }
        }

        return $email;
    }
}
