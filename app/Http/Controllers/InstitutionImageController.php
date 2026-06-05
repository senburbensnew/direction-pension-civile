<?php

namespace App\Http\Controllers;

use App\Models\InstitutionImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InstitutionImageController extends Controller
{
    public function index()
    {
        $images = InstitutionImage::ordered()->get();
        return view('admin.institution-images.index', compact('images'));
    }

    public function create()
    {
        return view('admin.institution-images.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'  => 'nullable|string|max:255',
            'image'  => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'active' => 'boolean',
            'order'  => 'nullable|integer|min:0',
        ]);

        $path = $request->file('image')->store('institution-images', 'public');

        InstitutionImage::create([
            'title'  => $request->title,
            'image'  => $path,
            'active' => $request->boolean('active', true),
            'order'  => $request->order ?? (InstitutionImage::max('order') + 1),
        ]);

        return redirect()->route('admin.institution-images.index')
            ->with('success', 'Image ajoutee avec succes.');
    }

    public function edit(InstitutionImage $institutionImage)
    {
        return view('admin.institution-images.edit', compact('institutionImage'));
    }

    public function update(Request $request, InstitutionImage $institutionImage)
    {
        $request->validate([
            'title'  => 'nullable|string|max:255',
            'image'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'active' => 'boolean',
            'order'  => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            if (!str_starts_with($institutionImage->image, 'images/')) {
                Storage::disk('public')->delete($institutionImage->image);
            }
            $institutionImage->image = $request->file('image')->store('institution-images', 'public');
        }

        $institutionImage->update([
            'title'  => $request->title,
            'active' => $request->boolean('active', true),
            'order'  => $request->order ?? $institutionImage->order,
        ]);

        return redirect()->route('admin.institution-images.index')
            ->with('success', 'Image mise a jour.');
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:institution_images,id']);

        foreach ($request->order as $position => $id) {
            InstitutionImage::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(InstitutionImage $institutionImage)
    {
        if (!str_starts_with($institutionImage->image, 'images/')) {
            Storage::disk('public')->delete($institutionImage->image);
        }
        $institutionImage->delete();

        return redirect()->route('admin.institution-images.index')
            ->with('success', 'Image supprimee.');
    }
}
