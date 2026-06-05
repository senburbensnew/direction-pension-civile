<?php

namespace App\Http\Controllers;

use App\Models\GlossaireTerm;
use Illuminate\Http\Request;

class GlossaireController extends Controller
{
    public function publicIndex()
    {
        $terms = GlossaireTerm::published()->ordered()->get();
        return view('glossaire.index', compact('terms'));
    }

    public function adminIndex(Request $request)
    {
        $query = GlossaireTerm::orderBy('term');
        if ($request->filled('q')) {
            $query->where('term', 'like', '%' . $request->q . '%');
        }
        $terms = $query->paginate(20)->withQueryString();
        return view('admin.glossaire.index', compact('terms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'term'         => 'required|string|max:200',
            'definition'   => 'required|string',
            'category'     => 'required|string|max:100',
            'icon'         => 'nullable|string|max:50',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);
        $data['published']    = $request->boolean('published', true);
        $data['icon']         = $data['icon'] ?? 'fa-book';
        $data['order_column'] = $data['order_column'] ?? 0;

        GlossaireTerm::create($data);
        return back()->with('success', 'Terme ajouté.');
    }

    public function update(Request $request, GlossaireTerm $glossaireTerm)
    {
        $data = $request->validate([
            'term'         => 'required|string|max:200',
            'definition'   => 'required|string',
            'category'     => 'required|string|max:100',
            'icon'         => 'nullable|string|max:50',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);
        $data['published']    = $request->boolean('published', true);
        $data['icon']         = $data['icon'] ?? 'fa-book';
        $data['order_column'] = $data['order_column'] ?? 0;

        $glossaireTerm->update($data);
        return back()->with('success', 'Terme mis à jour.');
    }

    public function destroy(GlossaireTerm $glossaireTerm)
    {
        $glossaireTerm->delete();
        return back()->with('success', 'Terme supprimé.');
    }

    public function togglePublish(GlossaireTerm $glossaireTerm)
    {
        $glossaireTerm->update(['published' => !$glossaireTerm->published]);
        return back()->with('success', $glossaireTerm->published ? 'Publié.' : 'Dépublié.');
    }
}
