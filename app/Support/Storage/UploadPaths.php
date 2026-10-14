<?php

namespace App\Support\Storage;

use Illuminate\Support\Str;

final class UploadPaths
{
    public static function publicDisk(): string
    {
        return (string) config('uploads.disks.public', 'public');
    }

    public static function privateDisk(): string
    {
        return (string) config('uploads.disks.private', 'private');
    }

    public static function demandesDisk(): string
    {
        return (string) config('uploads.disks.demandes', 'public');
    }

    public static function comptesDisk(): string
    {
        return (string) config('uploads.disks.comptes', 'private');
    }

    public static function cms(string $area): string
    {
        return (string) config("uploads.paths.cms.{$area}");
    }

    public static function mediatheque(string $type): string
    {
        return self::cms('mediatheque').'/'.trim($type, '/');
    }

    public static function userProfilePhoto(): string
    {
        return (string) config('uploads.paths.users.profile_photo');
    }

    public static function reportYear(?string $year): string
    {
        $year = $year ?: 'unspecified';

        return self::cms('reports').'/'.$year;
    }

    public static function formStashRoot(): string
    {
        return (string) config('uploads.paths.temp.form_stash');
    }

    public static function secretsRoot(): string
    {
        return (string) config('uploads.paths.secrets');
    }

    public static function demandeEmbeddedPath(string $type): string
    {
        $template = (string) config('uploads.paths.demandes.embedded');

        return str_replace(
            ['{type}', '{year}', '{month}'],
            [Str::slug($type), now()->format('Y'), now()->format('m')],
            $template
        );
    }
}
