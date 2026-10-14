<?php

namespace App\Http\Controllers\Formalites;

use App\Http\Controllers\Controller;
use App\Models\BeneficiaireRelationDemande;
use App\Models\PensionMandat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\Response;

class BeneficiaireRepresentationPieceController extends Controller
{
    public function mandatPiece(
        Request $request,
        PensionMandat $mandat,
        Media $media,
    ): Response {
        $this->authorizeFormalitesPieceAccess($request);

        abort_unless(
            $media->model_type === PensionMandat::class
            && (int) $media->model_id === (int) $mandat->id,
            404
        );

        return $this->streamMedia($media, $request->boolean('inline'));
    }

    public function relationPiece(
        Request $request,
        BeneficiaireRelationDemande $demande,
        Media $media,
    ): Response {
        $this->authorizeFormalitesPieceAccess($request);

        abort_unless(
            $media->model_type === BeneficiaireRelationDemande::class
            && (int) $media->model_id === (int) $demande->id,
            404
        );

        return $this->streamMedia($media, $request->boolean('inline'));
    }

    private function authorizeFormalitesPieceAccess(Request $request): void
    {
        $user = $request->user();

        abort_unless($user && (
            $user->hasRole('admin')
            || $user->can('pension_mandats.view')
            || $user->can('beneficiaire_relations.view')
        ), 403);
    }

    private function streamMedia(Media $media, bool $inline): Response
    {
        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($media->getPathRelativeToRoot()), 404);

        $headers = $inline
            ? ['Content-Disposition' => 'inline; filename="'.$media->file_name.'"']
            : [];

        return response()->file($disk->path($media->getPathRelativeToRoot()), $headers);
    }
}
