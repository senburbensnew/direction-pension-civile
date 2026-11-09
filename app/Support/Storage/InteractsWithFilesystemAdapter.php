<?php

namespace App\Support\Storage;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

trait InteractsWithFilesystemAdapter
{
    protected function filesystemAdapter(string $diskName): FilesystemAdapter
    {
        $disk = Storage::disk($diskName);

        if (! $disk instanceof FilesystemAdapter) {
            abort(500, 'Unsupported storage driver.');
        }

        return $disk;
    }

    protected function publicDiskAdapter(): FilesystemAdapter
    {
        return $this->filesystemAdapter(UploadPaths::publicDisk());
    }
}
