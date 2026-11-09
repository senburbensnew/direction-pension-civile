<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresOptimizedImages;
use App\Http\Requests\Admin\StoreOfficialRequest;
use App\Http\Requests\Admin\UpdateOfficialRequest;
use App\Models\Official;
use App\Support\Storage\UploadPaths;

class OfficialController extends Controller
{
    use StoresOptimizedImages;

    private const ALLOWED_HTML_TAGS = '<p><br><strong><em><u><s><h2><h3><ul><ol><li><blockquote><a><span>';

    private function sanitizeHtml(?string $html): ?string
    {
        if (empty($html) || trim(strip_tags($html)) === '') {
            return null;
        }

        return strip_tags($html, self::ALLOWED_HTML_TAGS);
    }

    public function index()
    {
        $officials = Official::ordered()->get();

        return view('admin.officials.index', compact('officials'));
    }

    public function create()
    {
        return view('admin.officials.form', ['official' => new Official]);
    }

    public function store(StoreOfficialRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storeOptimizedImage($request->file('photo'), UploadPaths::cms('officials'), 'official');
        }

        $data['active'] = $request->boolean('active', true);
        $data['order'] = $request->input('order', Official::max('order') + 1);
        $data['biographie'] = $this->sanitizeHtml($data['biographie'] ?? null);
        $data['discours'] = $this->sanitizeHtml($data['discours'] ?? null);

        Official::create($data);

        return redirect()->route('admin.officials.index')
            ->with('success', 'Officiel ajouté avec succès.');
    }

    public function edit(Official $official)
    {
        return view('admin.officials.form', compact('official'));
    }

    public function update(UpdateOfficialRequest $request, Official $official)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $this->deleteOptimizedImage($official->photo);
            $data['photo'] = $this->storeOptimizedImage($request->file('photo'), UploadPaths::cms('officials'), 'official');
        }

        $data['active'] = $request->boolean('active', true);
        $data['biographie'] = $this->sanitizeHtml($data['biographie'] ?? null);
        $data['discours'] = $this->sanitizeHtml($data['discours'] ?? null);

        $official->update($data);

        return redirect()->route('admin.officials.index')
            ->with('success', 'Profil mis à jour.');
    }

    public function destroy(Official $official)
    {
        $this->deleteOptimizedImage($official->photo);
        $official->delete();

        return redirect()->route('admin.officials.index')
            ->with('success', 'Officiel supprimé.');
    }
}
