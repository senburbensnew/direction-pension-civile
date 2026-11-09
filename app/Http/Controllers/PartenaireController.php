<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresOptimizedImages;
use App\Http\Requests\Admin\ReorderPartenairesRequest;
use App\Http\Requests\Admin\StorePartenaireRequest;
use App\Models\Partenaire;
use App\Support\Storage\UploadPaths;

class PartenaireController extends Controller
{
    use StoresOptimizedImages;

    public function index()
    {
        $partenaires = Partenaire::ordered()->get();

        return view('admin.partenaires.index', compact('partenaires'));
    }

    public function create()
    {
        return view('admin.partenaires.create');
    }

    public function store(StorePartenaireRequest $request)
    {
        $data = [
            'name' => $request->name,
            'website_url' => $request->website_url,
            'active' => $request->boolean('active', true),
            'order' => $request->order ?? (Partenaire::max('order') + 1),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->storeOptimizedImage($request->file('logo'), UploadPaths::cms('partenaires'), 'partenaire');
        }

        Partenaire::create($data);

        return redirect()->route('admin.partenaires.index')
            ->with('success', 'Partenaire ajoute avec succes.');
    }

    public function edit(Partenaire $partenaire)
    {
        return view('admin.partenaires.edit', compact('partenaire'));
    }

    public function update(StorePartenaireRequest $request, Partenaire $partenaire)
    {
        if ($request->hasFile('logo')) {
            $this->deleteOptimizedImage($partenaire->logo);
            $partenaire->logo = $this->storeOptimizedImage($request->file('logo'), UploadPaths::cms('partenaires'), 'partenaire');
        }

        $partenaire->update([
            'name' => $request->name,
            'website_url' => $request->website_url,
            'active' => $request->boolean('active', true),
            'order' => $request->order ?? $partenaire->order,
        ]);

        return redirect()->route('admin.partenaires.index')
            ->with('success', 'Partenaire mis a jour.');
    }

    public function reorder(ReorderPartenairesRequest $request)
    {
        foreach ($request->validated('order') as $position => $id) {
            Partenaire::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Partenaire $partenaire)
    {
        $this->deleteOptimizedImage($partenaire->logo);
        $partenaire->delete();

        return redirect()->route('admin.partenaires.index')
            ->with('success', 'Partenaire supprime.');
    }
}
