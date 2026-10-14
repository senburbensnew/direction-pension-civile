<?php

namespace App\Support\Storage;

use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

trait StreamsStoredMedia
{
    protected function streamMediaFile(Media $media, bool $inline): Response
    {
        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($media->getPathRelativeToRoot()), 404);

        $filename = str_replace('"', '', $media->file_name);
        $disposition = $inline ? 'inline' : 'attachment';

        return response()->file(
            $disk->path($media->getPathRelativeToRoot()),
            [
                'Content-Type' => $media->mime_type ?: 'application/octet-stream',
                'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            ]
        );
    }

    protected function streamDiskPath(string $diskName, string $relativePath, string $downloadName, bool $inline): Response
    {
        $disk = Storage::disk($diskName);

        abort_unless($disk->exists($relativePath), 404);

        $filename = str_replace('"', '', $downloadName);
        $disposition = $inline ? 'inline' : 'attachment';

        return response()->file(
            $disk->path($relativePath),
            [
                'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            ]
        );
    }
}
