<?php

namespace App\Http\Controllers;

use App\Models\LienUtile;
use Illuminate\Http\Request;

class LienUtileController extends Controller
{
    public function publicIndex()
    {
        $links = LienUtile::published()->ordered()->get();
        return view('liens-utiles.index', compact('links'));
    }

    public function adminIndex(Request $request)
    {
        $query = LienUtile::orderBy('order_column')->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', '%' . $q . '%')
                    ->orWhere('abbr', 'like', '%' . $q . '%')
                    ->orWhere('url', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $links = $query->paginate(20)->withQueryString();
        $categories = LienUtile::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('admin.liens-utiles.index', compact('links', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:300',
            'abbr'         => 'nullable|string|max:20',
            'url'          => 'required|url|max:500',
            'category'     => 'required|string|max:100',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);
        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;

        LienUtile::create($data);
        return back()->with('success', 'Lien ajouté.');
    }

    public function update(Request $request, LienUtile $lienUtile)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:300',
            'abbr'         => 'nullable|string|max:20',
            'url'          => 'required|url|max:500',
            'category'     => 'required|string|max:100',
            'order_column' => 'nullable|integer|min:0',
            'published'    => 'nullable|boolean',
        ]);
        $data['published']    = $request->boolean('published', true);
        $data['order_column'] = $data['order_column'] ?? 0;

        $lienUtile->update($data);
        return back()->with('success', 'Lien mis à jour.');
    }

    public function destroy(LienUtile $lienUtile)
    {
        $lienUtile->delete();
        return back()->with('success', 'Lien supprimé.');
    }

    public function togglePublish(LienUtile $lienUtile)
    {
        $lienUtile->update(['published' => !$lienUtile->published]);
        return back()->with('success', $lienUtile->published ? 'Publié.' : 'Dépublié.');
    }
}
