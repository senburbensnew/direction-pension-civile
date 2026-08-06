<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Models\PublicationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicationController extends Controller
{
    public function publicIndex(Request $request)
    {
        $query = Publication::published()->ordered();

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('description', 'like', '%' . $request->q . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $publications = $query->get()->groupBy('type');
        $types = PublicationType::options();
        $typeVisuals = PublicationType::visuals();

        return view('communication.textes_publication', compact('publications', 'types', 'typeVisuals'));
    }

    public function download(Publication $publication)
    {
        if (!$publication->file_path) {
            abort(404, 'Fichier introuvable.');
        }

        if (str_starts_with($publication->file_path, 'documents/')) {
            $path = public_path($publication->file_path);
            if (!file_exists($path)) abort(404, 'Fichier introuvable.');
            return response()->download($path);
        }

        if (!Storage::disk('public')->exists($publication->file_path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($publication->file_path);
    }

    public function adminIndex(Request $request)
    {
        $query = Publication::orderBy('order_column')->orderBy('created_at', 'desc');
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        $publications = $query->paginate(20)->withQueryString();
        $types = PublicationType::options();
        $publicationTypes = PublicationType::ordered()->get();

        return view('admin.publications.index', compact('publications', 'types', 'publicationTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'type'         => ['required', 'string', Rule::in(array_keys(PublicationType::options()))],
            'file'         => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'url'          => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);

        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;
        unset($data['file']);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('publications', 'public');
        }

        Publication::create($data);
        return back()->with('success', 'Publication ajoutée.');
    }

    public function update(Request $request, Publication $publication)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'type'         => ['required', 'string', Rule::in(array_keys(PublicationType::options()))],
            'file'         => 'nullable|file|mimes:pdf,doc,docx|max:20480',
            'url'          => 'nullable|url|max:500',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);

        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;
        unset($data['file']);

        if ($request->hasFile('file')) {
            if ($publication->file_path) {
                Storage::disk('public')->delete($publication->file_path);
            }
            $data['file_path'] = $request->file('file')->store('publications', 'public');
        }

        $publication->update($data);
        return back()->with('success', 'Publication mise à jour.');
    }

    public function destroy(Publication $publication)
    {
        if ($publication->file_path) {
            Storage::disk('public')->delete($publication->file_path);
        }
        $publication->delete();
        return back()->with('success', 'Publication supprimée.');
    }

    public function togglePublish(Publication $publication)
    {
        $publication->update(['published' => !$publication->published]);
        return back()->with('success', $publication->published ? 'Publiée.' : 'Dépubliée.');
    }

    public function storeType(Request $request)
    {
        $data = $request->validate([
            'label'        => 'required|string|max:100',
            'code'         => 'nullable|string|max:50|alpha_dash|unique:publication_types,code',
            'icon'         => 'nullable|string|max:50',
            'badge_class'  => 'nullable|string|max:100',
            'order_column' => 'nullable|integer|min:0',
        ]);

        $data['code'] = $data['code'] ?: PublicationType::makeCode($data['label']);
        $data['icon'] = $data['icon'] ?: 'fa-file';
        $data['badge_class'] = $data['badge_class'] ?: 'bg-gray-100 text-gray-700';
        $data['order_column'] = $data['order_column'] ?? ((int) PublicationType::max('order_column') + 1);

        PublicationType::create($data);

        return back()->with('success', 'Type de document ajouté.');
    }

    public function updateType(Request $request, PublicationType $publicationType)
    {
        $data = $request->validate([
            'label'        => 'required|string|max:100',
            'icon'         => 'nullable|string|max:50',
            'badge_class'  => 'nullable|string|max:100',
            'order_column' => 'nullable|integer|min:0',
        ]);

        $data['icon'] = $data['icon'] ?: $publicationType->icon;
        $data['badge_class'] = $data['badge_class'] ?: $publicationType->badge_class;
        $data['order_column'] = $data['order_column'] ?? $publicationType->order_column;

        $publicationType->update($data);

        return back()->with('success', 'Type de document mis à jour.');
    }

    public function destroyType(PublicationType $publicationType)
    {
        $count = Publication::where('type', $publicationType->code)->count();
        if ($count > 0) {
            return back()->with('error', "Impossible de supprimer « {$publicationType->label} » : {$count} publication(s) l'utilisent encore.");
        }

        $publicationType->delete();
        return back()->with('success', 'Type de document supprimé.');
    }
}
