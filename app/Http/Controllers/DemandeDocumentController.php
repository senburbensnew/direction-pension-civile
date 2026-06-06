<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DemandeDocumentController extends Controller
{
    public function store(Request $request, Demande $demande)
    {
        abort_if($demande->created_by !== auth()->id(), 403);

        $request->validate([
            'files'   => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        foreach ($request->file('files') as $file) {
            $demande->addMedia($file)
                ->usingFileName($file->getClientOriginalName())
                ->toMediaCollection('supplemental', 'public');
        }

        return back()->with('success', 'Document(s) ajouté(s) avec succès.');
    }

    public function destroy(Media $media)
    {
        $demande = $media->model;
        abort_if(!$demande || $demande->created_by !== auth()->id(), 403);

        $media->delete();

        return back()->with('success', 'Document supprimé avec succès.');
    }
}
