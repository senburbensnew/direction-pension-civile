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
        $query = GlossaireTerm::orderBy('order_column')->orderBy('term');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('term', 'like', '%' . $q . '%')
                    ->orWhere('definition', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $terms = $query->paginate(20)->withQueryString();
        $categories = GlossaireTerm::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.glossaire.index', compact('terms', 'categories'));
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
