<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Support\Storage\StreamsStoredMedia;
use App\Support\Storage\UploadPaths;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class DemandeMediaController extends Controller
{
    use StreamsStoredMedia;

    public function media(Request $request, Demande $demande, Media $media): Response
    {
        $this->authorizeDemandeFiles($demande);

        abort_unless(
            $media->model_type === Demande::class
            && (int) $media->model_id === (int) $demande->id,
            404
        );

        return $this->streamMediaFile($media, $request->boolean('inline'));
    }

    public function embedded(Request $request, Demande $demande): Response
    {
        $this->authorizeDemandeFiles($demande);

        $encoded = (string) $request->query('path', '');
        $path = base64_decode(strtr($encoded, '-_', '+/'), true);

        abort_unless(is_string($path) && $path !== '' && $demande->dataContainsStoragePath($path), 404);

        $diskName = $this->resolveEmbeddedDisk($path);

        return $this->streamDiskPath(
            $diskName,
            $path,
            basename($path),
            $request->boolean('inline')
        );
    }

    private function authorizeDemandeFiles(Demande $demande): void
    {
        $user = auth()->user();

        abort_unless($user, 403);

        if ($user->can('view', $demande) || $user->can('access', $demande)) {
            return;
        }

        abort(403);
    }

    private function resolveEmbeddedDisk(string $path): string
    {
        foreach ([UploadPaths::demandesDisk(), UploadPaths::publicDisk(), 'public'] as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return $disk;
            }
        }

        abort(404);
    }
}
