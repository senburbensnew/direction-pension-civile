<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\StoresOptimizedImages;
use App\Http\Requests\Admin\ReorderInstitutionImagesRequest;
use App\Http\Requests\Admin\StoreInstitutionImageRequest;
use App\Http\Requests\Admin\UpdateInstitutionImageRequest;
use App\Models\InstitutionImage;
use App\Support\Storage\UploadPaths;

class InstitutionImageController extends Controller
{
    use StoresOptimizedImages;

    public function index()
    {
        $images = InstitutionImage::ordered()->get();

        return view('admin.institution-images.index', compact('images'));
    }

    public function create()
    {
        return view('admin.institution-images.create');
    }

    public function store(StoreInstitutionImageRequest $request)
    {
        $path = $this->storeOptimizedImage($request->file('image'), UploadPaths::cms('institution'), 'institution');

        InstitutionImage::create([
            'title' => $request->title,
            'image' => $path,
            'active' => $request->boolean('active', true),
            'order' => $request->order ?? (InstitutionImage::max('order') + 1),
        ]);

        return redirect()->route('admin.institution-images.index')
            ->with('success', 'Image ajoutee avec succes.');
    }

    public function edit(InstitutionImage $institutionImage)
    {
        return view('admin.institution-images.edit', compact('institutionImage'));
    }

    public function update(UpdateInstitutionImageRequest $request, InstitutionImage $institutionImage)
    {
        if ($request->hasFile('image')) {
            $this->deleteOptimizedImage($institutionImage->image);
            $institutionImage->image = $this->storeOptimizedImage($request->file('image'), UploadPaths::cms('institution'), 'institution');
        }

        $institutionImage->update([
            'title' => $request->title,
            'active' => $request->boolean('active', true),
            'order' => $request->order ?? $institutionImage->order,
        ]);

        return redirect()->route('admin.institution-images.index')
            ->with('success', 'Image mise a jour.');
    }

    public function reorder(ReorderInstitutionImagesRequest $request)
    {
        foreach ($request->validated('order') as $position => $id) {
            InstitutionImage::where('id', $id)->update(['order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(InstitutionImage $institutionImage)
    {
        $this->deleteOptimizedImage($institutionImage->image);
        $institutionImage->delete();

        return redirect()->route('admin.institution-images.index')
            ->with('success', 'Image supprimee.');
    }
}
