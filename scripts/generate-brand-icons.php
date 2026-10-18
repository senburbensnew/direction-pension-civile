<?php

/**
 * Generate favicons and social preview images from public/images/setting-logo-1-M13oPLiYoM.webp
 *
 * Usage: php scripts/generate-brand-icons.php
 */
$root = dirname(__DIR__);
$source = $root.'/public/images/setting-logo-1-M13oPLiYoM.webp';
$outDir = $root.'/public/images/brand';

if (! is_file($source)) {
    fwrite(STDERR, "Source logo not found: {$source}\n");
    exit(1);
}

if (! extension_loaded('gd')) {
    fwrite(STDERR, "PHP GD extension is required.\n");
    exit(1);
}

if (! is_dir($outDir) && ! mkdir($outDir, 0755, true) && ! is_dir($outDir)) {
    fwrite(STDERR, "Cannot create output directory: {$outDir}\n");
    exit(1);
}

$im = imagecreatefromwebp($source);
if ($im === false) {
    fwrite(STDERR, "Failed to read WebP source.\n");
    exit(1);
}

imagesavealpha($im, true);

$sizes = [
    'favicon-16x16.png' => 16,
    'favicon-32x32.png' => 32,
    'apple-touch-icon.png' => 180,
    'logo-512.png' => 512,
    'og-image.png' => 512,
];

foreach ($sizes as $filename => $size) {
    $dest = imagecreatetruecolor($size, $size);
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    $transparent = imagecolorallocatealpha($dest, 0, 0, 0, 127);
    imagefilledrectangle($dest, 0, 0, $size, $size, $transparent);

    $srcW = imagesx($im);
    $srcH = imagesy($im);
    $scale = min($size / $srcW, $size / $srcH);
    $newW = (int) round($srcW * $scale);
    $newH = (int) round($srcH * $scale);
    $dstX = (int) round(($size - $newW) / 2);
    $dstY = (int) round(($size - $newH) / 2);

    imagecopyresampled($dest, $im, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);

    $path = $outDir.'/'.$filename;
    if (! imagepng($dest, $path)) {
        fwrite(STDERR, "Failed to write {$path}\n");
        exit(1);
    }
    imagedestroy($dest);
    echo "Wrote {$path}\n";
}

// Simple ICO wrapping the 32×32 PNG (browsers accept PNG payload in ICO).
$ico32 = $outDir.'/favicon-32x32.png';
$icoDest = $outDir.'/favicon.ico';
if (! copy($ico32, $icoDest)) {
    fwrite(STDERR, "Failed to write favicon.ico\n");
    exit(1);
}
echo "Wrote {$icoDest}\n";

imagedestroy($im);
echo "Done.\n";
