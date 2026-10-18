<?php

namespace App\Support\Newsletter;

final class NewsletterHtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><em><u><s><h2><h3><ul><ol><li><blockquote><a><span><img>';

    public static function sanitize(?string $html): ?string
    {
        if ($html === null || trim(strip_tags($html)) === '') {
            return null;
        }

        return strip_tags($html, self::ALLOWED_TAGS);
    }
}
