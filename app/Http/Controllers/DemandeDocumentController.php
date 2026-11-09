<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDemandeDocumentRequest;
use App\Models\Demande;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DemandeDocumentController extends Controller
{
    public function store(StoreDemandeDocumentRequest $request, Demande $demande)
    {
        abort_if($demande->created_by !== auth()->id(), 403);

        foreach ($request->file('files') as $file) {
            $demande->addMedia($file)
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('supplemental', config('demandes.disk', 'public'));
        }

        return back()->with('success', 'Document(s) ajouté(s) avec succès.');
    }

    public function destroy(Media $media)
    {
        $demande = $media->model;
        abort_if(! $demande || $demande->created_by !== auth()->id(), 403);

        $media->delete();

        return back()->with('success', 'Document supprimé avec succès.');
    }
}
