<?php

namespace App\Http\Controllers;

use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class OfficialController extends Controller
{
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
        return view('admin.officials.form', ['official' => new Official()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug'        => 'required|string|max:100|unique:officials,slug|regex:/^[a-z0-9\-]+$/',
            'role'        => 'required|string|max:255',
            'nom'         => 'required|string|max:255',
            'sexe'        => 'required|in:M,F',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'biographie'  => 'nullable|string',
            'discours'    => 'nullable|string',
            'citation'    => 'nullable|string|max:500',
            'order'       => 'nullable|integer|min:0',
            'active'      => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        $data['active']     = $request->boolean('active', true);
        $data['order']      = $request->input('order', Official::max('order') + 1);
        $data['biographie'] = $this->sanitizeHtml($data['biographie'] ?? null);
        $data['discours']   = $this->sanitizeHtml($data['discours'] ?? null);

        Official::create($data);

        return redirect()->route('admin.officials.index')
            ->with('success', 'Officiel ajouté avec succès.');
    }

    public function edit(Official $official)
    {
        return view('admin.officials.form', compact('official'));
    }

    public function update(Request $request, Official $official)
    {
        $data = $request->validate([
            'role'        => 'required|string|max:255',
            'nom'         => 'required|string|max:255',
            'sexe'        => 'required|in:M,F',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'biographie'  => 'nullable|string',
            'discours'    => 'nullable|string',
            'citation'    => 'nullable|string|max:500',
            'order'       => 'nullable|integer|min:0',
            'active'      => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($official->photo && !str_starts_with($official->photo, 'images/')) {
                Storage::disk('public')->delete($official->photo);
            }
            $data['photo'] = $request->file('photo')->store('officials', 'public');
        }

        $data['active']     = $request->boolean('active', true);
        $data['biographie'] = $this->sanitizeHtml($data['biographie'] ?? null);
        $data['discours']   = $this->sanitizeHtml($data['discours'] ?? null);

        $official->update($data);

        return redirect()->route('admin.officials.index')
            ->with('success', 'Profil mis à jour.');
    }

    public function destroy(Official $official)
    {
        if ($official->photo && !str_starts_with($official->photo, 'images/')) {
            Storage::disk('public')->delete($official->photo);
        }
        $official->delete();

        return redirect()->route('admin.officials.index')
            ->with('success', 'Officiel supprimé.');
    }
}
