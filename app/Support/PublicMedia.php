<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicMedia
{
    /**
     * Path/string suitable for the optimized-image component `src` prop.
     */
    public static function componentSrc(?string $pathOrUrl, string $fallback = 'images/image_placeholder.png'): string
    {
        if ($pathOrUrl === null || $pathOrUrl === '') {
            return $fallback;
        }

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        $path = ltrim(str_replace('\\', '/', $pathOrUrl), '/');

        if (str_starts_with($path, 'images/') || str_starts_with($path, 'storage/') || str_starts_with($path, 'media/')) {
            return $path;
        }

        return 'storage/'.$path;
    }

    /**
     * @param  array{url: string, webp: ?string}  $sources
     */
    public static function preferredUrl(array $sources): string
    {
        return $sources['webp'] ?? $sources['url'];
    }

    /**
     * @return array{
     *     url: string,
     *     webp: ?string,
     *     width: ?int,
     *     height: ?int,
     *     srcset: ?string,
     *     webp_srcset: ?string
     * }
     */
    public static function resolve(string $src): array
    {
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return self::emptySources($src);
        }

        return self::forPublicPath(self::componentSrc($src));
    }

    /**
     * @return array{
     *     url: string,
     *     webp: ?string,
     *     width: ?int,
     *     height: ?int,
     *     srcset: ?string,
     *     webp_srcset: ?string
     * }
     */
    public static function forPublicPath(string $path): array
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');

        if (str_starts_with($path, 'storage/')) {
            return self::forStorageRelative(substr($path, strlen('storage/')));
        }

        if (str_starts_with($path, 'media/')) {
            return self::resolveFilesystemPath(public_path($path), '/'.$path);
        }

        return self::resolveFilesystemPath(public_path($path), asset($path));
    }

    /**
     * @return array{
     *     url: string,
     *     webp: ?string,
     *     width: ?int,
     *     height: ?int,
     *     srcset: ?string,
     *     webp_srcset: ?string
     * }
     */
    public static function forUploadedOrStatic(string $relative): array
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');

        if (str_starts_with($relative, 'images/')) {
            return self::forPublicPath($relative);
        }

        return self::forStorageRelative($relative);
    }

    /**
     * @return array{
     *     url: string,
     *     webp: ?string,
     *     width: ?int,
     *     height: ?int,
     *     srcset: ?string,
     *     webp_srcset: ?string
     * }
     */
    public static function forStorageRelative(string $relative): array
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        $disk = Storage::disk('public');

        if (! $disk->exists($relative)) {
            return self::emptySources(asset('storage/'.$relative));
        }

        $absolute = $disk->path($relative);
        $webpRelative = self::webpSiblingPath($relative);

        return self::resolveFilesystemPath(
            $absolute,
            asset('storage/'.$relative),
            $disk->exists($webpRelative) ? asset('storage/'.$webpRelative) : null,
        );
    }

    public static function webpSiblingPath(string $path): string
    {
        return preg_replace('/\.(jpe?g|png|gif)$/i', '.webp', $path) ?? $path;
    }

    public static function deleteWebpSibling(string $storageRelative): void
    {
        $webp = self::webpSiblingPath(ltrim($storageRelative, '/'));
        Storage::disk('public')->delete($webp);
    }

    /**
     * @return array{
     *     url: string,
     *     webp: ?string,
     *     width: ?int,
     *     height: ?int,
     *     srcset: ?string,
     *     webp_srcset: ?string
     * }
     */
    private static function emptySources(string $url): array
    {
        return [
            'url' => $url,
            'webp' => null,
            'width' => null,
            'height' => null,
            'srcset' => null,
            'webp_srcset' => null,
        ];
    }

    /**
     * @return array{
     *     url: string,
     *     webp: ?string,
     *     width: ?int,
     *     height: ?int,
     *     srcset: ?string,
     *     webp_srcset: ?string
     * }
     */
    private static function resolveFilesystemPath(string $absolute, string $url, ?string $webpUrl = null): array
    {
        $webp = $webpUrl;
        if ($webp === null && is_file($absolute)) {
            $webpPath = self::webpSiblingPath($absolute);
            if (is_file($webpPath)) {
                $relativeWebp = ltrim(str_replace('\\', '/', substr($webpPath, strlen(public_path()))), '/');
                $webp = asset($relativeWebp);
            }
        }

        $width = null;
        $height = null;
        if (is_file($absolute)) {
            $size = @getimagesize($absolute);
            if (is_array($size)) {
                $width = (int) $size[0];
                $height = (int) $size[1];
            }
        }

        [$srcset, $webpSrcset] = self::buildSrcsets($absolute, $url, $webp);

        return [
            'url' => $url,
            'webp' => $webp,
            'width' => $width,
            'height' => $height,
            'srcset' => $srcset,
            'webp_srcset' => $webpSrcset,
        ];
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private static function buildSrcsets(string $absolute, string $defaultUrl, ?string $defaultWebpUrl): array
    {
        if (! is_file($absolute)) {
            return [null, null];
        }

        $dir = dirname($absolute);
        $filename = pathinfo($absolute, PATHINFO_FILENAME);
        $extension = pathinfo($absolute, PATHINFO_EXTENSION);

        $fallbackEntries = [];
        $webpEntries = [];

        $mainWidth = @getimagesize($absolute)[0] ?? 0;
        if ($mainWidth > 0) {
            $fallbackEntries[] = $defaultUrl.' '.$mainWidth.'w';
            if ($defaultWebpUrl) {
                $webpEntries[] = $defaultWebpUrl.' '.$mainWidth.'w';
            }
        }

        foreach (config('media.responsive_widths', [480, 960, 1600]) as $targetWidth) {
            $variantFile = $dir.DIRECTORY_SEPARATOR.$filename.'-w'.$targetWidth.'.'.$extension;
            if (! is_file($variantFile)) {
                continue;
            }

            $variantUrl = self::responsiveUrl($defaultUrl, $targetWidth);
            $fallbackEntries[] = $variantUrl.' '.$targetWidth.'w';

            if ($defaultWebpUrl) {
                $webpEntries[] = self::responsiveUrl($defaultWebpUrl, $targetWidth).' '.$targetWidth.'w';
            }
        }

        $srcset = count($fallbackEntries) > 1 ? implode(', ', $fallbackEntries) : null;
        $webpSrcset = count($webpEntries) > 1 ? implode(', ', $webpEntries) : null;

        return [$srcset, $webpSrcset];
    }

    private static function responsiveUrl(string $url, int $width): string
    {
        return preg_replace('/(\.[^.\/?#]+)(?=$|\?)/', '-w'.$width.'$1', $url, 1) ?? $url;
    }
}
