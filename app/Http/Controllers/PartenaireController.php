<?php

namespace App\Http\Controllers;

use App\Models\Partenaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartenaireController extends Controller
{
    public function index()
    {
        $partenaires = Partenaire::ordered()->get();
        return view('admin.partenaires.index', compact('partenaires'));
    }

    public function create()
    {
        return view('admin.partenaires.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'website_url' => 'nullable|url|max:255',
            'active'      => 'boolean',
            'order'       => 'nullable|integer|min:0',
        ]);

        $data = [
            'name'        => $request->name,
            'website_url' => $request->website_url,
            'active'      => $request->boolean('active', true),
            'order'       => $request->order ?? (Partenaire::max('order') + 1),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partenaires', 'public');
        }

        Partenaire::create($data);

        return redirect()->route('admin.partenaires.index')
            ->with('success', 'Partenaire ajoute avec succes.');
    }

    public function edit(Partenaire $partenaire)
    {
        return view('admin.partenaires.edit', compact('partenaire'));
    }

    public function update(Request $request, Partenaire $partenaire)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'website_url' => 'nullable|url|max:255',
            'active'      => 'boolean',
            'order'       => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('logo')) {
            if ($partenaire->logo && !str_starts_with($partenaire->logo, 'images/')) {
                Storage::disk('public')->delete($partenaire->logo);
            }
            $partenaire->logo = $request->file('logo')->store('partenaires', 'public');
        }

        $partenaire->update([
            'name'        => $request->name,
            'website_url' => $request->website_url,
            'active'      => $request->boolean('active', true),
            'order'       => $request->order ?? $partenaire->order,
        ]);

        return redirect()->route('admin.partenaires.index')
            ->with('success', 'Partenaire mis a jour.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:partenaires,id']);

        foreach ($request->order as $position => $id) {
            Partenaire::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Partenaire $partenaire)
    {
        if ($partenaire->logo && !str_starts_with($partenaire->logo, 'images/')) {
            Storage::disk('public')->delete($partenaire->logo);
        }
        $partenaire->delete();

        return redirect()->route('admin.partenaires.index')
            ->with('success', 'Partenaire supprime.');
    }
}
