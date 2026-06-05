<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $types = Publication::$types;
        return view('communication.textes_publication', compact('publications', 'types'));
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
        $types = Publication::$types;
        return view('admin.publications.index', compact('publications', 'types'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:300',
            'description'  => 'nullable|string',
            'type'         => 'required|string|in:' . implode(',', array_keys(Publication::$types)),
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
            'type'         => 'required|string|in:' . implode(',', array_keys(Publication::$types)),
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
}
