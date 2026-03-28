<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\DemandeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DemandeDocumentController extends Controller
{
    public function store(Request $request, Demande $demande)
    {
        abort_if($demande->created_by !== auth()->id(), 403);

        $request->validate([
            'files'   => 'required|array|min:1|max:10',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
            'label'   => 'nullable|string|max:255',
        ]);

        $disk  = 'public';
        $label = $request->input('label', 'Document supplémentaire');

        foreach ($request->file('files') as $file) {
            $path = $file->store('demandes/supplemental/' . $demande->id, $disk);

            $demande->documents()->create([
                'type'          => 'supplemental',
                'label'         => $label,
                'disk'          => $disk,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Document(s) ajouté(s) avec succès.');
    }

    public function destroy(DemandeDocument $document)
    {
        abort_if($document->demande->created_by !== auth()->id(), 403);

        if ($document->path && Storage::disk('public')->exists($document->path)) {
            Storage::disk('public')->delete($document->path);
        }

        $document->delete();

        return back()->with('success', 'Document supprimé avec succès');
    }
}
