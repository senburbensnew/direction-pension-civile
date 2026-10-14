<?php

namespace App\Support\Storage;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator;

class StructuredMediaPathGenerator extends DefaultPathGenerator
{
    protected function getBasePath(Media $media): string
    {
        $map = config('uploads.media_folders', []);
        $folder = $map[$media->model_type]
            ?? Str::kebab(Str::plural(class_basename($media->model_type)));

        return sprintf(
            'media/%s/%s/%s/%s',
            $folder,
            $media->model_id,
            $media->collection_name,
            $media->getKey()
        );
    }
}
