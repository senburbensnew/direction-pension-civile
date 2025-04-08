<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Helpers
{
    public static function cleanupFiles(array $paths)
    {
        foreach ($paths as $path) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Exception $e) {
                \Log::error("File cleanup failed: {$path} - " . $e->getMessage());
            }
        }
    }

    public static function processBase64Image(
        $base64Data,
        $folder,
        $errorMessage,
        $maxSize = 5242880, // 5MB
        $disk = 'public'
    ) {
        try {
            // Validate base64 format
            if (!preg_match('/^data:image\/.*;base64,/', $base64Data)) {
                throw new \Exception('Invalid base64 image format');
            }
    
            // Extract and decode image data
            $imageData = base64_decode(preg_replace('/^data:image\/.*;base64,/', '', $base64Data));
            if ($imageData === false) {
                throw new \Exception('Base64 decoding failed');
            }
    
            // Validate image size
            if (strlen($imageData) > $maxSize) {
                throw new \Exception("Image exceeds maximum size of " . round($maxSize / 1024 / 1024, 2) . "MB");
            }
    
            // Detect MIME type
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageData);
    
            // Validate MIME type and get extension (only PNG allowed)
            $allowedMimeTypes = [
                'image/png' => 'png',
            ];
            
            if (!isset($allowedMimeTypes[$mimeType])) {
                throw new \Exception('Unsupported image type: ' . $mimeType);
            }
            $extension = $allowedMimeTypes[$mimeType];
    
            // Sanitize folder path
            $folder = ltrim($folder, '/');
            $folderParts = explode('/', $folder);
            $filteredParts = array_filter($folderParts, function ($part) {
                return !in_array($part, ['', '.', '..']);
            });
            $folder = implode('/', $filteredParts);
    
            // Generate filename and save
            $filename = $folder . '/' . Str::uuid() . '.' . $extension;
            Storage::disk($disk)->put($filename, $imageData);
    
            return $filename;
    
        } catch (\Exception $e) {
            throw new \Exception("$errorMessage: " . $e->getMessage());
        }
    }
}