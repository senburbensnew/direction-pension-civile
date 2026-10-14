<?php

namespace App\Support\Storage;

final class CmsPathResolver
{
    /** @var list<string> */
    public const STATIC_PUBLIC_BASENAMES = [
        'france-flag-icon.svg',
        'haiti-flag-icon.svg',
        'ht-flag.PNG',
        'ht-flag.svg',
        'ht-flag.webp',
        'fr-flag.svg',
        'us-flag.svg',
        'image_placeholder.png',
        'image_placeholder.webp',
        'setting-logo-1-M13oPLiYoM.png',
        'setting-logo-1-M13oPLiYoM.webp',
    ];

    public static function legacyPathToStorage(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if (str_starts_with($path, 'cms/') || str_starts_with($path, 'storage/')) {
            return str_starts_with($path, 'storage/') ? substr($path, strlen('storage/')) : $path;
        }

        if (str_starts_with($path, 'images/carousel/')) {
            return UploadPaths::cms('carousel').'/'.substr($path, strlen('images/carousel/'));
        }

        if (str_starts_with($path, 'images/historique/')) {
            return UploadPaths::cms('institution').'/historique/'.substr($path, strlen('images/historique/'));
        }

        if (str_starts_with($path, 'images/logo_')) {
            return UploadPaths::cms('partenaires').'/'.substr($path, strlen('images/'));
        }

        if (str_starts_with($path, 'images/')) {
            $basename = substr($path, strlen('images/'));

            if (self::isStaticPublicBasename($basename)) {
                return $path;
            }

            if (self::isOfficialBasename($basename)) {
                return UploadPaths::cms('officials').'/'.$basename;
            }

            if (self::isInstitutionBasename($basename)) {
                return UploadPaths::cms('institution').'/'.$basename;
            }
        }

        if (str_starts_with($path, 'documents/')) {
            return UploadPaths::cms('publications').'/'.substr($path, strlen('documents/'));
        }

        if (str_starts_with($path, 'mediatheque/')) {
            return 'cms/'.$path;
        }

        if (str_starts_with($path, 'cms/mediatheque/images/')) {
            return UploadPaths::mediatheque('image').'/'.substr($path, strlen('cms/mediatheque/images/'));
        }

        if (str_starts_with($path, 'publications/')) {
            return UploadPaths::cms('publications').'/'.substr($path, strlen('publications/'));
        }

        if (str_starts_with($path, 'reports/')) {
            return UploadPaths::cms('reports').'/'.substr($path, strlen('reports/'));
        }

        if (str_starts_with($path, 'carousel/')) {
            return UploadPaths::cms('carousel').'/'.substr($path, strlen('carousel/'));
        }

        if (str_starts_with($path, 'officials/')) {
            return UploadPaths::cms('officials').'/'.substr($path, strlen('officials/'));
        }

        if (str_starts_with($path, 'partenaires/')) {
            return UploadPaths::cms('partenaires').'/'.substr($path, strlen('partenaires/'));
        }

        return $path;
    }

    public static function isStaticPublicPath(string $path): bool
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (! str_starts_with($path, 'images/')) {
            return false;
        }

        return self::isStaticPublicBasename(substr($path, strlen('images/')));
    }

    public static function isStaticPublicBasename(string $basename): bool
    {
        foreach (self::STATIC_PUBLIC_BASENAMES as $static) {
            if ($basename === $static) {
                return true;
            }
        }

        return str_contains($basename, 'flag-icon')
            || str_contains($basename, 'flag.svg')
            || str_contains($basename, 'setting-logo-1-M13oPLiYoM')
            || str_contains($basename, 'image_placeholder');
    }

    public static function storageTargetForPublicImageFile(string $filename): ?string
    {
        if (self::isStaticPublicBasename($filename)) {
            return null;
        }

        if (str_starts_with($filename, 'logo_')) {
            return UploadPaths::cms('partenaires').'/'.$filename;
        }

        if (self::isOfficialBasename($filename)) {
            return UploadPaths::cms('officials').'/'.$filename;
        }

        if (self::isInstitutionBasename($filename)) {
            return UploadPaths::cms('institution').'/'.$filename;
        }

        if (str_starts_with($filename, 'KEV_') || str_starts_with($filename, 'photo_')) {
            return UploadPaths::cms('carousel').'/'.$filename;
        }

        return null;
    }

    private static function isOfficialBasename(string $basename): bool
    {
        return (bool) preg_match(
            '/^(ministre|directrice|photo-metelus|PM-|presentation)/i',
            $basename
        );
    }

    private static function isInstitutionBasename(string $basename): bool
    {
        return (bool) preg_match(
            '/^(dpc_organigram|pension-hero|guest-auth|chart\.)/i',
            $basename
        );
    }
}
